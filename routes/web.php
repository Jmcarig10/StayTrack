<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PasswordChangeRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ArchiveController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/waiting-approval',
        function () {
            $user = Auth::user();

            if ($user instanceof User && $user->isApproved()) {
                return redirect()->route('dashboard');
            }

            return response()->view('auth.waiting-approval');
        }
    )->name('waiting.approval');

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->middleware('approved')->name('dashboard');

    Route::patch('notifications/read', [TenantPortalController::class, 'markNotificationsRead'])->middleware('approved')->name('notifications.read');
    Route::get('notifications/{notification}/open', [TenantPortalController::class, 'openNotification'])->middleware('approved')->name('notifications.open');

    Route::middleware(['approved', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('apartment', [TenantPortalController::class, 'apartment'])->name('apartment');
        Route::get('lease', [TenantPortalController::class, 'lease'])->name('lease');
        Route::get('bills', [TenantPortalController::class, 'bills'])->name('bills.index');
        Route::get('bills/{bill}', [TenantPortalController::class, 'showBill'])->name('bills.show');
        Route::get('bills/{bill}/pay', [TenantPortalController::class, 'payBill'])->name('bills.pay');
        Route::post('bills/{bill}/payments', [TenantPortalController::class, 'submitPayment'])->name('bills.payments.store');
        Route::get('bills/{bill}/invoice', [TenantPortalController::class, 'invoice'])->name('bills.invoice');
        Route::get('payments', [TenantPortalController::class, 'payments'])->name('payments.index');
        Route::get('payments/{payment}', [TenantPortalController::class, 'showPayment'])->name('payments.show');
        Route::get('payments/{payment}/receipt', [TenantPortalController::class, 'receipt'])->name('payments.receipt');
        Route::get('payments/{payment}/proof', [TenantPortalController::class, 'proof'])->name('payments.proof');
        Route::get('notifications', [TenantPortalController::class, 'notifications'])->name('notifications.index');
    });

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->middleware('approved')->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->middleware('approved')->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->middleware('approved')->name('profile.destroy');

    Route::get(
        '/maintenance',
        [MaintenanceRequestController::class, 'index']
    )->middleware('approved')->name('maintenance.index');

    Route::get(
        '/maintenance/create',
        [MaintenanceRequestController::class, 'create']
    )->middleware('approved')->name('maintenance.create');

    Route::post(
        '/maintenance',
        [MaintenanceRequestController::class, 'store']
    )->middleware('approved')->name('maintenance.store');

    Route::get(
        '/maintenance/{maintenanceRequest}',
        [MaintenanceRequestController::class, 'show']
    )->middleware('approved')->name('maintenance.show');

    Route::patch(
        '/maintenance/{maintenanceRequest}/complete',
        [MaintenanceRequestController::class, 'complete']
    )->middleware('approved')->name('maintenance.complete');

    Route::middleware(['approved', 'role:admin'])->group(function () {

        Route::get(
            'reports',
            [ReportController::class, 'index']
        )->name('reports.index');

        Route::get(
            'reports/export/{report}',
            [ReportController::class, 'export']
        )->name('reports.export');

        Route::get(
            'archive',
            [ArchiveController::class, 'index']
        )->name('archive.index');

        Route::patch(
            'archive/{type}/{id}/restore',
            [ArchiveController::class, 'restore']
        )->name('archive.restore');

        Route::get(
            'tenants/pending-accounts',
            [TenantController::class, 'pending']
        )->name('tenants.pending');

        Route::get(
            'password-requests',
            [PasswordChangeRequestController::class, 'index']
        )->name('password-requests.index');

        Route::patch(
            'password-requests/{passwordRequest}/approve',
            [PasswordChangeRequestController::class, 'approve']
        )->name('password-requests.approve');

        Route::patch(
            'password-requests/{passwordRequest}/reject',
            [PasswordChangeRequestController::class, 'reject']
        )->name('password-requests.reject');

        Route::post(
            'tenants/link',
            [TenantController::class, 'link']
        )->name('tenants.link');

        Route::patch(
            'tenants/{tenant}/link-user',
            [TenantController::class, 'linkUser']
        )->name('tenants.link-user');

        Route::patch(
            'tenants/{tenant}/unlink-user',
            [TenantController::class, 'unlinkUser']
        )->name('tenants.unlink-user');

        Route::resource('announcements', AnnouncementController::class)->only(['index', 'create', 'store']);

        Route::get(
            '/maintenance/{maintenanceRequest}/edit',
            [MaintenanceRequestController::class, 'edit']
        )->name('maintenance.edit');

        Route::put(
            '/maintenance/{maintenanceRequest}',
            [MaintenanceRequestController::class, 'update']
        )->name('maintenance.update');

        Route::patch(
            '/maintenance/{maintenanceRequest}/approve',
            [MaintenanceRequestController::class, 'approve']
        )->name('maintenance.approve');

        Route::delete(
            '/maintenance/{maintenanceRequest}',
            [MaintenanceRequestController::class, 'destroy']
        )->name('maintenance.destroy');

        Route::resource(
            'apartments',
            ApartmentController::class
        );

        Route::resource(
            'tenants',
            TenantController::class
        );

        Route::resource(
            'leases',
            LeaseController::class
        );

        Route::resource(
            'bills',
            BillController::class
        );

        Route::get('bills/{bill}/invoice', [BillController::class, 'invoice'])->name('bills.invoice');
        Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::patch('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::get('payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        Route::resource(
            'payments',
            PaymentController::class
        );

    });

});

require __DIR__.'/auth.php';
