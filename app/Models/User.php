<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // NOTE: This model is used for super admins in the central DB and for tenant admins in tenant DBs only.
    // Do not use for WiFi users (network users), which are managed in the network_users table per tenant.

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'username',
        'business_name',
        'is_super_admin',
        'email_verified_at',
        'last_login_at',
        
    ];

    protected $casts = [
        'email_verified_at'=> 'datetime',
        'is_super_admin' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getIsSuperAdminAttribute(): bool
    {
        return $this->attributes['is_super_admin'] ?? false;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
