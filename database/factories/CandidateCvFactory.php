<?php

namespace Database\Factories;

use App\Enums\ParseStatus;
use App\Models\CandidateCv;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateCv>
 */
class CandidateCvFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(),
            'candidate_name' => $this->faker->name(),
            'candidate_email' => $this->faker->safeEmail(),
            'original_file_name' => 'candidate-cv.pdf',
            'stored_file_path' => 'candidate-cvs/candidate-cv.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 128000,
            'raw_extracted_text' => null,
            'cleaned_text' => null,
            'parse_status' => ParseStatus::Pending,
            'uploaded_by' => User::factory(),
        ];
    }
}
