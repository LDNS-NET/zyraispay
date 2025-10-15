<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nothing extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'package_id',
        'amount',
        'status',
        'intasend_reference',
        'intasend_checkout_id',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
