<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'transaction_id', 'amount', 'status', 'payment_method'])]
class Payment extends Model
{
    /**
     * Get the project associated with this payment.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
