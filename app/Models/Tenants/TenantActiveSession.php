<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantActiveSession extends Model
{
    protected $fillable = [
        'router_id',
        'user_id',
        'session_id',
        'ip_address',
        'mac_address',
        'bytes_in',
        'bytes_out',
        'connected_at',
        'last_seen_at',
        'status',
    ];

    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'connected_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    // Relationships
    public function router(): BelongsTo
    {
        return $this->belongsTo(TenantMikrotik::class, 'router_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NetworkUser::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDisconnected($query)
    {
        return $query->where('status', 'disconnected');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Helper methods
    public function getBytesInFormatted(): string
    {
        return $this->formatBytes($this->bytes_in);
    }

    public function getBytesOutFormatted(): string
    {
        return $this->formatBytes($this->bytes_out);
    }

    public function getSessionDuration(): string
    {
        if (!$this->connected_at) return 'Unknown';
        
        $start = $this->connected_at;
        $end = $this->last_seen_at ?? now();
        $duration = $end->diffInSeconds($start);
        
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
