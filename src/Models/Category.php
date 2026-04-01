<?php

namespace BlackpigCreatif\Magistere\Models;

use BlackpigCreatif\Magistere\Enums\CategoryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'colour',
        'sort_order',
        'status',
    ];

    public function casts(): array
    {
        return [
            'name' => 'array',
            'sort_order' => 'integer',
            'status' => CategoryStatus::class,
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
