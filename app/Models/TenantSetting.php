<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'category',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public static function forTenant($tenantId, $category)
    {
        return static::where('tenant_id', $tenantId)->where('category', $category)->first();
    }

    // Optionally, add relationship to User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
