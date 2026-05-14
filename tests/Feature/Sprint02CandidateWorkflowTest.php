<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Enums\ParseStatus;
use App\Models\CandidateCv;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint02CandidateWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_cv_creation_creates_pending_analysis_record(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $jobPosting = JobPosting::factory()->for($user, 'creator')->create();

        $candidateCv = CandidateCv::factory()
            ->for($jobPosting)
            ->for($user, 'uploader')
            ->create();

        $candidateCv->refresh();

        $this->assertSame(ParseStatus::Pending, $candidateCv->parse_status);
        $this->assertNotNull($candidateCv->analysis);
        $this->assertSame(AnalysisStatus::Pending, $candidateCv->analysis->status);
        $this->assertTrue($candidateCv->analysis->jobPosting->is($jobPosting));
    }

    public function test_candidate_cv_keeps_raw_and_cleaned_text_separately(): void
    {
        $candidateCv = CandidateCv::factory()->create([
            'raw_extracted_text' => "Raw\nPDF   text",
            'cleaned_text' => 'Cleaned PDF text',
        ]);

        $candidateCv->refresh();

        $this->assertSame("Raw\nPDF   text", $candidateCv->raw_extracted_text);
        $this->assertSame('Cleaned PDF text', $candidateCv->cleaned_text);
    }

    public function test_filament_sprint_02_resource_pages_render(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user)
            ->get('/admin/job-postings')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/candidate-cvs')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/candidate-analyses')
            ->assertOk();
    }
}
