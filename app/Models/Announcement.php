<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['title', 'message', 'published_at', 'expires_at', 'is_active', 'created_by'];

    protected $casts = ['published_at' => 'date', 'expires_at' => 'date', 'is_active' => 'boolean'];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
