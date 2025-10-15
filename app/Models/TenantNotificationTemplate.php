<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantNotificationTemplate extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tenant_id', 'type', 'channel', 'channel_type', 'template_type', 'subject', 'message_template', 'variables', 'description', 'is_active', 'created_by'
    ];
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
    public function tenant() { return $this->belongsTo(Tenant::class, 'tenant_id'); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
