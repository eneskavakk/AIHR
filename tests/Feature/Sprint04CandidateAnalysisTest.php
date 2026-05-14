<?php

namespace Tests\Feature;

use App\Actions\Candidates\AnalyzeCandidateAction;
use App\Enums\AnalysisStatus;
use App\Enums\CandidateLevel;
use App\Jobs\AnalyzeCandidateJob;
use App\Models\CandidateCv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Sprint04CandidateAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_action_stores_validated_ai_result(): void
    {
        Http::fake([
            config('aihr.ai_service_url').'/analyze-candidate' => Http::response([
                'success' => true,
                'result' => [
                    'aday_adi' => 'Melek Su Kavak',
                    'pozisyon' => 'Satış Danışmanı',
                    'uygunluk_skoru' => 72,
                    'aday_seviyesi' => 'Strong Match',
                    'genel_ozet' => 'Aday satış deneyimiyle pozisyona uygundur.',
                    'olumlu_yonler' => ['Satış deneyimi'],
                    'eksik_yonler' => ['Belirtilmemiş'],
                    'eslesen_yetenekler' => ['Satış'],
                    'eksik_yetenekler' => ['Belirtilmemiş'],
                    'deneyim_analizi' => [
                        'istenen_deneyim' => 'Satış deneyimi',
                        'tespit_edilen_deneyim' => 'Sedef Giyim',
                        'sonuc' => 'Uyumlu',
                    ],
                    'egitim_analizi' => [
                        'istenen_egitim' => 'Belirtilmemiş',
                        'tespit_edilen_egitim' => 'Lise',
                        'sonuc' => 'Engel yok',
                    ],
                    'nihai_karar' => 'İK görüşmesi için değerlendirilebilir.',
                ],
                'raw_response' => '{"aday_adi":"Melek Su Kavak"}',
                'model' => 'qwen2.5:7b',
                'attempt_count' => 1,
                'prompt_version' => 'candidate-analysis-v1',
            ]),
        ]);

        $candidateCv = CandidateCv::factory()->create([
            'cleaned_text' => 'Melek Su Kavak satış danışmanı olarak çalıştı.',
        ]);
        $analysis = $candidateCv->analysis;

        $analyzed = app(AnalyzeCandidateAction::class)->handle($analysis);

        $analysis->refresh();

        $this->assertTrue($analyzed);
        $this->assertSame(AnalysisStatus::Completed, $analysis->status);
        $this->assertSame(72, $analysis->score);
        $this->assertSame(CandidateLevel::StrongMatch, $analysis->candidate_level);
        $this->assertSame('Melek Su Kavak', $analysis->result_json['aday_adi']);
        $this->assertNotNull($analysis->raw_ai_response);

        Http::assertSent(fn ($request): bool => $request->url() === config('aihr.ai_service_url').'/analyze-candidate');
    }

    public function test_analyze_action_fails_when_cleaned_text_is_missing(): void
    {
        $candidateCv = CandidateCv::factory()->create([
            'cleaned_text' => null,
        ]);
        $analysis = $candidateCv->analysis;

        $analyzed = app(AnalyzeCandidateAction::class)->handle($analysis);

        $analysis->refresh();

        $this->assertFalse($analyzed);
        $this->assertSame(AnalysisStatus::Failed, $analysis->status);
        $this->assertSame('CV temizlenmiş metni bulunamadı. Önce PDF metnini çıkarın.', $analysis->error_message);
    }

    public function test_analyze_candidate_job_runs_analysis(): void
    {
        Http::fake([
            config('aihr.ai_service_url').'/analyze-candidate' => Http::response([
                'success' => true,
                'result' => [
                    'aday_adi' => 'Melek Su Kavak',
                    'pozisyon' => 'Satış Danışmanı',
                    'uygunluk_skoru' => 85,
                    'aday_seviyesi' => 'Excellent Match',
                    'genel_ozet' => 'Aday satış rolüne güçlü şekilde uygundur.',
                    'olumlu_yonler' => ['Satış deneyimi'],
                    'eksik_yonler' => [],
                    'eslesen_yetenekler' => ['Satış'],
                    'eksik_yetenekler' => [],
                    'deneyim_analizi' => [
                        'istenen_deneyim' => 'Satış deneyimi',
                        'tespit_edilen_deneyim' => 'Satış danışmanlığı',
                        'sonuc' => 'Uyumlu',
                    ],
                    'egitim_analizi' => [
                        'istenen_egitim' => 'Belirtilmemiş',
                        'tespit_edilen_egitim' => 'Belirtilmemiş',
                        'sonuc' => 'Belirtilmemiş',
                    ],
                    'nihai_karar' => 'Görüşmeye alınabilir.',
                ],
                'raw_response' => '{"aday_adi":"Melek Su Kavak"}',
                'model' => 'qwen2.5:7b',
                'attempt_count' => 1,
                'prompt_version' => 'candidate-analysis-v1',
            ]),
        ]);

        $candidateCv = CandidateCv::factory()->create([
            'cleaned_text' => 'Satış danışmanı deneyimi vardır.',
        ]);
        $analysis = $candidateCv->analysis;

        (new AnalyzeCandidateJob($analysis->id))->handle(app(AnalyzeCandidateAction::class));

        $analysis->refresh();

        $this->assertSame(AnalysisStatus::Completed, $analysis->status);
        $this->assertSame(85, $analysis->score);
        $this->assertSame(CandidateLevel::ExcellentMatch, $analysis->candidate_level);
    }
}
