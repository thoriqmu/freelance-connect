<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['project_id', 'client_id', 'freelancer_id', 'transaction_id', 'amount', 'status', 'paid_at', 'released_at'])]
class Payment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the project associated with this payment.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the client who made this payment.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the freelancer who will receive this payment.
     */
    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    /**
     * Get the payment detail record associated with this payment.
     */
    public function paymentDetail(): HasOne
    {
        return $this->hasOne(PaymentDetail::class);
    }

    /**
     * Get the payment fee record associated with this payment.
     */
    public function paymentFee(): HasOne
    {
        return $this->hasOne(PaymentFee::class);
    }

    /**
     * Get the disbursement record associated with this payment.
     */
    public function disbursement(): HasOne
    {
        return $this->hasOne(Disbursement::class);
    }

    /**
     * Get the refund record associated with this payment.
     */
    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }
}
