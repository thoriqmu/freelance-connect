<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    protected $fillable = [
        'freelancer_id',
        'project_id',
        'payment_id',
        'amount',
        'status',
        'withdrawn_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'withdrawn_at' => 'datetime',
    ];

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
