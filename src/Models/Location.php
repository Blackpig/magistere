<?php

namespace BlackpigCreatif\Magistere\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'region',
        'postcode',
        'country',
        'lat',
        'lng',
        'max_capacity',
        'description',
        'website',
        'featured_image',
        'meta',
    ];

    public function casts(): array
    {
        return [
            'description' => 'array',
            'meta' => 'array',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'max_capacity' => 'integer',
        ];
    }

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->region,
            $this->postcode,
            $this->country,
        ]));
    }
}
