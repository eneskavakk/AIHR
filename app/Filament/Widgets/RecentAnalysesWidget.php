<?php

namespace App\Filament\Widgets;

use App\Enums\AnalysisStatus;
use App\Filament\Resources\CandidateAnalyses\CandidateAnalysisResource;
use App\Models\CandidateAnalysis;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentAnalysesWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    protected static ?string $heading = 'Son Analizler';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CandidateAnalysis::query()
                    ->with(['jobPosting', 'candidateCv'])
                    ->latest('updated_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('candidateCv.candidate_name')
                    ->label('Aday')
                    ->placeholder('Belirtilmemiş')
                    ->searchable(),
                TextColumn::make('jobPosting.title')
                    ->label('Pozisyon')
                    ->limit(25),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge(),
                TextColumn::make('score')
                    ->label('Skor')
                    ->numeric()
                    ->placeholder('-')
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 60 => 'info',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold'),
                TextColumn::make('candidate_level')
                    ->label('Seviye')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Güncellendi')
                    ->since()
                    ->sortable(),
            ])
            ->recordUrl(fn (CandidateAnalysis $record): string => CandidateAnalysisResource::getUrl('view', ['record' => $record]))
            ->paginated(false);
    }
}
