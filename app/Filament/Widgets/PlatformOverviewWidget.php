<?php

namespace App\Filament\Widgets;

use App\Enums\AnalysisStatus;
use App\Models\CandidateAnalysis;
use App\Models\CandidateCv;
use App\Models\JobPosting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalPostings = JobPosting::count();
        $activePostings = JobPosting::where('is_active', true)->count();
        $totalCvs = CandidateCv::count();
        $totalAnalyses = CandidateAnalysis::count();
        $completedAnalyses = CandidateAnalysis::where('status', AnalysisStatus::Completed)->count();
        $pendingAnalyses = CandidateAnalysis::whereIn('status', [AnalysisStatus::Pending, AnalysisStatus::Processing])->count();
        $failedAnalyses = CandidateAnalysis::where('status', AnalysisStatus::Failed)->count();

        $avgScore = CandidateAnalysis::where('status', AnalysisStatus::Completed)
            ->whereNotNull('score')
            ->avg('score');

        return [
            Stat::make('İş İlanları', $totalPostings)
                ->description($activePostings.' aktif ilan')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, $totalPostings]),

            Stat::make('Yüklenen CV', $totalCvs)
                ->description('Toplam aday başvurusu')
                ->descriptionIcon('heroicon-o-document-arrow-up')
                ->color('info')
                ->chart([2, 4, 6, 8, 5, $totalCvs]),

            Stat::make('Tamamlanan Analiz', $completedAnalyses.'/'.$totalAnalyses)
                ->description($pendingAnalyses > 0 ? $pendingAnalyses.' analiz bekliyor' : 'Tüm analizler tamamlandı')
                ->descriptionIcon($pendingAnalyses > 0 ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
                ->color($pendingAnalyses > 0 ? 'warning' : 'success')
                ->chart([1, 3, 5, 4, 6, $completedAnalyses]),

            Stat::make('Ortalama Skor', $avgScore !== null ? number_format((float) $avgScore, 1) : '-')
                ->description($failedAnalyses > 0 ? $failedAnalyses.' başarısız analiz' : 'Platform ortalaması')
                ->descriptionIcon($failedAnalyses > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-chart-bar')
                ->color(match (true) {
                    $avgScore === null => 'gray',
                    $avgScore >= 70 => 'success',
                    $avgScore >= 50 => 'info',
                    default => 'warning',
                })
                ->chart([40, 55, 60, 45, 70, (int) ($avgScore ?? 0)]),
        ];
    }
}
