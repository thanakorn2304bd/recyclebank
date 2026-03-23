<?php

namespace App\Support;

use App\Models\LogActivity;
use App\Models\UserAccount;
use Illuminate\Support\Str;

class ActivityLogger
{
    public static function log(?UserAccount $user, string $module, string $action): void
    {
        if (! $user?->user_id) {
            return;
        }

        try {
            LogActivity::create([
                'user_id' => $user->user_id,
                'module' => Str::limit(trim($module), 50, ''),
                'action' => Str::limit(trim($action), 255, ''),
                'timestamp' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public static function forCurrentUser(string $module, string $action): void
    {
        $user = auth()->user();

        if (! $user instanceof UserAccount) {
            return;
        }

        self::log($user, $module, $action);
    }
}
