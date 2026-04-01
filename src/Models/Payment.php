<?php

namespace BlackpigCreatif\Magistere\Models;

use App\Models\User;
use BlackpigCreatif\Magistere\Enums\PaymentMethod;
use BlackpigCreatif\Magistere\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'attendee_id',
        'recorded_by',
        'amount',
        'currency',
        'method',
        'reference',
        'paid_at',
        'notes',
        'type',
    ];

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
            'method' => PaymentMethod::class,
            'type' => PaymentType::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        /** Recalculate booking payment status whenever a payment is saved or deleted. */
        static::saved(function (self $payment): void {
            $payment->booking->recalculatePaymentStatus();
        });

        static::deleted(function (self $payment): void {
            $payment->booking->recalculatePaymentStatus();
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function recordedBy(): BelongsTo
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('auth.providers.users.model', User::class);

        return $this->belongsTo($userModel, 'recorded_by');
    }

    public function isRefund(): bool
    {
        return $this->type === PaymentType::Refund;
    }
}
