<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional(0.3)->secondaryAddress(),
            'city' => fake()->city(),
            'region' => fake()->optional(0.7)->state(),
            'postcode' => fake()->postcode(),
            'country' => fake()->randomElement(['FR', 'GB', 'DE', 'ES', 'IT', 'BE', 'NL']),
            'lat' => fake()->optional(0.6)->latitude(),
            'lng' => fake()->optional(0.6)->longitude(),
            'max_capacity' => fake()->optional(0.7)->numberBetween(10, 200),
            'description' => ['en' => fake()->optional(0.5)->paragraph()],
            'website' => fake()->optional(0.4)->url(),
            'featured_image' => null,
            'meta' => null,
        ];
    }
}
