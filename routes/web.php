<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminCommunityController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataSubjectRequestController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\HouseholdMemberAdditionRequestController;
use App\Http\Controllers\MainMenuController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialPriceController;
use App\Http\Controllers\PrivacyNoticeController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SecurityIncidentController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\WithdrawRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('main-menu');
});

Route::get('privacy-notice', [PrivacyNoticeController::class, 'show'])
    ->middleware('pdpa.enabled')
    ->name('privacy-notice.show');

Route::get('main-menu', MainMenuController::class)->name('main-menu');

Route::middleware(['auth', 'active', 'password.current'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('admin/staff', [AdminStaffController::class, 'index'])->name('admin.staff.index');
        Route::get('admin/staff/create', [AdminStaffController::class, 'create'])->name('admin.staff.create');
        Route::post('admin/staff', [AdminStaffController::class, 'store'])->name('admin.staff.store');
        Route::get('admin/staff/{staff}', [AdminStaffController::class, 'show'])->name('admin.staff.show');
        Route::get('admin/staff/{staff}/edit', [AdminStaffController::class, 'edit'])->name('admin.staff.edit');
        Route::put('admin/staff/{staff}', [AdminStaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('admin/staff/{staff}', [AdminStaffController::class, 'destroy'])->name('admin.staff.destroy');

        Route::get('admin/communities', [AdminCommunityController::class, 'index'])->name('admin.communities.index');
        Route::get('admin/communities/create', [AdminCommunityController::class, 'create'])->name('admin.communities.create');
        Route::post('admin/communities', [AdminCommunityController::class, 'store'])->name('admin.communities.store');
        Route::get('admin/communities/{community}/edit', [AdminCommunityController::class, 'edit'])->name('admin.communities.edit');
        Route::put('admin/communities/{community}', [AdminCommunityController::class, 'update'])->name('admin.communities.update');
        Route::delete('admin/communities/{community}', [AdminCommunityController::class, 'destroy'])->name('admin.communities.destroy');
        Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('admin/users/staff', [AdminUserController::class, 'storeStaff'])->name('admin.users.store-staff');
        Route::patch('admin/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('admin.users.toggle-active');
        Route::get('admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');

        Route::get('admin/backup', [BackupController::class, 'index'])->name('admin.backup.index');
        Route::post('admin/backup', [BackupController::class, 'store'])->name('admin.backup.store');
        Route::post('admin/backup/restore', [BackupController::class, 'restore'])->name('admin.backup.restore');
        Route::get('admin/backup/{filename}/download', [BackupController::class, 'download'])->name('admin.backup.download');
        Route::delete('admin/backup/{filename}', [BackupController::class, 'destroy'])->name('admin.backup.destroy');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
        Route::resource('materials', MaterialController::class)->except(['show']);
        Route::get('households/quick-search', [HouseholdController::class, 'quickSearch'])->name('households.quick-search');
        Route::get('households/{household}/documents', [HouseholdController::class, 'documentsIndex'])
            ->name('households.documents.index');
        Route::get('households/{household}/documents/{registrationDocument}', [HouseholdController::class, 'showRegistrationDocument'])
            ->name('households.documents.show');
        Route::get(
            'households/{household}/member-additions/{memberAdditionRequest}/documents/{memberAdditionRequestDocument}',
            [HouseholdMemberAdditionRequestController::class, 'showDocument']
        )->name('households.member-additions.documents.show');
        Route::resource('households', HouseholdController::class)->except(['index', 'show']);
        Route::get('households/{household}/credentials', [HouseholdController::class, 'createCredentials'])->name('households.credentials.create');
        Route::post('households/{household}/credentials', [HouseholdController::class, 'storeCredentials'])->name('households.credentials.store');
        Route::patch('households/{household}/review', [HouseholdController::class, 'review'])->name('households.review');
        Route::patch(
            'households/{household}/member-additions/{memberAdditionRequest}/review',
            [HouseholdMemberAdditionRequestController::class, 'review']
        )->name('households.member-additions.review');

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
        Route::patch('withdraw-requests/{withdrawRequest}/review', [WithdrawRequestController::class, 'review'])->name('withdraw-requests.review');
        Route::post('transactions/{transaction}/reverse', [TransactionHistoryController::class, 'reverse'])->name('transactions.reverse');

        Route::middleware('pdpa.enabled')->group(function () {
            Route::get('compliance/dsars', [DataSubjectRequestController::class, 'index'])->name('compliance.dsars.index');
            Route::get('compliance/dsars/create', [DataSubjectRequestController::class, 'create'])->name('compliance.dsars.create');
            Route::post('compliance/dsars', [DataSubjectRequestController::class, 'store'])->name('compliance.dsars.store');
            Route::get('compliance/dsars/{dsar}', [DataSubjectRequestController::class, 'show'])->name('compliance.dsars.show');
            Route::put('compliance/dsars/{dsar}', [DataSubjectRequestController::class, 'update'])->name('compliance.dsars.update');

            Route::get('compliance/incidents', [SecurityIncidentController::class, 'index'])->name('compliance.incidents.index');
            Route::get('compliance/incidents/create', [SecurityIncidentController::class, 'create'])->name('compliance.incidents.create');
            Route::post('compliance/incidents', [SecurityIncidentController::class, 'store'])->name('compliance.incidents.store');
            Route::get('compliance/incidents/{incident}', [SecurityIncidentController::class, 'show'])->name('compliance.incidents.show');
            Route::put('compliance/incidents/{incident}', [SecurityIncidentController::class, 'update'])->name('compliance.incidents.update');
        });
    });

    // สมาชิกดูได้เฉพาะข้อมูลของตนเอง (บังคับด้วย policy และ query scope)
    Route::resource('households', HouseholdController::class)->only(['index', 'show']);
    Route::get('withdraw-requests', [WithdrawRequestController::class, 'index'])->name('withdraw-requests.index');
    Route::middleware('role:member')->group(function () {
        Route::get('households/{household}/member-additions/create', [HouseholdMemberAdditionRequestController::class, 'create'])
            ->name('households.member-additions.create');
        Route::post('households/{household}/member-additions', [HouseholdMemberAdditionRequestController::class, 'store'])
            ->name('households.member-additions.store');
        Route::get('withdraw-requests/create', [WithdrawRequestController::class, 'create'])->name('withdraw-requests.create');
        Route::post('withdraw-requests', [WithdrawRequestController::class, 'store'])->name('withdraw-requests.store');
    });
    Route::get('withdraw-requests/{withdrawRequest}', [WithdrawRequestController::class, 'show'])->name('withdraw-requests.show');
    Route::get('withdraw-requests/{withdrawRequest}/form', [WithdrawRequestController::class, 'form'])->name('withdraw-requests.form');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('households/{household}/transactions', [TransactionHistoryController::class, 'household'])->name('transactions.household');
    Route::get('transactions/{transaction}', [TransactionHistoryController::class, 'show'])->name('transactions.show');
    Route::get('transactions/{transaction}/receipt', [ReceiptController::class, 'receipt'])->name('transactions.receipt');
});

require __DIR__.'/auth.php';
