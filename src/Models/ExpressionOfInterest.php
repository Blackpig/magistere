<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\EoiSource;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpressionOfInterest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'expressions_of_interest';

    protected $fillable = [
        'workshop_id',
        'course_id',
        'converted_booking_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'attendee_count',
        'message',
        'source',
        'status',
        'token',
        'token_expires_at',
        'notified_at',
    ];

    public function casts(): array
    {
        return [
            'source' => EoiSource::class,
            'status' => EoiStatus::class,
            'attendee_count' => 'integer',
            'token_expires_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $eoi): void {
            if (empty($eoi->token)) {
                $eoi->token = static::generateToken();
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function convertedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'converted_booking_id');
    }

    // -------------------------------------------------------------------------
    // Token management
    // -------------------------------------------------------------------------

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Issue a fresh token and set the expiry window from config.
     */
    public function refreshToken(): void
    {
        $this->update([
            'token' => static::generateToken(),
            'token_expires_at' => now()->addHours(
                config('magistere.booking.token_expiry_hours', 72),
            ),
        ]);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at !== null && now()->isAfter($this->token_expires_at);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeInterest(Builder $query): void
    {
        $query->where('source', EoiSource::Interest);
    }

    public function scopeWaitlist(Builder $query): void
    {
        $query->where('source', EoiSource::Waitlist);
    }

    public function scopeNew(Builder $query): void
    {
        $query->where('status', EoiStatus::New);
    }

    public function scopeNotifiable(Builder $query): void
    {
        $query->where('status', EoiStatus::New)
            ->where('source', EoiSource::Interest);
    }
}
