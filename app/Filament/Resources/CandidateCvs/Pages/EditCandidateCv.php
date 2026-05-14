<?php

namespace App\Filament\Resources\CandidateCvs\Pages;

use App\Filament\Resources\CandidateCvs\CandidateCvResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCandidateCv extends EditRecord
{
    protected static string $resource = CandidateCvResource::class;

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
