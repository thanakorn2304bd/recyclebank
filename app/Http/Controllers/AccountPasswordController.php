<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountPasswordRequest;
use App\Models\UserAccount;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountPasswordController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var UserAccount $user */
        $user = $request->user();

        return view('account.password.edit', [
            'user' => $user,
            'requiresPasswordReset' => (bool) $user->force_password_reset,
        ]);
    }

    public function update(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        /** @var UserAccount $user */
        $user = $request->user();
        $before = [
            'password_changed_at' => $user->password_changed_at?->format('Y-m-d H:i:s'),
            'force_password_reset' => (bool) $user->force_password_reset,
        ];

        $user->applyPassword($request->validated('password'));
        $user->save();

        ActivityLogger::forCurrentUser(
            'auth',
            'เปลี่ยนรหัสผ่านของตนเอง',
            [
                'entity_type' => 'user_account',
                'entity_id' => (string) $user->user_id,
                'before' => $before,
                'after' => [
                    'password_changed_at' => $user->password_changed_at?->format('Y-m-d H:i:s'),
                    'force_password_reset' => (bool) $user->force_password_reset,
                ],
            ]
        );

        return redirect()
            ->route('main-menu')
            ->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }
}
