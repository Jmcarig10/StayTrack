<?php

namespace App\Models;

use App\Models\Concerns\Archivable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use Archivable, HasFactory, SoftDeletes;

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
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'approved_at' => 'datetime',
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
