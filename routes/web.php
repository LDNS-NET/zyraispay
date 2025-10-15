<?php


use App\Http\Controllers\Tenants\TenantHello;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\Admin\ActiveUserController;
use App\Http\Controllers\Admin\SMSController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Tenants\TenantLeadController;
use App\Http\Controllers\Tenants\TenantTicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Tenants\PackageController;
use App\Http\Controllers\Tenants\TenantUserController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenants\VoucherController;
use App\Http\Controllers\Tenants\TenantEquipmentController;
use App\Http\Controllers\Tenants\TenantSMSBalanceController;
use App\Http\Controllers\Tenants\TenantSMSController;
use App\Http\Middleware\LocalTenantFallback;
use App\Http\Controllers\Tenants\TenantSMSTemplateController;
use App\Http\Controllers\Tenants\TenantSettingsController;
use App\Http\Controllers\Tenants\TenantPayoutSettingsController;
use App\Http\Controllers\Tenants\TenantPaymentGatewayController;
use App\Http\Controllers\Tenants\TenantSmsGatewayController;
use App\Http\Controllers\Tenants\TenantWhatsappGatewayController;
use App\Http\Controllers\Tenants\TenantHotspotSettingsController;
use App\Http\Controllers\Tenants\TenantGeneralSettingsController;
use App\Http\Controllers\Tenants\TenantNotificationSettingsController;
use App\Http\Controllers\Tenants\TenantMikrotikController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\DashboardController as ControllersDashboardController;
use App\Http\Controllers\Tenants\CaptivePortalController;
use App\Http\Controllers\Tenants\TenantPaymentController;
use App\Http\Controllers\Tenants\TenantExpensesController;
use App\Http\Controllers\Tenants\TenantInvoiceController;
use App\Http\Controllers\Tenants\DashboardController;
use App\Http\Controllers\Tenants\TenantActiveUsersController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\SubscriptionAnalyticsController;
use App\Http\Controllers\Tenants\NetworkUserDetailsController;

// �� Central Domain (e.g. 127.0.0.1)
Route::middleware('web')->group(function () {
    // Landing page
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    // Auth routes for testing registration on central domain
    // 🔴 REMOVE IN PRODUCTION
    require __DIR__ . '/auth.php';
});






// 🔐 Admin panel for super admin on central domain
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //Active Users
    Route::get('/active-users', [ActiveUserController::class, 'activeUsers'])->name('admin.active-users');
    Route::put('/active-users/{tenant_id}/{user_id}', [ActiveUserController::class, 'update'])->name('admin.active-users.update');
    Route::delete('/active-users/{tenant_id}/{user_id}', [ActiveUserController::class, 'destroy'])->name('admin.active-users.destroy');



    //sms integration and sending
    // SMS management
    Route::get('/sms', [SMSController::class, 'index'])->name('admin.sms.index');
    Route::post('/sms/send', [SMSController::class, 'send'])->name('admin.sms.send');
    Route::get('/sms/export', [SMSController::class, 'export'])->name('admin.sms.export'); // ✅ NEW
    Route::delete('/sms/{sms}', [SMSController::class, 'destroy'])->name('admin.sms.destroy');
    Route::delete('/sms', [SMSController::class, 'destroyBulk'])->name('admin.sms.bulk-destroy'); // ✅ FIXED



    // Equipment management
    //Route::get('/equipment', function () {return Inertia::render('Admin/Equipment/Index');})->name('admin.equipment.index');



    Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{id}', [TenantController::class, 'show'])->name('tenants.show');
    Route::get('/tenants/{id}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/tenants/{id}', [TenantController::class, 'update'])->name('tenants.update');
    Route::delete('/tenants/{id}', [TenantController::class, 'destroy'])->name('tenants.destroy');

    Route::resource('leads', LeadController::class)->only(['index', 'create', 'store']);

    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

    // IntaSend Wallets
    Route::get('/wallets', [WalletController::class, 'index'])->name('admin.wallets.index');
    Route::get('/wallets/{walletId}', [WalletController::class, 'show'])->name('admin.wallets.show');

    // Subscription Analytics
    Route::get('/subscriptions', [SubscriptionAnalyticsController::class, 'index'])->name('admin.subscriptions.index');
    Route::get('/subscriptions/statistics', [SubscriptionAnalyticsController::class, 'statistics'])->name('admin.subscriptions.statistics');
    Route::post('/subscriptions/send-reminders', [SubscriptionAnalyticsController::class, 'sendReminders'])->name('admin.subscriptions.send-reminders');
    Route::post('/subscriptions/process-expired', [SubscriptionAnalyticsController::class, 'processExpired'])->name('admin.subscriptions.process-expired');


});

