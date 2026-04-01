<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\CategoryStatus;
use BlackpigCreatif\Magistere\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /** @var list<string> */
    protected array $palette = [
        '#e63946', '#2a9d8f', '#e9c46a', '#457b9d',
        '#a8dadc', '#f4a261', '#264653', '#e76f51',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->words(2, asText: true);

        return [
            'name' => ['en' => ucwords($name)],
            'slug' => Str::slug($name),
            'colour' => fake()->randomElement($this->palette),
            'sort_order' => fake()->numberBetween(0, 100),
            'status' => CategoryStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => CategoryStatus::Inactive]);
    }
}
