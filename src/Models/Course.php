<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\CourseLevel;
use BlackpigCreatif\Magistere\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'summary',
        'description',
        'level',
        'duration_days',
        'duration_hours',
        'min_capacity',
        'max_capacity',
        'base_price',
        'currency',
        'featured_image',
        'gallery',
        'meta',
        'status',
        'published_at',
    ];

    public function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'description' => 'array',
            'gallery' => 'array',
            'meta' => 'array',
            'level' => CourseLevel::class,
            'status' => CourseStatus::class,
            'base_price' => 'decimal:2',
            'min_capacity' => 'integer',
            'max_capacity' => 'integer',
            'duration_days' => 'integer',
            'duration_hours' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', CourseStatus::Active);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', CourseStatus::Active)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
