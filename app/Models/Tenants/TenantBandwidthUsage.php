<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBandwidthUsage extends Model
{
    protected $fillable = [
        'router_id',
        'interface_name',
        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'timestamp',
    ];

    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',
        'timestamp' => 'datetime',
    ];

    // Relationships
    public function router(): BelongsTo
    {
        return $this->belongsTo(TenantMikrotik::class, 'router_id');
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
