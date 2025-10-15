<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TenantMikrotik extends Model
{
    protected $fillable = [
        'name',
        'ip_address',
        'api_port',
        'ssh_port',
        'openvpn_profile_id',
        'router_username',
        'router_password',
        'connection_type',
        'last_seen_at',
        'status',
        'model',
        'os_version',
        'uptime',
        'cpu_usage',
        'memory_usage',
        'temperature',
        'notes',
        'sync_token',
        'created_by',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'uptime' => 'integer',
        'cpu_usage' => 'decimal:2',
        'memory_usage' => 'decimal:2',
        'temperature' => 'decimal:2',
    ];

    protected $hidden = [
        'router_password',
    ];

    // Encrypt password when setting
    public function setRouterPasswordAttribute($value)
    {
        $this->attributes['router_password'] = Crypt::encryptString($value);
    }

    // Decrypt password when getting
    public function getRouterPasswordAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    // Relationships
    public function logs(): HasMany
    {
        return $this->hasMany(TenantRouterLog::class, 'router_id');
    }

    public function bandwidthUsage(): HasMany
    {
        return $this->hasMany(TenantBandwidthUsage::class, 'router_id');
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(TenantActiveSession::class, 'router_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(TenantRouterAlert::class, 'router_id');
    }

    public function openvpnProfile(): BelongsTo
    {
        return $this->belongsTo(TenantOpenVPNProfile::class, 'openvpn_profile_id');
    }

    // Scopes
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    // Helper methods
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function getUptimeFormatted(): string
    {
        if (!$this->uptime) return 'Unknown';
        
        $days = floor($this->uptime / 86400);
        $hours = floor(($this->uptime % 86400) / 3600);
        $minutes = floor(($this->uptime % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }

    public function getConnectionUrl(): string
    {
        switch ($this->connection_type) {
            case 'api':
                return "http://{$this->ip_address}:{$this->api_port}";
            case 'ssh':
                return "ssh://{$this->ip_address}:{$this->ssh_port}";
            case 'ovpn':
                return "ovpn://{$this->ip_address}";
            default:
                return $this->ip_address;
        }
    }

    protected static function booted()
    {
        static::addGlobalScope('created_by', function ($query) {
            if (auth()->check()) {
                $query->where('created_by', auth()->id());
            }
        });
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
