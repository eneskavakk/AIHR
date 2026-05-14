<?php

namespace Tests\Feature;

use App\Actions\Candidates\RetryAnalysisAction;
use App\Enums\AnalysisStatus;
use App\Enums\CandidateLevel;
use App\Models\CandidateAnalysis;
use App\Models\CandidateCv;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint07MvpStabilizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }

    // ──────────────────────────────────
    // İş İlanı Testleri
    // ──────────────────────────────────

    public function test_job_posting_can_be_created(): void
    {
        $posting = JobPosting::create([
            'title' => 'Junior PHP Developer',
            'description' => 'PHP ve Laravel ile web uygulamaları geliştirme',
            'requirements' => 'PHP, Laravel, MySQL bilgisi',
            'seniority_level' => 'Junior',
            'language' => 'tr',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('job_postings', [
            'id' => $posting->id,
            'title' => 'Junior PHP Developer',
            'is_active' => true,
        ]);
    }

    // ──────────────────────────────────
    // CV Upload Testleri
    // ──────────────────────────────────

    public function test_cv_upload_accepts_pdf(): void
    {
        Storage::fake('local');
        Queue::fake();

        $posting = JobPosting::factory()->create();
        $file = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');

        $cv = CandidateCv::create([
            'job_posting_id' => $posting->id,
            'candidate_name' => 'Test Aday',
            'stored_file_path' => 'candidate-cvs/test.pdf',
            'original_file_name' => 'cv.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 102400,
            'uploaded_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('candidate_cvs', [
            'id' => $cv->id,
            'mime_type' => 'application/pdf',
        ]);
    }

    public function test_cv_upload_size_limit_is_configured(): void
    {
        $limit = config('aihr.max_cv_upload_size_kb');
        $this->assertNotNull($limit);
        $this->assertGreaterThan(0, $limit);
        $this->assertLessThanOrEqual(10240, $limit); // Max 10MB
    }

    // ──────────────────────────────────
    // Analiz Kaydı Testleri
    // ──────────────────────────────────

    public function test_analysis_record_created_with_pending_status(): void
    {
        Queue::fake();

        $posting = JobPosting::factory()->create();
        $cv = CandidateCv::factory()->create([
            'job_posting_id' => $posting->id,
        ]);

        $analysis = CandidateAnalysis::create([
            'job_posting_id' => $posting->id,
            'candidate_cv_id' => $cv->id,
            'status' => AnalysisStatus::Pending,
        ]);

        $this->assertEquals(AnalysisStatus::Pending, $analysis->status);
    }

    // ──────────────────────────────────
    // Queue Status Transition Testleri
    // ──────────────────────────────────

    public function test_analysis_status_transitions_to_failed(): void
    {
        Queue::fake();

        $posting = JobPosting::factory()->create();
        $cv = CandidateCv::factory()->create(['job_posting_id' => $posting->id]);

        $analysis = CandidateAnalysis::create([
            'job_posting_id' => $posting->id,
            'candidate_cv_id' => $cv->id,
            'status' => AnalysisStatus::Processing,
        ]);

        $analysis->forceFill([
            'status' => AnalysisStatus::Failed,
            'error_message' => 'Test error',
            'completed_at' => now(),
        ])->save();

        $analysis->refresh();
        $this->assertEquals(AnalysisStatus::Failed, $analysis->status);
        $this->assertEquals('Test error', $analysis->error_message);
    }

    public function test_completed_analysis_has_score_and_level(): void
    {
        Queue::fake();

        $posting = JobPosting::factory()->create();
        $cv = CandidateCv::factory()->create(['job_posting_id' => $posting->id]);

        $analysis = CandidateAnalysis::create([
            'job_posting_id' => $posting->id,
            'candidate_cv_id' => $cv->id,
            'status' => AnalysisStatus::Completed,
            'score' => 75,
            'candidate_level' => CandidateLevel::StrongMatch,
            'result_json' => ['uygunluk_skoru' => 75, 'nihai_karar' => 'Güçlü aday'],
            'completed_at' => now(),
        ]);

        $analysis->refresh();
        $this->assertEquals(75, $analysis->score);
        $this->assertEquals(CandidateLevel::StrongMatch, $analysis->candidate_level);
    }

    // ──────────────────────────────────
    // Retry Testleri
    // ──────────────────────────────────

    public function test_retry_action_only_works_on_failed_analyses(): void
    {
        Queue::fake();

        $posting = JobPosting::factory()->create();
        $cv = CandidateCv::factory()->create(['job_posting_id' => $posting->id]);

        $completedAnalysis = CandidateAnalysis::create([
            'job_posting_id' => $posting->id,
            'candidate_cv_id' => $cv->id,
            'status' => AnalysisStatus::Completed,
            'score' => 80,
        ]);

        $retryAction = app(RetryAnalysisAction::class);
        $result = $retryAction->handle($completedAnalysis);

        $this->assertFalse($result, 'Completed analysis should not be retryable');
    }

    public function test_retry_action_resets_failed_analysis(): void
    {
        Queue::fake();

        $posting = JobPosting::factory()->create();
        $cv = CandidateCv::factory()->create(['job_posting_id' => $posting->id]);

        $failedAnalysis = CandidateAnalysis::create([
            'job_posting_id' => $posting->id,
            'candidate_cv_id' => $cv->id,
            'status' => AnalysisStatus::Failed,
            'error_message' => 'Previous error',
            'score' => null,
        ]);

        $retryAction = app(RetryAnalysisAction::class);
        $result = $retryAction->handle($failedAnalysis);

        $this->assertTrue($result);

        $failedAnalysis->refresh();
        $this->assertEquals(AnalysisStatus::Pending, $failedAnalysis->status);
        $this->assertNull($failedAnalysis->error_message);
    }

    // ──────────────────────────────────
    // Enum Testleri
    // ──────────────────────────────────

    public function test_analysis_status_enum_has_labels(): void
    {
        $this->assertEquals('Bekliyor', AnalysisStatus::Pending->getLabel());
        $this->assertEquals('İşleniyor', AnalysisStatus::Processing->getLabel());
        $this->assertEquals('Tamamlandı', AnalysisStatus::Completed->getLabel());
        $this->assertEquals('Başarısız', AnalysisStatus::Failed->getLabel());
    }

    public function test_candidate_level_enum_has_labels(): void
    {
        $this->assertEquals('Zayıf Eşleşme', CandidateLevel::WeakMatch->getLabel());
        $this->assertEquals('Kısmi Eşleşme', CandidateLevel::PartialMatch->getLabel());
        $this->assertEquals('Güçlü Eşleşme', CandidateLevel::StrongMatch->getLabel());
        $this->assertEquals('Mükemmel Eşleşme', CandidateLevel::ExcellentMatch->getLabel());
    }

    // ──────────────────────────────────
    // Config / Güvenlik Testleri
    // ──────────────────────────────────

    public function test_ai_service_config_exists(): void
    {
        $this->assertNotNull(config('aihr.ai_service_url'));
        $this->assertNotNull(config('aihr.ai_service_timeout'));
        $this->assertGreaterThan(0, config('aihr.ai_service_timeout'));
    }

    public function test_api_token_config_exists(): void
    {
        $this->assertArrayHasKey('ai_service_token', config('aihr'));
    }
}
