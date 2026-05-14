<?php

namespace App\Filament\Resources\JobPostings\Schemas;

use App\Enums\AnalysisStatus;
use App\Models\CandidateAnalysis;
use App\Models\JobPosting;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobPostingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // HR Dashboard stats
                Section::make('Özet İstatistikler')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('candidate_cvs_count')
                            ->label('Yüklenen CV')
                            ->getStateUsing(fn (JobPosting $record): int => $record->candidateCvs()->count())
                            ->icon('heroicon-o-document-arrow-up')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('completed_analyses_count')
                            ->label('Tamamlanan Analiz')
                            ->getStateUsing(fn (JobPosting $record): int => $record->candidateAnalyses()
                                ->where('status', AnalysisStatus::Completed->value)
                                ->count())
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('average_score')
                            ->label('Ortalama Skor')
                            ->getStateUsing(function (JobPosting $record): string {
                                $avg = $record->candidateAnalyses()
                                    ->where('status', AnalysisStatus::Completed->value)
                                    ->whereNotNull('score')
                                    ->avg('score');

                                return $avg !== null ? number_format((float) $avg, 1) : '-';
                            })
                            ->icon('heroicon-o-chart-bar')
                            ->badge()
                            ->color(function (JobPosting $record): string {
                                $avg = $record->candidateAnalyses()
                                    ->where('status', AnalysisStatus::Completed->value)
                                    ->whereNotNull('score')
                                    ->avg('score');

                                if ($avg === null) {
                                    return 'gray';
                                }

                                return match (true) {
                                    $avg >= 80 => 'success',
                                    $avg >= 60 => 'info',
                                    $avg >= 40 => 'warning',
                                    default => 'danger',
                                };
                            }),
                        TextEntry::make('pending_analyses_count')
                            ->label('Bekleyen Analiz')
                            ->getStateUsing(fn (JobPosting $record): int => $record->candidateAnalyses()
                                ->whereIn('status', [AnalysisStatus::Pending->value, AnalysisStatus::Processing->value])
                                ->count())
                            ->icon('heroicon-o-clock')
                            ->badge()
                            ->color('warning'),
                    ]),

                // Best candidates quick view
                Section::make('En İyi Eşleşen Adaylar')
                    ->visible(fn (JobPosting $record): bool => $record->candidateAnalyses()
                        ->where('status', AnalysisStatus::Completed->value)
                        ->whereNotNull('score')
                        ->exists())
                    ->schema([
                        TextEntry::make('top_candidates')
                            ->label('')
                            ->html()
                            ->getStateUsing(function (JobPosting $record): string {
                                $topAnalyses = $record->candidateAnalyses()
                                    ->where('status', AnalysisStatus::Completed->value)
                                    ->whereNotNull('score')
                                    ->with('candidateCv')
                                    ->orderByDesc('score')
                                    ->limit(5)
                                    ->get();

                                if ($topAnalyses->isEmpty()) {
                                    return '<p style="color:#94a3b8;">Henüz tamamlanan analiz bulunmuyor.</p>';
                                }

                                $rows = $topAnalyses->map(function (CandidateAnalysis $analysis, int $index) {
                                    $rank = $index + 1;
                                    $name = e($analysis->candidateCv->candidate_name ?? 'Belirtilmemiş');
                                    $score = $analysis->score;
                                    $level = $analysis->candidate_level?->getLabel() ?? '-';

                                    $scoreColor = match (true) {
                                        $score >= 80 => '#22c55e',
                                        $score >= 60 => '#3b82f6',
                                        $score >= 40 => '#f59e0b',
                                        default => '#ef4444',
                                    };

                                    $medal = match ($rank) {
                                        1 => '🥇',
                                        2 => '🥈',
                                        3 => '🥉',
                                        default => "<span style='display:inline-block;width:1.5rem;text-align:center;font-weight:600;color:#94a3b8;'>$rank</span>",
                                    };

                                    $summary = '';
                                    if (! empty($analysis->result_json['nihai_karar'])) {
                                        $summaryText = e(mb_substr($analysis->result_json['nihai_karar'], 0, 120));
                                        if (mb_strlen($analysis->result_json['nihai_karar']) > 120) {
                                            $summaryText .= '…';
                                        }
                                        $summary = "<div style='font-size:0.75rem;color:#94a3b8;margin-top:0.25rem;'>$summaryText</div>";
                                    }

                                    return <<<HTML
                                    <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.75rem 0;border-bottom:1px solid rgba(148,163,184,0.15);">
                                        <div style="font-size:1.25rem;line-height:1;flex-shrink:0;">$medal</div>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-weight:600;">$name</div>
                                            <div style="font-size:0.813rem;color:#94a3b8;">$level</div>
                                            $summary
                                        </div>
                                        <div style="flex-shrink:0;font-size:1.125rem;font-weight:700;color:$scoreColor;">$score</div>
                                    </div>
                                    HTML;
                                })->implode('');

                                return "<div>$rows</div>";
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('İlan Bilgileri')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')->label('Pozisyon'),
                        TextEntry::make('department')->label('Departman')->placeholder('-'),
                        TextEntry::make('seniority_level')->label('Seviye')->placeholder('-'),
                        TextEntry::make('location')->label('Lokasyon')->placeholder('-'),
                        TextEntry::make('employment_type')->label('Çalışma Tipi')->placeholder('-'),
                        TextEntry::make('language')->label('Dil')->badge(),
                        IconEntry::make('is_active')->label('Aktif')->boolean(),
                        TextEntry::make('creator.name')->label('Oluşturan')->placeholder('-'),
                        TextEntry::make('created_at')->label('Oluşturuldu')->dateTime()->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Silindi')
                            ->dateTime()
                            ->visible(fn (JobPosting $record): bool => $record->trashed()),
                    ]),
                Section::make('İlan İçeriği')
                    ->schema([
                        TextEntry::make('description')->label('Açıklama')->columnSpanFull(),
                        TextEntry::make('requirements')->label('Aranan Nitelikler')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('responsibilities')->label('Sorumluluklar')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }
}
