<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
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
// use App\Http\Controllers\Tenants\DashboardController; // removed, replaced by TenantDashboardController
use App\Http\Controllers\Tenants\TenantDashboardController;
/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});




// Define tenant middleware
$tenantMiddleware = ['auth', 'verified', \App\Http\Middleware\CheckSubscriptionStatus::class];

Route::middleware($tenantMiddleware)
    ->prefix('tenants')
    ->name('tenants.')
    ->group(function () {



    Route::get('/dashboard', [TenantDashboardController::class, 'index'])
    ->name('tenants.dashboard.index');


    //tenants packages
    Route::resource('packages', PackageController::class)->except(['show']);
    Route::delete('/tenants/packages/bulk-delete', [PackageController::class, 'bulkDelete'])->name('packages.bulk-delete');

    // Invoice generation for payments
    Route::get('payments/{payment}/invoice', [\App\Http\Controllers\Tenants\TenantPaymentController::class, 'invoice'])->name('payments.invoice');

        //network users( tenants )
        Route::resource('users', TenantUserController::class)->except(['show']);
        Route::delete('/tenants/users/bulk-delete', [TenantUserController::class, 'destroyBulk'])->name('users.bulk-delete');



        //vouchers
        Route::resource('vouchers', VoucherController::class);
        Route::post('/vouchers/{voucher}/send', [VoucherController::class, 'send'])->name('vouchers.send');
        Route::delete('/tenants/vouchers/bulk-delete', [VoucherController::class, 'bulkDelete'])->name('vouchers.bulk-delete');


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
        Route::get('settings/sms-gateway/json', [TenantSmsGatewayController::class, 'json'])->name('settings.sms_gateway.json');

        Route::get('settings/whatsapp-gateway', [TenantWhatsappGatewayController::class, 'edit'])->name('settings.whatsapp_gateway.edit');
        Route::post('settings/whatsapp-gateway', [TenantWhatsappGatewayController::class, 'update'])->name('settings.whatsapp_gateway.update');

        Route::get('settings/hotspot', [TenantHotspotSettingsController::class, 'edit'])->name('settings.hotspot.edit');
        Route::post('settings/hotspot', [TenantHotspotSettingsController::class, 'update'])->name('settings.hotspot.update');

        Route::get('settings/general', [TenantGeneralSettingsController::class, 'edit'])->name('settings.general.edit');
        Route::post('settings/general', [TenantGeneralSettingsController::class, 'update'])->name('settings.general.update');

        Route::get('settings/notifications', [TenantNotificationSettingsController::class, 'edit'])->name('settings.notifications.edit');
        Route::post('settings/notifications', [TenantNotificationSettingsController::class, 'update'])->name('settings.notifications.update');
    });

