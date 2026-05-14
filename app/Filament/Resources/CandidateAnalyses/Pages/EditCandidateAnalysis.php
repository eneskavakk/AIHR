<?php

namespace App\Filament\Resources\CandidateAnalyses\Pages;

use App\Filament\Resources\CandidateAnalyses\CandidateAnalysisResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCandidateAnalysis extends EditRecord
{
    protected static string $resource = CandidateAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
