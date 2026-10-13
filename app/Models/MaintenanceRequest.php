<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'apartment_id',
        'title',
        'category',
        'priority',
        'description',
        'status',
        'admin_notes',
        'assigned_to',
        'scheduled_at',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
