<?php

namespace App\Http\Controllers;

use App\Models\PrivacyNoticeVersion;
use Illuminate\View\View;

class PrivacyNoticeController extends Controller
{
    public function show(): View
    {
        $privacyNotice = PrivacyNoticeVersion::query()
            ->where('is_active', true)
            ->where('effective_at', '<=', now())
            ->orderByDesc('effective_at')
            ->orderByDesc('privacy_notice_version_id')
            ->firstOrFail();

        return view('privacy.notice', compact('privacyNotice'));
    }
}
