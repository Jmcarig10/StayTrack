<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function tenantNotifications()
    {
        return $this->hasMany(TenantNotification::class);
    }

    protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'password',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isApproved(): bool
    {
        return $this->isAdmin() || $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return ! $this->isAdmin() && $this->status === 'pending';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
