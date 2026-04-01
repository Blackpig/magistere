<?php

namespace BlackpigCreatif\Magistere\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'booking_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'dietary_requirements',
        'accessibility_requirements',
        'notes',
        'is_primary_contact',
        'checked_in_at',
    ];

    public function casts(): array
    {
        return [
            'is_primary_contact' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class, 'attendee_extra')
            ->withPivot('quantity');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }
}
