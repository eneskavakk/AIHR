<?php

namespace App\Filament\Resources\CandidateCvs\Pages;

use App\Filament\Resources\CandidateCvs\CandidateCvResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCandidateCvs extends ListRecords
{
    protected static string $resource = CandidateCvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
