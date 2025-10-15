<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class TenantPaymentGateway extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tenant_id', 'provider', 'api_key', 'payout_method', 'is_active', 'label', 'last_used_at', 'created_by'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];
    protected $hidden = ['api_key'];
    public function setApiKeyAttribute($value) { $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null; }
    public function getApiKeyAttribute($value) { return $value ? Crypt::decryptString($value) : null; }
    public function tenant() { return $this->belongsTo(Tenant::class, 'tenant_id'); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
