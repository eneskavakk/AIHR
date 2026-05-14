<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CandidateLevel: string implements HasColor, HasLabel
{
    case WeakMatch = 'Weak Match';
    case PartialMatch = 'Partial Match';
    case StrongMatch = 'Strong Match';
    case ExcellentMatch = 'Excellent Match';

    public function getLabel(): string
    {
        return match ($this) {
            self::WeakMatch => 'Zayıf Eşleşme',
            self::PartialMatch => 'Kısmi Eşleşme',
            self::StrongMatch => 'Güçlü Eşleşme',
            self::ExcellentMatch => 'Mükemmel Eşleşme',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WeakMatch => 'danger',
            self::PartialMatch => 'warning',
            self::StrongMatch => 'info',
            self::ExcellentMatch => 'success',
        };
    }
}
