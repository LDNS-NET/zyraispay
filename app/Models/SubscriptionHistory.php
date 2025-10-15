<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'action',
        'old_status',
        'new_status',
        'amount',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the subscription history.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the subscription that this history belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id', 'id');
    }

    /**
     * Create a history record for subscription actions.
     */
    public static function createRecord(
        string $tenantId,
        string $subscriptionId,
        string $action,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?float $amount = null,
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'subscription_id' => $subscriptionId,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'amount' => $amount,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope to get records for a specific tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to get records for a specific action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
