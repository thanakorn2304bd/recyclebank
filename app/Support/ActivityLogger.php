<?php

namespace App\Support;

use App\Models\LogActivity;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityLogger
{
    public static function log(?UserAccount $user, string $module, string $action, array $context = []): void
    {
        if (! $user?->user_id) {
            return;
        }

        $request = request();

        try {
            LogActivity::create([
                'user_id' => $user->user_id,
                'module' => Str::limit(trim($module), 50, ''),
                'action' => Str::limit(trim($action), 255, ''),
                'timestamp' => now(),
                'entity_type' => self::nullableString($context['entity_type'] ?? null, 100),
                'entity_id' => self::nullableString($context['entity_id'] ?? null, 50),
                'ip_address' => self::requestIpAddress($request),
                'user_agent' => self::requestUserAgent($request),
                'before_values' => self::normalizeArrayPayload($context['before'] ?? null),
                'after_values' => self::normalizeArrayPayload($context['after'] ?? null),
                'metadata' => self::normalizeArrayPayload($context['metadata'] ?? null),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public static function forCurrentUser(string $module, string $action, array $context = []): void
    {
        $user = auth()->user();

        if (! $user instanceof UserAccount) {
            return;
        }

        self::log($user, $module, $action, $context);
    }

    private static function requestIpAddress(mixed $request): ?string
    {
        if (! $request instanceof Request) {
            return null;
        }

        return self::nullableString($request->ip(), 45);
    }

    private static function requestUserAgent(mixed $request): ?string
    {
        if (! $request instanceof Request) {
            return null;
        }

        return self::nullableString($request->userAgent(), 65535);
    }

    private static function nullableString(mixed $value, int $limit): ?string
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return null;
        }

        return Str::limit($string, $limit, '');
    }

    private static function normalizeArrayPayload(mixed $value): ?array
    {
        if (! is_array($value) || $value === []) {
            return null;
        }

        return $value;
    }
}
