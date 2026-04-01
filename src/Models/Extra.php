<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\ExtraPer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Extra extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'workshop_id',
        'title',
        'description',
        'price',
        'currency',
        'capacity',
        'per',
        'is_required',
        'sort_order',
        'status',
    ];

    public function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'price' => 'decimal:2',
            'capacity' => 'integer',
            'per' => ExtraPer::class,
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class)
            ->withPivot('quantity');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(Attendee::class)
            ->withPivot('quantity');
    }
}
