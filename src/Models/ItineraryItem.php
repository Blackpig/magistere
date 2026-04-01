<?php

namespace BlackpigCreatif\Magistere\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'day',
        'start_time',
        'end_time',
        'title',
        'description',
        'sort_order',
    ];

    public function casts(): array
    {
        return [
            'day' => 'integer',
            'sort_order' => 'integer',
            'title' => 'array',
            'description' => 'array',
        ];
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
}
