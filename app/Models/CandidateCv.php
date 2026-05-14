<?php

namespace App\Models;

use App\Enums\ParseStatus;
use Database\Factories\CandidateCvFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateCv extends Model
{
    /** @use HasFactory<CandidateCvFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_posting_id',
        'candidate_name',
        'candidate_email',
        'original_file_name',
        'stored_file_path',
        'mime_type',
        'file_size',
        'raw_extracted_text',
        'cleaned_text',
        'parse_status',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'parse_status' => ParseStatus::class,
            'file_size' => 'integer',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(CandidateAnalysis::class)->latestOfMany();
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(CandidateAnalysis::class);
    }
}
