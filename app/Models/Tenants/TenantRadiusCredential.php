<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class TenantRadiusCredential extends Model
{
    protected $fillable = [
        'secret',
        'ip_range',
        'nas_identifier',
    ];

    protected $hidden = [
        'secret',
    ];

    // Helper methods
    public function getFormattedSecret(): string
    {
        return str_repeat('*', strlen($this->secret));
    }
}
