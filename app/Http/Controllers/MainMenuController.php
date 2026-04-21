<?php

namespace App\Http\Controllers;

use App\Support\MainMenuViewDataFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MainMenuController extends Controller
{
    public function __construct(
        private readonly MainMenuViewDataFactory $viewDataFactory
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $priceMonth = $user ? (trim($request->string('price_month')->toString()) ?: null) : null;

        if ($user?->force_password_reset) {
            return redirect()->route('account.password.edit');
        }

        return view('main_menu', $this->viewDataFactory->make($user, $priceMonth));
    }
}
