<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class TenantWhatsappGateway extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tenant_id', 'user_id', 'provider', 'api_key', 'api_secret', 'phone_number', 'webhook_url',
        'status_callback_url', 'region', 'custom_parameters', 'is_active', 'label', 'last_used_at', 'created_by'
    ];
    protected $casts = [
        'custom_parameters' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];
    protected $hidden = ['api_key', 'api_secret'];
    public function setApiKeyAttribute($value) { $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null; }
    public function getApiKeyAttribute($value) { return $value ? Crypt::decryptString($value) : null; }
    public function setApiSecretAttribute($value) { $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null; }
    public function getApiSecretAttribute($value) { return $value ? Crypt::decryptString($value) : null; }
    public function tenant() { return $this->belongsTo(Tenant::class, 'tenant_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
