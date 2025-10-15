<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class TenantSMSBalance extends Model
{

    protected $table = "tenant_sms_balances";

    protected $fillable = [
        'balance',
        'last_topped_up_at',
    ];

    protected $casts = [
        'last_topped_up_at' => 'datetime',
    ];

    // This model is for tenant DB only, no tenant_id needed
}
