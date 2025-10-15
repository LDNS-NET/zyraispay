<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $fillable = [
        "user_id",
        "recipient_name",
        "recipient_phone",
        "message",
        "status",
        "sent_at",
        "response",
    ];

    protected $casts = [
        "sent_at" => "datetime",
        "response" => "array", // Optional: decode JSON into PHP array
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
