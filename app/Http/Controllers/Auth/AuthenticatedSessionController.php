<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        $user->last_login = now();
        $user->save();

        $householdId = $user->household_id ?: $user->household?->household_id;
        $defaultRoute = $user->role === 'member'
            ? ($householdId
                ? route('households.show', ['household' => $householdId], absolute: false)
                : route('households.index', absolute: false))
            : route('main-menu', absolute: false);

        return redirect()->intended($defaultRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $request->session()->forget('user_id');

        return redirect('/');
    }
}
