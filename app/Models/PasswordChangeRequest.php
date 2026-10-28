<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordChangeRequest extends Model
{
    protected $fillable = [
        'email',
        'password_hash',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
