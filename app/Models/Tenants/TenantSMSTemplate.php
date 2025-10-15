<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class TenantSMSTemplate extends Model
{
    protected $table = "tenant_sms_templates";

    protected $fillable = [
        'name',
        'content',
        'created_by',
    ];

    // This model is for tenant DB only, no tenant_id needed
}
