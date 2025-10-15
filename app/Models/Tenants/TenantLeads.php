<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class TenantLeads extends Model
{

    protected $table = 'tenant_leads';

    protected $fillable = [
        'name',
        'address',
        'phone_number',
        'email_address',
        'status',
        'created_by',
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
