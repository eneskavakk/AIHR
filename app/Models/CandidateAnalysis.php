<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\CandidateLevel;
use Database\Factories\CandidateAnalysisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateAnalysis extends Model
{
    /** @use HasFactory<CandidateAnalysisFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_posting_id',
        'candidate_cv_id',
        'status',
        'score',
        'candidate_level',
        'result_json',
        'raw_ai_response',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AnalysisStatus::class,
            'candidate_level' => CandidateLevel::class,
            'result_json' => 'array',
            'score' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function candidateCv(): BelongsTo
    {
        return $this->belongsTo(CandidateCv::class);
    }
}
