<?php

namespace App\Filament\Resources\CandidateCvs\Pages;

use App\Filament\Resources\CandidateCvs\CandidateCvResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCandidateCv extends ViewRecord
{
    protected static string $resource = CandidateCvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
