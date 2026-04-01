<?php

namespace BlackpigCreatif\Magistere\Database\Factories;

use BlackpigCreatif\Magistere\Enums\CourseLevel;
use BlackpigCreatif\Magistere\Enums\CourseStatus;
use BlackpigCreatif\Magistere\Models\Category;
use BlackpigCreatif\Magistere\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->unique()->catchPhrase();

        return [
            'category_id' => null,
            'title' => ['en' => $title],
            'slug' => Str::slug($title),
            'summary' => ['en' => fake()->sentence(12)],
            'description' => ['en' => fake()->paragraphs(3, asText: true)],
            'level' => fake()->randomElement(CourseLevel::cases()),
            'duration_days' => fake()->optional(0.6)->numberBetween(1, 7),
            'duration_hours' => fake()->optional(0.8)->numberBetween(2, 40),
            'min_capacity' => fake()->numberBetween(2, 5),
            'max_capacity' => fake()->numberBetween(6, 20),
            'base_price' => fake()->randomFloat(2, 150, 2000),
            'currency' => 'EUR',
            'featured_image' => null,
            'gallery' => null,
            'meta' => null,
            'status' => CourseStatus::Active,
            'published_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ];
    }

    public function draft(): static
    {
        return $this->state([
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(['status' => CourseStatus::Archived]);
    }

    public function withCategory(): static
    {
        return $this->state(['category_id' => Category::factory()]);
    }
}
