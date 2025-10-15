<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'status',
        'expires_at',
        'package_id',
        'created_by',
        'used_by',
        'sent_to',
        'sent_at',
        'note',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the package that this voucher is for.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the user who created this voucher.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who used this voucher.
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Get the user this voucher was sent to.
     */
    public function sentTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_to');
    }

    /**
     * Check if the voucher is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    /**
     * Check if the voucher can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }
} 