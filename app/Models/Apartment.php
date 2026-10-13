<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apartment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_number',
        'building_name',
        'property_address',
        'floor',
        'unit_type',
        'monthly_rent',
        'security_deposit',
        'bedrooms',
        'bathrooms',
        'status',
        'description',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
    ];

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }
}
