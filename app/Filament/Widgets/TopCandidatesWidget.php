<?php

namespace App\Filament\Widgets;

use App\Enums\AnalysisStatus;
use App\Filament\Resources\CandidateAnalyses\CandidateAnalysisResource;
use App\Models\CandidateAnalysis;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopCandidatesWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    protected static ?string $heading = '🏆 En İyi Adaylar';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CandidateAnalysis::query()
                    ->where('status', AnalysisStatus::Completed)
                    ->whereNotNull('score')
                    ->with(['jobPosting', 'candidateCv'])
                    ->orderByDesc('score')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('candidateCv.candidate_name')
                    ->label('Aday')
                    ->placeholder('Belirtilmemiş')
                    ->weight('bold'),
                TextColumn::make('jobPosting.title')
                    ->label('Pozisyon')
                    ->limit(25),
                TextColumn::make('score')
                    ->label('Skor')
                    ->numeric()
                    ->color(fn (?int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'info',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold')
                    ->size('lg'),
                TextColumn::make('candidate_level')
                    ->label('Seviye')
                    ->badge(),
                TextColumn::make('result_json')
                    ->label('Karar')
                    ->getStateUsing(function (CandidateAnalysis $record): ?string {
                        $karar = $record->result_json['nihai_karar'] ?? null;
                        if ($karar === null) {
                            return null;
                        }

                        return mb_strlen($karar) > 60 ? mb_substr($karar, 0, 60).'…' : $karar;
                    })
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn (CandidateAnalysis $record): string => CandidateAnalysisResource::getUrl('view', ['record' => $record]))
            ->paginated(false);
    }
}
