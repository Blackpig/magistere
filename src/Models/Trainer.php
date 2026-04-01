<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\TrainerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'email',
        'phone',
        'website',
        'featured_image',
        'gallery',
        'meta',
        'status',
    ];

    public function casts(): array
    {
        return [
            'bio' => 'array',
            'gallery' => 'array',
            'meta' => 'array',
            'status' => TrainerStatus::class,
        ];
    }

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class)
            ->withPivot(['role', 'sort_order'])
            ->orderByPivot('sort_order');
    }
}
