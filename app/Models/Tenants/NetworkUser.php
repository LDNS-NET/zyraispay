<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class NetworkUser extends Model
{
    use HasFactory;

    protected $table = 'network_users';

    protected $fillable = [
        'account_number',
        'full_name',
        'username',
        'password',
        'phone',
        'email',
        'location',
        'type',
        'package_id',
        'status',
        'registered_at',
        'expires_at',
        'online',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'online' => 'boolean',
    ];
    
    /**
     * Get the mikrotik that owns the network user.
     */
    /**
     * Get all MikroTik routers for the tenant
     */
    public function tenantMikrotiks()
    {
        return app('currentTenant')->mikrotiks();
    }

    protected static function booted()
    {
        static::addGlobalScope('created_by', function ($query) {
            if (Auth::check()) {
                $query->where('created_by', Auth::id());
            }
        });
        static::creating(function ($model) {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
            // Auto-generate account_number if not set
            if (empty($model->account_number)) {
                // Get tenant business name initials
                $tenant = app(\App\Models\Tenant::class);
                $prefix = '';
                if ($tenant && !empty($tenant->business_name)) {
                    $prefix = strtoupper(substr(preg_replace('/\s+/', '', $tenant->business_name), 0, 2));
                } else {
                    $prefix = 'NU';
                }
                do {
                    // 6-digit random number for total 8 chars
                    $accountNumber = $prefix . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                } while (self::where('account_number', $accountNumber)->exists());
                $model->account_number = $accountNumber;
            }
        });
    }

    // This model is for tenant DB only, no tenant_id needed

    // Removed global scope on created_by. All users in the tenant DB are now visible to any tenant user.

    public function package()
    {
        return $this->belongsTo(\App\Models\Package::class, 'package_id');
    }
}

