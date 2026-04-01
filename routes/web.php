<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\MainMenuController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialPriceController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('main-menu');
});

Route::get('main-menu', MainMenuController::class)->name('main-menu');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('admin/staff', [AdminStaffController::class, 'index'])->name('admin.staff.index');
        Route::get('admin/staff/{staff}', [AdminStaffController::class, 'show'])->name('admin.staff.show');
        Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users/staff', [AdminUserController::class, 'storeStaff'])->name('admin.users.store-staff');
        Route::get('admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
        Route::resource('materials', MaterialController::class)->except(['show']);
        Route::resource('households', HouseholdController::class)->except(['index', 'show']);
        Route::get('households/{household}/credentials', [HouseholdController::class, 'createCredentials'])->name('households.credentials.create');
        Route::post('households/{household}/credentials', [HouseholdController::class, 'storeCredentials'])->name('households.credentials.store');
        Route::patch('households/{household}/review', [HouseholdController::class, 'review'])->name('households.review');

        // ราคา: ใช้ resource + เพิ่ม route ดู “ราคาปัจจุบัน” ต่อวัสดุ
        Route::post('material-prices/bulk-update', [MaterialPriceController::class, 'bulkUpdate'])->name('material-prices.bulk-update');
        Route::resource('material-prices', MaterialPriceController::class)->except(['show', 'edit', 'update']);
        Route::get('materials/{material}/prices', [MaterialPriceController::class, 'materialPrices'])->name('materials.prices');

        Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::get('deposits/lookup-household', [DepositController::class, 'lookupHousehold'])->name('deposits.lookup-household');
        Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');

        Route::get('withdraws/preview', [WithdrawController::class, 'preview'])->name('withdraws.preview');
        Route::get('withdraws/create', [WithdrawController::class, 'create'])->name('withdraws.create');
        Route::post('withdraws', [WithdrawController::class, 'store'])->name('withdraws.store');
        Route::post('transactions/{transaction}/reverse', [TransactionHistoryController::class, 'reverse'])->name('transactions.reverse');
    });

    // สมาชิกดูได้เฉพาะข้อมูลของตนเอง (บังคับด้วย policy และ query scope)
    Route::resource('households', HouseholdController::class)->only(['index', 'show']);
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('households/{household}/transactions', [TransactionHistoryController::class, 'household'])->name('transactions.household');
    Route::get('transactions/{transaction}', [TransactionHistoryController::class, 'show'])->name('transactions.show');
    Route::get('transactions/{transaction}/receipt', [ReceiptController::class, 'receipt'])->name('transactions.receipt');
});

require __DIR__.'/auth.php';
