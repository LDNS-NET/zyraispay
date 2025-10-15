<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantExpenses extends Model
{

    protected $fillable = [
        'description',
        'amount',
        'incurred_on',
        'category',
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
