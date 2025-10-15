<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantGeneralSetting extends Model
{
    use SoftDeletes;

    protected $table = 'tenant_general_settings';

    protected $fillable = [
        'tenant_id',
        'business_name',
        'business_description',
        'business_type',
        'registration_number',
        'tax_number',
        'primary_email',
        'support_email',
        'primary_phone',
        'support_phone',
        'whatsapp_number',
        'address_street',
        'address_city',
        'address_state',
        'address_postal_code',
        'address_country',
        'website_url',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'business_hours',
        'timezone',
        'currency',
        'language',
    ];

    protected $hidden = [
        // Add sensitive fields here if any
    ];

    protected $casts = [
        'business_hours' => 'array',
    ];

    // Example: Encrypt sensitive fields (if needed)
    // protected $encryptable = ['tax_number'];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}