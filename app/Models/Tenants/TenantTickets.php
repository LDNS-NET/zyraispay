<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class TenantTickets extends Model
{
    protected $fillable = [
        'ticket_number',
        'client_type',
        'client_id',
        'status',
        'priority',
        'description',
        'created_by'
    ];

    public function client()
    {
        return $this->morphTo(null, 'client_type', 'client_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $ticket->ticket_number = 'TCK-' . strtoupper(Str::random(8));
        });
    }


    //tenant Specific
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


