<?php

namespace Database\Factories;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['Yazilim', 'Urun', 'Satis', 'Operasyon']),
            'description' => $this->faker->paragraphs(3, true),
            'requirements' => $this->faker->paragraphs(2, true),
            'responsibilities' => $this->faker->paragraphs(2, true),
            'seniority_level' => $this->faker->randomElement(['Junior', 'Mid', 'Senior']),
            'location' => $this->faker->randomElement(['Istanbul', 'Ankara', 'Remote']),
            'employment_type' => $this->faker->randomElement(['Full-time', 'Part-time', 'Contract']),
            'language' => 'tr',
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
