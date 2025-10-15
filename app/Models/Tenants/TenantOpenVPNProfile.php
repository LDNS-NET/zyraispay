<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantOpenVPNProfile extends Model
{
    protected $fillable = [
        'config_path',
        'client_cert_path',
        'client_key_path',
        'ca_cert_path',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function routers(): HasMany
    {
        return $this->hasMany(TenantMikrotik::class, 'openvpn_profile_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
