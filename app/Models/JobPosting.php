<?php

namespace App\Models;

use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'department',
        'description',
        'requirements',
        'responsibilities',
        'seniority_level',
        'location',
        'employment_type',
        'language',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function candidateCvs(): HasMany
    {
        return $this->hasMany(CandidateCv::class);
    }

    public function candidateAnalyses(): HasMany
    {
        return $this->hasMany(CandidateAnalysis::class);
    }
}
