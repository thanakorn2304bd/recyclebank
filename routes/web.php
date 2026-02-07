<?php

use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialPriceController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\ReceiptController;

Route::get('/', function () {
    return redirect()->route('main-menu');
});

Route::view('dashboard', 'dashboard')->name('dashboard');
Route::view('main-menu', 'main_menu')->name('main-menu');

Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
Route::resource('materials', MaterialController::class)->except(['show']);
Route::resource('households', HouseholdController::class)->except(['show']);

// ราคา: ใช้ resource + เพิ่ม route ดู “ราคาปัจจุบัน” ต่อวัสดุ
Route::resource('material-prices', MaterialPriceController::class)->except(['show', 'edit', 'update']);
Route::get('materials/{material}/prices', [MaterialPriceController::class, 'materialPrices'])->name('materials.prices');

Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
Route::get('deposits/lookup-household', [DepositController::class, 'lookupHousehold'])->name('deposits.lookup-household');
Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');

Route::get('withdraws/create', [WithdrawController::class, 'create'])->name('withdraws.create');
Route::post('withdraws', [WithdrawController::class, 'store'])->name('withdraws.store');

Route::get('transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
Route::get('households/{household}/transactions', [TransactionHistoryController::class, 'household'])->name('transactions.household');
Route::get('transactions/{transaction}', [TransactionHistoryController::class, 'show'])->name('transactions.show');

Route::get('transactions/{transaction}/receipt', [ReceiptController::class, 'receipt'])->name('transactions.receipt');
