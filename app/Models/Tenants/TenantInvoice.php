<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class TenantInvoice extends Model
{
    protected $fillable = [
        'user',
        'tenant_invoice',
        'amount',
        'package',
        'issued_on',
        'due_on',
        'created_by'
    ];

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
