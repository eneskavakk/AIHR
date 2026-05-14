<?php

namespace App\Filament\Resources\CandidateCvs\Tables;

use App\Actions\Candidates\ParseCandidateCvAction;
use App\Enums\ParseStatus;
use App\Jobs\ProcessCandidateAnalysisJob;
use App\Models\CandidateCv;
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

class CandidateCvsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jobPosting.title')
                    ->label('İş İlanı')
                    ->searchable(),
                TextColumn::make('candidate_name')
                    ->label('Aday')
                    ->searchable(),
                TextColumn::make('candidate_email')
                    ->label('E-posta')
                    ->searchable(),
                TextColumn::make('original_file_name')
                    ->label('Dosya')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('file_size')
                    ->label('Boyut')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '-')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('parse_status')
                    ->label('PDF Durumu')
                    ->badge()
                    ->color(fn (ParseStatus|string $state): string => match ($state instanceof ParseStatus ? $state : ParseStatus::from($state)) {
                        ParseStatus::Pending => 'warning',
                        ParseStatus::Completed => 'success',
                        ParseStatus::Failed => 'danger',
                    })
                    ->searchable(),
                TextColumn::make('analysis.status')
                    ->label('Analiz')
                    ->badge()
                    ->placeholder('pending'),
                TextColumn::make('uploader.name')
                    ->label('Yükleyen')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Yüklendi')
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
                SelectFilter::make('parse_status')
                    ->label('PDF Durumu')
                    ->options(ParseStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('parseCv')
                    ->label('PDF Metnini Çıkar')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->requiresConfirmation()
                    ->action(function (CandidateCv $record): void {
                        $parsed = app(ParseCandidateCvAction::class)->handle($record);

                        Notification::make()
                            ->title($parsed ? 'PDF metni çıkarıldı.' : 'PDF metni çıkarılamadı.')
                            ->body($parsed ? 'Ham ve temizlenmiş metin CV kaydına yazıldı.' : 'Detaylar uygulama loglarına kaydedildi.')
                            ->{$parsed ? 'success' : 'danger'}()
                            ->send();
                    }),
                Action::make('analyzeCandidate')
                    ->label('AI Analiz Yap')
                    ->icon('heroicon-o-sparkles')
                    ->requiresConfirmation()
                    ->action(function (CandidateCv $record): void {
                        $analysis = $record->analysis;

                        if ($analysis === null) {
                            Notification::make()
                                ->title('Analiz kaydı bulunamadı.')
                                ->body('CV için pending analiz kaydı oluşmamış görünüyor.')
                                ->danger()
                                ->send();

                            return;
                        }

                        ProcessCandidateAnalysisJob::dispatch($analysis->id);

                        Notification::make()
                            ->title('AI analiz kuyruğa alındı.')
                            ->body('Analiz arka planda çalışacak. Durumu birkaç saniye sonra yenileyerek kontrol edin.')
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
