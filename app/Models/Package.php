<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\UsesTenantConnection;

class Package extends Model
{
    protected $fillable = [
        'name',
        'type',
        'price',
        'device_limit',
        'duration_value',
        'duration_unit',
        'upload_speed',
        'download_speed',
        'burst_limit',
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

    protected $appends = ['duration_in_days'];

    public function getDurationInDaysAttribute(): int|float|null
    {
        return match ($this->duration_unit) {
            'minutes' => round($this->duration_value / 1440, 2),
            'hours'   => round($this->duration_value / 24, 2),
            'days'    => $this->duration_value,
            'weeks'   => $this->duration_value * 7,
            'months'  => $this->duration_value * 30,
            default   => null,
        };
    }

}

