<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRouterAlert extends Model
{
    protected $fillable = [
        'router_id',
        'alert_type',
        'message',
        'severity',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function router(): BelongsTo
    {
        return $this->belongsTo(TenantMikrotik::class, 'router_id');
    }

    // Scopes
    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('severity', 'high');
    }

    // Helper methods
    public function isAcknowledged(): bool
    {
        return !is_null($this->acknowledged_at);
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function acknowledge()
    {
        $this->update(['acknowledged_at' => now()]);
    }

    public function resolve()
    {
        $this->update(['resolved_at' => now()]);
    }

    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }
}
