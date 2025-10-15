<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'status',
        'trial_starts_at',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'next_billing_date',
        'amount',
        'currency',
        'payment_method',
        'last_payment_at',
        'failed_payment_count',
        'notes',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'last_payment_at' => 'datetime',
        'amount' => 'decimal:2',
        'failed_payment_count' => 'integer',
    ];

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if the subscription is in trial period.
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at && 
               now()->lessThan($this->trial_ends_at);
    }

    /**
     * Check if the subscription is active (paid and not expired).
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->current_period_ends_at && 
               now()->lessThan($this->current_period_ends_at);
    }

    /**
     * Check if the subscription is expired.
     */
    public function isExpired(): bool
    {
        if ($this->isOnTrial()) {
            return false;
        }

        return $this->status === 'expired' || 
               ($this->current_period_ends_at && now()->greaterThan($this->current_period_ends_at));
    }

    /**
     * Check if the subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Get the days remaining in trial.
     */
    public function getTrialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }
        // Use the trial_ends_at and trial_starts_at from the DB
        $days = $this->trial_starts_at && $this->trial_ends_at
            ? $this->trial_starts_at->diffInDays($this->trial_ends_at, false)
            : 0;
        // If still on trial, show remaining days
        $remaining = $this->trial_ends_at && now()->lessThan($this->trial_ends_at)
            ? now()->diffInDays($this->trial_ends_at, false)
            : 0;
        return $remaining > 0 ? $remaining : 1;
    }

    /**
     * Get the days and hours remaining in trial period.
     */
    public function getTrialDurationRemaining(): array
    {
        if (!$this->isOnTrial() || !$this->trial_ends_at) {
            return ['days' => 0, 'hours' => 0];
        }
        $now = now();
        $diff = $now->diff($this->trial_ends_at);
        $days = $diff->days;
        $hours = $diff->h;
        // Always at least 1 day if still on trial
        if ($now->lessThan($this->trial_ends_at) && $days === 0 && $hours > 0) {
            $days = 1;
        }
        return [
            'days' => $days,
            'hours' => $hours
        ];
    }

    /**
     * Get the days remaining in current period.
     */
    public function getCurrentPeriodDaysRemaining(): int
    {
        if (!$this->isActive()) {
            return 0;
        }
        // Use the current_period_starts_at and current_period_ends_at from the DB
        $days = $this->current_period_starts_at && $this->current_period_ends_at
            ? $this->current_period_starts_at->diffInDays($this->current_period_ends_at, false)
            : 0;
        // If still in period, show remaining days
        $remaining = $this->current_period_ends_at && now()->lessThan($this->current_period_ends_at)
            ? now()->diffInDays($this->current_period_ends_at, false)
            : 0;
        return $remaining > 0 ? $remaining : 1;
    }

    /**
     * Start a new billing period.
     */
    public function startNewPeriod(): void
    {
        $now = now();
        $oldStatus = $this->status;
        
        $this->update([
            'status' => 'active',
            'current_period_starts_at' => $now,
            'current_period_ends_at' => $now->copy()->addDays(30),
            'next_billing_date' => $now->copy()->addDays(30),
            'last_payment_at' => $now,
            'failed_payment_count' => 0,
        ]);

        // Create history record
        \App\Models\SubscriptionHistory::createRecord(
            $this->tenant_id,
            $this->id,
            'renewed',
            $oldStatus,
            'active',
            $this->amount,
            'Subscription renewed for 30 days',
            ['period_start' => $now, 'period_end' => $now->copy()->addDays(30)]
        );
    }

    /**
     * Mark subscription as expired.
     */
    public function markAsExpired(): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => 'expired',
        ]);

        // Create history record
        \App\Models\SubscriptionHistory::createRecord(
            $this->tenant_id,
            $this->id,
            'expired',
            $oldStatus,
            'expired',
            null,
            'Subscription expired due to non-payment',
            ['expired_at' => now()]
        );
    }

    /**
     * Mark subscription as past due.
     */
    public function markAsPastDue(): void
    {
        $oldStatus = $this->status;
        $newFailedCount = $this->failed_payment_count + 1;
        
        $this->update([
            'status' => 'past_due',
            'failed_payment_count' => $newFailedCount,
        ]);

        // Create history record
        \App\Models\SubscriptionHistory::createRecord(
            $this->tenant_id,
            $this->id,
            'payment_failed',
            $oldStatus,
            'past_due',
            $this->amount,
            "Payment failed (attempt #{$newFailedCount})",
            ['failed_count' => $newFailedCount]
        );
    }

    /**
     * Create a new subscription for a tenant.
     */
    public static function createForTenant(Tenant $tenant, int $trialDays = 10): self
    {
        $trialStart = $tenant->created_at;
        $trialEnd = $tenant->created_at->copy()->addDays($trialDays);
        $subscription = self::create([
            'tenant_id' => $tenant->id,
            'status' => 'trial',
            'trial_starts_at' => $trialStart,
            'trial_ends_at' => $trialEnd,
            'amount' => config('subscription.monthly_amount', 5000.00), // Default KES 5000
            'currency' => 'KES',
            'payment_method' => 'mpesa',
        ]);

        // Create history record
        \App\Models\SubscriptionHistory::createRecord(
            $tenant->id,
            $subscription->id,
            'created',
            null,
            'trial',
            $subscription->amount,
            "New subscription created with {$trialDays}-day trial",
            ['trial_days' => $trialDays, 'trial_ends_at' => $trialEnd]
        );

        return $subscription;
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('current_period_ends_at', '>', now());
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere('current_period_ends_at', '<', now());
        });
    }

    /**
     * Scope to get subscriptions ending soon.
     */
    public function scopeEndingSoon($query, int $days = 3)
    {
        return $query->where('current_period_ends_at', '<=', now()->addDays($days))
                    ->where('current_period_ends_at', '>', now());
    }
}
