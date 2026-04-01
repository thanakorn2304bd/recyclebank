<?php

namespace App\Http\Requests\Auth;

use App\Models\Household;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const PASSWORD_LOCK_MAX_ATTEMPTS = 5;

    private const PASSWORD_LOCK_MINUTES = 15;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $user = $this->candidateUser();
        $this->ensureUserIsNotLocked($user);

        if (! Auth::attempt([
            'username' => $this->string('username')->toString(),
            'password' => $this->string('password')->toString(),
            'is_active' => 1,
        ])) {
            if ($this->credentialsMatchInactivePendingAccount($user)) {
                RateLimiter::clear($this->throttleKey());
                $message = $this->approvalPendingMessage($user);
                $this->session()->flash('approval_pending_notice', $message);

                throw ValidationException::withMessages([
                    'username' => $message,
                ]);
            }

            RateLimiter::hit($this->throttleKey());

            if ($user instanceof UserAccount && $user->is_active) {
                $lockedUntil = $this->registerFailedAttempt($user);

                if ($lockedUntil !== null) {
                    RateLimiter::clear($this->throttleKey());

                    throw ValidationException::withMessages([
                        'username' => 'บัญชีนี้ถูกล็อกชั่วคราวจากการกรอกรหัสผ่านผิดหลายครั้ง จนถึง '.$lockedUntil->format('d/m/Y H:i'),
                    ]);
                }
            }

            throw ValidationException::withMessages([
                'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง หรือบัญชีถูกปิดใช้งาน',
            ]);
        }

        if ($user instanceof UserAccount) {
            $user->clearLoginLockState();
            $user->save();
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }

    private function candidateUser(): ?UserAccount
    {
        return UserAccount::query()
            ->with('household')
            ->where('username', $this->string('username')->toString())
            ->first();
    }

    private function credentialsMatchInactivePendingAccount(?UserAccount $user): bool
    {
        if (! $user instanceof UserAccount || $user->is_active) {
            return false;
        }

        if (! $user->household instanceof Household) {
            return false;
        }

        if ($user->household->active_status !== 'pending') {
            return false;
        }

        return Hash::check($this->string('password')->toString(), $user->password);
    }

    private function ensureUserIsNotLocked(?UserAccount $user): void
    {
        if (! $user instanceof UserAccount || ! $user->is_active) {
            return;
        }

        if ($user->locked_until !== null && $user->locked_until->isPast()) {
            $user->clearLoginLockState();
            $user->save();

            return;
        }

        if (! $user->isLocked()) {
            return;
        }

        RateLimiter::clear($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => 'บัญชีนี้ถูกล็อกชั่วคราวจากการกรอกรหัสผ่านผิดหลายครั้ง จนถึง '.$user->locked_until?->format('d/m/Y H:i'),
        ]);
    }

    private function registerFailedAttempt(UserAccount $user)
    {
        $attempts = ((int) $user->failed_login_attempts) + 1;
        $user->failed_login_attempts = $attempts;

        if ($attempts < self::PASSWORD_LOCK_MAX_ATTEMPTS) {
            $user->save();

            return null;
        }

        $user->locked_until = now()->addMinutes(self::PASSWORD_LOCK_MINUTES);
        $user->save();

        ActivityLogger::log(
            $user,
            'auth',
            'ล็อกบัญชีชั่วคราวจากการกรอกรหัสผ่านผิดหลายครั้ง',
            [
                'entity_type' => 'user_account',
                'entity_id' => (string) $user->user_id,
                'metadata' => [
                    'locked_until' => $user->locked_until?->format('Y-m-d H:i:s'),
                ],
            ]
        );

        return $user->locked_until;
    }

    private function approvalPendingMessage(UserAccount $user): string
    {
        $accountNo = $user->household?->account_no ?: $user->username;

        return "คำขอสมัครสมาชิกของบัญชี {$accountNo} อยู่ระหว่างรออนุมัติจากเจ้าหน้าที่ กรุณารอการยืนยันก่อนเข้าสู่ระบบ";
    }
}
