<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof UserAccount || $user->is_active) {
            return $next($request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget('user_id');

        return redirect()->route('login')->withErrors([
            'username' => 'บัญชีนี้ถูกปิดใช้งานแล้ว กรุณาติดต่อผู้ดูแลระบบ',
        ]);
    }
}
