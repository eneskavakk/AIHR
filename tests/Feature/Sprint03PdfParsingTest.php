<?php

namespace Tests\Feature;

use App\Actions\Candidates\ParseCandidateCvAction;
use App\Enums\ParseStatus;
use App\Models\CandidateCv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint03PdfParsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_action_stores_raw_and_cleaned_text(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('candidate-cvs/test.pdf', '%PDF-1.4 test');

        Http::fake([
            config('aihr.ai_service_url').'/parse-cv' => Http::response([
                'success' => true,
                'raw_text' => "Raw\nPDF text",
                'cleaned_text' => 'Raw PDF text',
                'page_count' => 1,
                'warnings' => [],
            ]),
        ]);

        $candidateCv = CandidateCv::factory()->create([
            'stored_file_path' => 'candidate-cvs/test.pdf',
            'original_file_name' => 'test.pdf',
            'parse_status' => ParseStatus::Pending,
            'raw_extracted_text' => null,
            'cleaned_text' => null,
        ]);

        $parsed = app(ParseCandidateCvAction::class)->handle($candidateCv);

        $candidateCv->refresh();

        $this->assertTrue($parsed);
        $this->assertSame(ParseStatus::Completed, $candidateCv->parse_status);
        $this->assertSame("Raw\nPDF text", $candidateCv->raw_extracted_text);
        $this->assertSame('Raw PDF text', $candidateCv->cleaned_text);

        Http::assertSent(fn ($request): bool => $request->url() === config('aihr.ai_service_url').'/parse-cv');
    }

    public function test_parse_action_marks_cv_as_failed_when_service_fails(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('candidate-cvs/test.pdf', '%PDF-1.4 test');

        Http::fake([
            config('aihr.ai_service_url').'/parse-cv' => Http::response([
                'detail' => 'No extractable text found.',
            ], 422),
        ]);

        $candidateCv = CandidateCv::factory()->create([
            'stored_file_path' => 'candidate-cvs/test.pdf',
            'original_file_name' => 'test.pdf',
            'parse_status' => ParseStatus::Pending,
        ]);

        $parsed = app(ParseCandidateCvAction::class)->handle($candidateCv);

        $candidateCv->refresh();

        $this->assertFalse($parsed);
        $this->assertSame(ParseStatus::Failed, $candidateCv->parse_status);
    }
}
