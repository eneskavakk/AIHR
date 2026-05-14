<?php

namespace App\Filament\Resources\CandidateAnalyses\Tables;

use App\Actions\Candidates\RetryAnalysisAction;
use App\Enums\AnalysisStatus;
use App\Enums\CandidateLevel;
use App\Jobs\ProcessCandidateAnalysisJob;
use App\Models\CandidateAnalysis;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CandidateAnalysesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('score', 'desc')
            ->columns([
                TextColumn::make('jobPosting.title')
                    ->label('İş İlanı')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('candidateCv.candidate_name')
                    ->label('Aday')
                    ->searchable()
                    ->placeholder('Belirtilmemiş'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->searchable(),
                TextColumn::make('score')
                    ->label('Skor')
                    ->numeric()
                    ->sortable()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 60 => 'info',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold')
                    ->placeholder('-'),
                TextColumn::make('candidate_level')
                    ->label('Seviye')
                    ->badge()
                    ->searchable(),
                TextColumn::make('result_json')
                    ->label('Nihai Karar Özeti')
                    ->getStateUsing(function (CandidateAnalysis $record): ?string {
                        $karar = $record->result_json['nihai_karar'] ?? null;
                        if ($karar === null) {
                            return null;
                        }

                        return mb_strlen($karar) > 80 ? mb_substr($karar, 0, 80).'…' : $karar;
                    })
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->label('Analiz Tarihi')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
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
                SelectFilter::make('job_posting_id')
                    ->label('İş İlanı')
                    ->relationship('jobPosting', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(AnalysisStatus::class),
                SelectFilter::make('candidate_level')
                    ->label('Aday Seviyesi')
                    ->options(CandidateLevel::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('retryAnalysis')
                    ->label('Yeniden Dene')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Analizi Yeniden Dene')
                    ->modalDescription('Başarısız analiz sıfırlanacak ve tekrar kuyruğa alınacak. Devam etmek istiyor musunuz?')
                    ->visible(fn (CandidateAnalysis $record): bool => $record->status === AnalysisStatus::Failed)
                    ->action(function (CandidateAnalysis $record): void {
                        $retried = app(RetryAnalysisAction::class)->handle($record);

                        Notification::make()
                            ->title($retried ? 'Analiz yeniden kuyruğa alındı.' : 'Yeniden deneme başarısız.')
                            ->body($retried ? 'Analiz arka planda tekrar çalışacak.' : 'Sadece başarısız analizler yeniden denenebilir.')
                            ->{$retried ? 'success' : 'danger'}()
                            ->send();
                    }),
                Action::make('reAnalyze')
                    ->label('Tekrar Analiz Et')
                    ->icon('heroicon-o-sparkles')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Tekrar Analiz Et')
                    ->modalDescription('Mevcut analiz sonuçları silinecek ve yeni bir analiz başlatılacak. Devam etmek istiyor musunuz?')
                    ->visible(fn (CandidateAnalysis $record): bool => $record->status === AnalysisStatus::Completed)
                    ->action(function (CandidateAnalysis $record): void {
                        $record->forceFill([
                            'status' => AnalysisStatus::Pending,
                            'error_message' => null,
                            'score' => null,
                            'candidate_level' => null,
                            'result_json' => null,
                            'raw_ai_response' => null,
                            'started_at' => null,
                            'completed_at' => null,
                        ])->save();

                        ProcessCandidateAnalysisJob::dispatch($record->id);

                        Notification::make()
                            ->title('Tekrar analiz kuyruğa alındı.')
                            ->body('Mevcut sonuçlar sıfırlandı, yeni analiz arka planda çalışacak.')
                            ->success()
                            ->send();
                    }),
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
