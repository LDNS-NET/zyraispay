<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantHotspotSetting extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tenant_id', 'portal_template', 'logo_url', 'user_prefix', 'prune_inactive_days', 'description', 'allowed_networks', 'default_package_id', 'captive_portal_url', 'advanced_options', 'is_active', 'created_by', 'last_updated_by'
    ];
    protected $casts = [
        'allowed_networks' => 'array',
        'advanced_options' => 'array',
        'is_active' => 'boolean',
    ];
    public function tenant() { return $this->belongsTo(Tenant::class, 'tenant_id'); }
}
