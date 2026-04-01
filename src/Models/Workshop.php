<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workshop extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'location_id',
        'title',
        'slug',
        'summary',
        'description',
        'starts_at',
        'ends_at',
        'registration_opens_at',
        'registration_closes_at',
        'min_capacity',
        'max_capacity',
        'price',
        'deposit_amount',
        'currency',
        'status',
        'featured_image',
        'meta',
        'notes',
    ];

    public function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'description' => 'array',
            'meta' => 'array',
            'status' => WorkshopStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'min_capacity' => 'integer',
            'max_capacity' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(Trainer::class)
            ->withPivot(['role', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function itineraryItems(): HasMany
    {
        return $this->hasMany(ItineraryItem::class)
            ->orderBy('day')
            ->orderBy('sort_order');
    }

    public function extras(): HasMany
    {
        return $this->hasMany(Extra::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function expressionsOfInterest(): HasMany
    {
        return $this->hasMany(ExpressionOfInterest::class);
    }

    // -------------------------------------------------------------------------
    // Computed capacity properties
    // -------------------------------------------------------------------------

    /**
     * Effective capacity is the most restrictive of:
     * course max_capacity (absolute ceiling), location max_capacity, and workshop max_capacity.
     * Course ceiling can never be exceeded.
     */
    public function effectiveCapacity(): int
    {
        $courseCeiling = $this->course->max_capacity;

        $overrides = array_filter(
            [$this->max_capacity, $this->location?->max_capacity],
            fn ($value) => $value !== null,
        );

        return empty($overrides) ? $courseCeiling : min($courseCeiling, ...$overrides);
    }

    /**
     * Sum of attendees across active (confirmed + completed) bookings.
     */
    public function attendeesCount(): int
    {
        return $this->bookings()
            ->whereIn('status', [
                BookingStatus::Confirmed->value,
                BookingStatus::Completed->value,
            ])
            ->sum('attendee_count');
    }

    public function availableSpaces(): int
    {
        return max(0, $this->effectiveCapacity() - $this->attendeesCount());
    }

    public function isFull(): bool
    {
        return $this->availableSpaces() <= 0;
    }

    public function isOpenForBooking(): bool
    {
        if (! in_array($this->status, [WorkshopStatus::Published, WorkshopStatus::Confirmed])) {
            return false;
        }

        $now = now();

        if ($this->registration_opens_at && $now->lt($this->registration_opens_at)) {
            return false;
        }

        if ($this->registration_closes_at && $now->gt($this->registration_closes_at)) {
            return false;
        }

        return ! $this->isFull();
    }

    /**
     * Human-readable capacity hint for Filament forms.
     * e.g. "Course max: 8 · Location max: 6 · Effective: 6"
     */
    public function capacityHint(): string
    {
        $parts = ['Course max: ' . $this->course->max_capacity];

        if ($this->location?->max_capacity !== null) {
            $parts[] = 'Location max: ' . $this->location->max_capacity;
        }

        if ($this->max_capacity !== null) {
            $parts[] = 'Workshop max: ' . $this->max_capacity;
        }

        $parts[] = 'Effective: ' . $this->effectiveCapacity();

        return implode(' · ', $parts);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Display title: workshop override if set, otherwise falls back to course title.
     * Returns the array if translatable fields are in use.
     */
    public function getDisplayTitleAttribute(): mixed
    {
        return $this->title ?: $this->course?->title;
    }

    public function getDisplaySummaryAttribute(): mixed
    {
        return $this->summary ?: $this->course?->summary;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePublished(Builder $query): void
    {
        $query->where('status', WorkshopStatus::Published);
    }

    public function scopeConfirmed(Builder $query): void
    {
        $query->where('status', WorkshopStatus::Confirmed);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->whereIn('status', [WorkshopStatus::Published, WorkshopStatus::Confirmed])
            ->where('starts_at', '>=', now());
    }
}
