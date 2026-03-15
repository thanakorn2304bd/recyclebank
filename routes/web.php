<?php

use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialPriceController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\ReceiptController;
use App\Models\Material;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('main-menu');
});

Route::get('main-menu', function () {
    $today = now()->toDateString();

    $materials = Material::query()
        ->with('category')
        ->with(['prices' => function ($query) use ($today) {
            $query->where('effective_date', '<=', $today)
                ->where(function ($sub) use ($today) {
                    $sub->whereNull('expired_date')
                        ->orWhere('expired_date', '>=', $today);
                })
                ->orderByDesc('effective_date')
                ->orderByDesc('price_id');
        }])
        ->orderBy('material_name')
        ->get();

    return view('main_menu', compact('materials'));
})->name('main-menu');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
        Route::resource('materials', MaterialController::class)->except(['show']);
        Route::resource('households', HouseholdController::class)->except(['index', 'show']);
        Route::get('households/{household}/credentials', [HouseholdController::class, 'createCredentials'])->name('households.credentials.create');
        Route::post('households/{household}/credentials', [HouseholdController::class, 'storeCredentials'])->name('households.credentials.store');

        // ราคา: ใช้ resource + เพิ่ม route ดู “ราคาปัจจุบัน” ต่อวัสดุ
        Route::resource('material-prices', MaterialPriceController::class)->except(['show', 'edit', 'update']);
        Route::get('materials/{material}/prices', [MaterialPriceController::class, 'materialPrices'])->name('materials.prices');

        Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::get('deposits/lookup-household', [DepositController::class, 'lookupHousehold'])->name('deposits.lookup-household');
        Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');

        Route::get('withdraws/create', [WithdrawController::class, 'create'])->name('withdraws.create');
        Route::post('withdraws', [WithdrawController::class, 'store'])->name('withdraws.store');
    });

    // สมาชิกดูได้เฉพาะข้อมูลของตนเอง (กรองใน controller)
    Route::resource('households', HouseholdController::class)->only(['index', 'show']);
    Route::get('transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('households/{household}/transactions', [TransactionHistoryController::class, 'household'])->name('transactions.household');
    Route::get('transactions/{transaction}', [TransactionHistoryController::class, 'show'])->name('transactions.show');
    Route::get('transactions/{transaction}/receipt', [ReceiptController::class, 'receipt'])->name('transactions.receipt');
});

require __DIR__.'/auth.php';
