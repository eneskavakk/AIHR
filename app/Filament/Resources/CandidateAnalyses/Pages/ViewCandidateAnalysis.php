<?php

namespace App\Filament\Resources\CandidateAnalyses\Pages;

use App\Actions\Candidates\RetryAnalysisAction;
use App\Enums\AnalysisStatus;
use App\Filament\Resources\CandidateAnalyses\CandidateAnalysisResource;
use App\Jobs\ProcessCandidateAnalysisJob;
use App\Models\CandidateAnalysis;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCandidateAnalysis extends ViewRecord
{
    protected static string $resource = CandidateAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryAnalysis')
                ->label('Yeniden Dene')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Analizi Yeniden Dene')
                ->modalDescription('Başarısız analiz sıfırlanacak ve tekrar kuyruğa alınacak.')
                ->visible(fn (): bool => $this->record->status === AnalysisStatus::Failed)
                ->action(function (): void {
                    /** @var CandidateAnalysis $record */
                    $record = $this->record;
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
                ->modalDescription('Mevcut analiz sonuçları silinecek ve yeni bir analiz başlatılacak.')
                ->visible(fn (): bool => $this->record->status === AnalysisStatus::Completed)
                ->action(function (): void {
                    /** @var CandidateAnalysis $record */
                    $record = $this->record;

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
            EditAction::make(),
        ];
    }
}
