<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRouterLog extends Model
{
    protected $fillable = [
        'router_id',
        'action',
        'message',
        'status',
        'response_data',
        'execution_time',
    ];

    protected $casts = [
        'response_data' => 'array',
        'execution_time' => 'integer',
    ];

    // Relationships
    public function router(): BelongsTo
    {
        return $this->belongsTo(TenantMikrotik::class, 'router_id');
    }

    // Scopes
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
