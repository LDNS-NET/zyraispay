<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantGeneralSetting extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tenant_id', 'business_type', 'theme', 'deleted_at', 'contact_email', 'support_email', 'phone', 'support_phone',
        'whatsapp', 'address', 'city', 'state', 'postal_code', 'country', 'website', 'facebook', 'twitter',
        'instagram', 'business_hours', 'timezone', 'currency', 'language', 'created_by'
    ];
    protected $casts = [
        'business_hours' => 'string',
    ];
    public function tenant() { return $this->belongsTo(Tenant::class, 'tenant_id'); }
}
