<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $request->session()->put('user_id', $user->user_id);

        $user->clearLoginLockState();
        $user->last_login = now();
        $user->save();
        ActivityLogger::log($user, 'auth', 'เข้าสู่ระบบ');

        if ($user->force_password_reset) {
            return redirect()
                ->route('account.password.edit')
                ->with('success', 'กรุณาเปลี่ยนรหัสผ่านก่อนใช้งานต่อ เนื่องจากบัญชีนี้เพิ่งได้รับการตั้งหรือรีเซ็ตรหัสผ่าน');
        }

        return redirect()->intended(route('main-menu', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            ActivityLogger::log($user, 'auth', 'ออกจากระบบ');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $request->session()->forget('user_id');

        return redirect('/');
    }
}
