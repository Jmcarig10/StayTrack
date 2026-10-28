<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'event_key',
        'title',
        'body',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
