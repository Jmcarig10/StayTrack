<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lease_id',
        'billing_month',
        'due_date',
        'amount',
        'late_fee',
        'status',
        'description',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function getTotalAmountAttribute()
    {
        return (float) $this->amount + (float) $this->late_fee;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
