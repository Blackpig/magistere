<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\PaymentStatus;
use BlackpigCreatif\Magistere\Enums\PaymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'workshop_id',
        'reference',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
        'contact_organisation',
        'status',
        'payment_status',
        'attendee_count',
        'subtotal',
        'amount_paid',
        'currency',
        'notes',
        'internal_notes',
        'marketing_consent',
        'gdpr_consent',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];

    public function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'attendee_count' => 'integer',
            'subtotal' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'marketing_consent' => 'boolean',
            'gdpr_consent' => 'boolean',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $booking): void {
            if (empty($booking->reference)) {
                $booking->reference = static::generateReference();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class)
            ->withPivot('quantity');
    }

    // -------------------------------------------------------------------------
    // Business logic
    // -------------------------------------------------------------------------

    /**
     * Recalculate amount_paid and payment_status from the payment ledger.
     * Call this after any payment is created, updated, or deleted.
     */
    public function recalculatePaymentStatus(): void
    {
        $net = (float) $this->payments()
            ->get()
            ->sum(fn (Payment $payment) => match ($payment->type) {
                PaymentType::Payment, PaymentType::Adjustment => (float) $payment->amount,
                PaymentType::Refund => -(float) $payment->amount,
            });

        $depositThreshold = (float) ($this->workshop->deposit_amount
            ?? round((float) $this->subtotal * config('magistere.booking.deposit_percentage', 25) / 100, 2));

        $status = match (true) {
            $net < 0 => PaymentStatus::Refunded,
            $net === 0.0 => PaymentStatus::Unpaid,
            $net > (float) $this->subtotal => PaymentStatus::Overpaid,
            $net >= (float) $this->subtotal => PaymentStatus::Paid,
            $net >= $depositThreshold => PaymentStatus::DepositReceived,
            default => PaymentStatus::PartPaid,
        };

        $this->update([
            'amount_paid' => $net,
            'payment_status' => $status,
        ]);
    }

    public static function generateReference(): string
    {
        $prefix = config('magistere.booking.reference_prefix', 'MAG');
        $year = now()->year;

        $count = static::withTrashed()
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%04d', $prefix, $year, $count);
    }

    public function getContactFullNameAttribute(): string
    {
        return trim("{$this->contact_first_name} {$this->contact_last_name}");
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePending(Builder $query): void
    {
        $query->where('status', BookingStatus::Pending);
    }

    public function scopeConfirmed(Builder $query): void
    {
        $query->where('status', BookingStatus::Confirmed);
    }

    public function scopeWaitlisted(Builder $query): void
    {
        $query->where('status', BookingStatus::Waitlisted);
    }
}
