<?php

namespace App\Support\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PendingHouseholdRegistrationStore
{
    public const SESSION_KEY = 'auth.pending_household_registration';

    private const CACHE_KEY_PREFIX = 'auth.pending_household_registration.';

    public function put(Request $request, array $payload): string
    {
        $existingToken = $this->requestToken($request);

        if ($existingToken !== null) {
            Cache::forget($this->cacheKey($existingToken));
        }

        $token = Str::random(40);
        $payload['token'] = $token;

        Cache::put(
            $this->cacheKey($token),
            $payload,
            now()->addMinutes((int) config('session.lifetime', 120))
        );

        $request->session()->put(self::SESSION_KEY, [
            'token' => $token,
        ]);

        return $token;
    }

    public function get(Request $request): ?array
    {
        $legacySessionPayload = $request->session()->get(self::SESSION_KEY);

        if ($this->isLegacyPayload($legacySessionPayload)) {
            return $this->migrateLegacyPayload($request, $legacySessionPayload);
        }

        $tokens = collect([
            $this->extractRequestToken($request),
            $this->extractSessionToken($request),
        ])->filter(fn ($token) => is_string($token) && $token !== '')
            ->unique()
            ->values();

        foreach ($tokens as $token) {
            $payload = Cache::get($this->cacheKey($token));

            if (! is_array($payload)) {
                continue;
            }

            $payload['token'] = $token;
            $request->session()->put(self::SESSION_KEY, [
                'token' => $token,
            ]);

            return $payload;
        }

        return null;
    }

    public function forget(Request $request): void
    {
        $tokens = collect([
            $this->extractRequestToken($request),
            $this->extractSessionToken($request),
        ])->filter(fn ($token) => is_string($token) && $token !== '')
            ->unique()
            ->values();

        foreach ($tokens as $token) {
            Cache::forget($this->cacheKey($token));
        }

        $request->session()->forget(self::SESSION_KEY);
    }

    public function requestToken(Request $request): ?string
    {
        return $this->extractRequestToken($request) ?? $this->extractSessionToken($request);
    }

    private function migrateLegacyPayload(Request $request, array $payload): array
    {
        $token = is_string($payload['token'] ?? null) && $payload['token'] !== ''
            ? $payload['token']
            : Str::random(40);

        $payload['token'] = $token;

        Cache::put(
            $this->cacheKey($token),
            $payload,
            now()->addMinutes((int) config('session.lifetime', 120))
        );

        $request->session()->put(self::SESSION_KEY, [
            'token' => $token,
        ]);

        return $payload;
    }

    private function isLegacyPayload(mixed $value): bool
    {
        return is_array($value) && array_key_exists('household_attributes', $value);
    }

    private function extractRequestToken(Request $request): ?string
    {
        $token = $request->query('draft');

        if (! is_string($token) || $token === '') {
            $token = $request->input('draft_token');
        }

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function extractSessionToken(Request $request): ?string
    {
        $sessionValue = $request->session()->get(self::SESSION_KEY);

        if (is_string($sessionValue) && $sessionValue !== '') {
            return $sessionValue;
        }

        $token = is_array($sessionValue) ? ($sessionValue['token'] ?? null) : null;

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function cacheKey(string $token): string
    {
        return self::CACHE_KEY_PREFIX.$token;
    }
}