// 🏢 Tenant routes (only for subdomains like school1.localhost, NOT 127.0.0.1)
/*Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    require __DIR__ . '/auth.php';

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('tenant.dashboard');
});*/

//remove this after dev is finished


// routes/tenant.php - Tenant routes are defined in routes/tenant.php with proper tenancy middleware





Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});


//Tenants Middleware and routes grouping is done header_register_callback
$tenantMiddleware = ['auth', 'verified', \App\Http\Middleware\CheckSubscriptionStatus::class];

if (app()->environment('local')) {
    $tenantMiddleware[] = LocalTenantFallback::class;
} else {
    $tenantMiddleware[] = InitializeTenancyByDomain::class;
    $tenantMiddleware[] = PreventAccessFromCentralDomains::class;
}

Route::middleware($tenantMiddleware)
    ->prefix('tenants')
    ->name('tenants.')
    ->group(function () {


        //dashboard
        Route::resource('dashboard', DashboardController::class);

        //Active Users
        Route::resource('activeusers', TenantActiveUsersController::class)->except(['show']);

        //tenants packages
        Route::resource('packages', PackageController::class)->except(['show']);
        Route::delete('/tenants/packages/bulk-delete', [PackageController::class, 'bulkDelete'])->name('packages.bulk-delete');

        //network users( tenants )
        Route::resource('users', TenantUserController::class);
        Route::delete('/tenants/users/bulk-delete', [TenantUserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/details', [TenantUserController::class, 'update'])->name('users.details.update');
    

        //Leads
        Route::resource('leads', TenantLeadController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('tenants/leads/bulk-delete', [TenantLeadController::class, 'bulkDelete'])->name('leads.bulk-delete');

        //tickets
        Route::resource('tickets', TenantTicketController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('tenants/tickets/bulk-delete', [TenantTicketController::class, 'bulkDelete'])->name('tickets.bulk-delete');
        Route::put('/tenants/tickets/{ticket}/resolve', [TenantTicketController::class, 'resolve'])->name('tickets.resolve');

        //Equipment
        Route::resource('equipment', TenantEquipmentController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('/tenants/equipment/bulk-delete', [TenantEquipmentController::class, 'bulkDelete' ])->name('equipment.bulk-delete');
        
        //vouchers
        Route::resource('vouchers', VoucherController::class);
        Route::post('/vouchers/{voucher}/send', [VoucherController::class, 'send'])->name('vouchers.send');
        Route::delete('/tenants/vouchers/bulk-delete', [VoucherController::class, 'bulkDelete'])->name('vouchers.bulk-delete');

        //Payments
        Route::resource('payment', TenantPaymentController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('/tenants/payments/bulk-delete', [TenantPaymentController::class, 'bulkDelete' ])->name('payments.bulk-delete');

        //Invoices
        Route::resource('invoices', TenantInvoiceController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('/tenants/invoices/bulk-delete', [TenantInvoiceController::class, 'bulkDelete'])->name('invoices.bulk-delete');

        //Expenses
        Route::resource('expenses', TenantExpensesController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('/tenants/expenses/bulk-delete', [TenantExpensesController::class, 'bulkDelete' ])->name('expenses.bulk-delete');
       
        //Dashboard
        Route::resource('dashboard', DashboardController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::delete('/tenants/equipment/bulk-delete', [TenantEquipmentController::class, 'bulkDelete' ])->name('equipment.bulk-delete');

        //SMS
        Route::resource('sms', TenantSMSController::class)->only(['index','store', 'update', 'destroy','show']);
        Route::resource('sms-templates', TenantSMSTemplateController::class)->except(['show']);
        //Route::resource ('sms_balance', TenantSMSBalanceController::class, 'index')->name('sms.balance');

        Route::resource('mikrotiks', TenantMikrotikController::class);
        Route::get('mikrotiks/{mikrotik}/test-connection', [TenantMikrotikController::class, 'testConnection'])->name('mikrotiks.testConnection');
        Route::get('mikrotiks/{mikrotik}/ping', [TenantMikrotikController::class, 'pingRouter'])->name('mikrotiks.ping');
        Route::post('mikrotiks/validate', [TenantMikrotikController::class, 'validateRouter'])->name('mikrotiks.validate');
        Route::get('mikrotiks/{mikrotik}/download-setup-script', [TenantMikrotikController::class, 'downloadSetupScript'])->name('mikrotiks.downloadSetupScript');
        Route::get('mikrotiks/{mikrotik}/download-radius-script', [TenantMikrotikController::class, 'downloadRadiusScript'])->name('mikrotiks.downloadRadiusScript');
        Route::get('mikrotiks/{mikrotik}/remote-management', [TenantMikrotikController::class, 'remoteManagement'])->name('mikrotiks.remoteManagement');
        Route::get('mikrotiks/{mikrotik}/ca.crt', [TenantMikrotikController::class, 'downloadCACert'])->name('mikrotiks.downloadCACert');
        Route::get('mikrotiks/{mikrotik}/reprovision', [TenantMikrotikController::class, 'reprovision'])->name('mikrotiks.reprovision');




        // Tenant settings routes



        Route::get('/settings', [TenantSettingsController::class, 'index'])->name('settings.index');

        Route::get('settings/general', [TenantGeneralSettingsController::class, 'edit'])->name('settings.general.edit');
        Route::post('settings/general', [TenantGeneralSettingsController::class, 'update'])->name('settings.general.update');

        Route::get('settings/payout', [TenantPayoutSettingsController::class, 'edit'])->name('settings.payout.edit');
        Route::post('settings/payout', [TenantPayoutSettingsController::class, 'update'])->name('settings.payout.update');

        Route::get('settings/payment-gateway', [TenantPaymentGatewayController::class, 'edit'])->name('settings.payment_gateway.edit');
        Route::post('settings/payment-gateway', [TenantPaymentGatewayController::class, 'update'])->name('settings.payment_gateway.update');
        Route::post('settings/payment-gateway/test', [TenantPaymentGatewayController::class, 'test'])->name('settings.payment_gateway.test');

        Route::get('settings/sms-gateway', [TenantSmsGatewayController::class, 'edit'])->name('settings.sms_gateway.edit');
        Route::post('settings/sms-gateway', [TenantSmsGatewayController::class, 'update'])->name('settings.sms_gateway.update');

        Route::get('settings/whatsapp-gateway', [TenantWhatsappGatewayController::class, 'edit'])->name('settings.whatsapp_gateway.edit');
        Route::post('settings/whatsapp-gateway', [TenantWhatsappGatewayController::class, 'update'])->name('settings.whatsapp_gateway.update');

        Route::get('settings/hotspot', [TenantHotspotSettingsController::class, 'edit'])->name('settings.hotspot.edit');
        Route::post('settings/hotspot', [TenantHotspotSettingsController::class, 'update'])->name('settings.hotspot.update');

        Route::get('settings/general', [TenantGeneralSettingsController::class, 'edit'])->name('settings.general.edit');
        Route::post('settings/general', [TenantGeneralSettingsController::class, 'update'])->name('settings.general.update');

        Route::get('settings/notifications', [TenantNotificationSettingsController::class, 'edit'])->name('settings.notifications.edit');
        Route::post('settings/notifications', [TenantNotificationSettingsController::class, 'update'])->name('settings.notifications.update');
    });

// Captive portal routes (public access for WiFi users)
Route::middleware(['web'])->group(function () {
    // Captive Portal UI page
    Route::get('/captive-portal', function () {
        return Inertia::render('Tenants/CaptivePortal/Index');
    })->name('captive-portal');

    // Fetch available hotspot packages
    Route::get('/hotspot/packages', [CaptivePortalController::class, 'packages']);

    // Login with username & password (Hotspot)
    Route::post('/hotspot/login', [CaptivePortalController::class, 'login']);

    // Login using a voucher
    Route::post('/hotspot/voucher', [CaptivePortalController::class, 'voucher']);

    // Pay for access
    Route::post('/hotspot/pay', [CaptivePortalController::class, 'pay']);

    // Callback from IntaSend after payment
    Route::post('/hotspot/payment/callback', [CaptivePortalController::class, 'paymentCallback']);
});

Route::get('/captive-portal/tenant', function () {
    $tenant = tenant();
    return response()->json([
        'business_name' => $tenant?->business_name ?? 'Hotspot',
        'phone' => $tenant?->phone ?? '',
    ]);
});

// Subscription routes (accessible even when subscription is expired)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/subscription/expired', [SubscriptionController::class, 'expired'])->name('subscription.expired');
    Route::get('/subscription/payment', [SubscriptionController::class, 'payment'])->name('subscription.payment');
    Route::post('/subscription/payment/process', [SubscriptionController::class, 'processPayment'])->name('subscription.payment.process');
    Route::post('/subscription/payment/callback', [SubscriptionController::class, 'paymentCallback'])->name('subscription.payment.callback');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/status', [SubscriptionController::class, 'status'])->name('subscription.status');
});




