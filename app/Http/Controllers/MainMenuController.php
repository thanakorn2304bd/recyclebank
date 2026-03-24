<?php

namespace App\Http\Controllers;

use App\Support\MainMenuViewDataFactory;
use Illuminate\Http\Request;

class MainMenuController extends Controller
{
    public function __construct(
        private readonly MainMenuViewDataFactory $viewDataFactory
    ) {}

    public function __invoke(Request $request)
    {
        return view('main_menu', $this->viewDataFactory->make($request->user()));
    }
}
