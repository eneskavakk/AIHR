<?php

namespace App\Filament\Resources\JobPostings\Tables;

use App\Enums\AnalysisStatus;
use App\Models\JobPosting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class JobPostingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Pozisyon')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('department')
                    ->label('Departman')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('seniority_level')
                    ->label('Seviye')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('location')
                    ->label('Lokasyon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('employment_type')
                    ->label('Çalışma Tipi')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('language')
                    ->label('Dil')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('candidate_cvs_count')
                    ->label('CV')
                    ->counts('candidateCvs')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('completed_analyses')
                    ->label('Analiz')
                    ->getStateUsing(fn (JobPosting $record): string => $record->candidateAnalyses()
                        ->where('status', AnalysisStatus::Completed->value)
                        ->count().'/'.$record->candidateAnalyses()->count())
                    ->badge()
                    ->color(fn (JobPosting $record): string => $record->candidateAnalyses()
                        ->where('status', AnalysisStatus::Failed->value)
                        ->exists() ? 'warning' : 'success'),
                TextColumn::make('avg_score')
                    ->label('Ort. Skor')
                    ->getStateUsing(function (JobPosting $record): string {
                        $avg = $record->candidateAnalyses()
                            ->where('status', AnalysisStatus::Completed->value)
                            ->whereNotNull('score')
                            ->avg('score');

                        return $avg !== null ? number_format((float) $avg, 0) : '-';
                    })
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
                TextColumn::make('creator.name')
                    ->label('Oluşturan')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Oluşturuldu')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktiflik'),
                SelectFilter::make('language')
                    ->label('Dil')
                    ->options([
                        'tr' => 'Türkçe',
                        'en' => 'English',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
