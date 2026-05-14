<?php

namespace App\Filament\Resources\CandidateAnalyses\Pages;

use App\Filament\Resources\CandidateAnalyses\CandidateAnalysisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCandidateAnalyses extends ListRecords
{
    protected static string $resource = CandidateAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
