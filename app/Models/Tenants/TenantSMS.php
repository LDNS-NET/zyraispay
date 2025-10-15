<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class TenantSMS extends Model
{
    protected $table = "tenant_sms";

    protected $fillable = [
        'recipient',
        'recipient_phone',
        'message',
        'status',
        'cost',
        'created_by',
        'sent_at',
        'response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'response' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }

    // This model is for tenant DB only, no tenant_id needed
}


