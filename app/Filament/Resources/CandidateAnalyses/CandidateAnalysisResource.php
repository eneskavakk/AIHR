<?php

namespace App\Filament\Resources\CandidateAnalyses;

use App\Enums\AnalysisStatus;
use App\Filament\Resources\CandidateAnalyses\Pages\CreateCandidateAnalysis;
use App\Filament\Resources\CandidateAnalyses\Pages\EditCandidateAnalysis;
use App\Filament\Resources\CandidateAnalyses\Pages\ListCandidateAnalyses;
use App\Filament\Resources\CandidateAnalyses\Pages\ViewCandidateAnalysis;
use App\Filament\Resources\CandidateAnalyses\Schemas\CandidateAnalysisForm;
use App\Filament\Resources\CandidateAnalyses\Schemas\CandidateAnalysisInfolist;
use App\Filament\Resources\CandidateAnalyses\Tables\CandidateAnalysesTable;
use App\Models\CandidateAnalysis;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CandidateAnalysisResource extends Resource
{
    protected static ?string $model = CandidateAnalysis::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-chart-bar-square';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Analiz Kayıtları';

    protected static ?string $modelLabel = 'Analiz Kaydı';

    protected static ?string $pluralModelLabel = 'Analiz Kayıtları';

    protected static string|UnitEnum|null $navigationGroup = 'Aday Yönetimi';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::whereIn('status', [
            AnalysisStatus::Pending->value,
            AnalysisStatus::Processing->value,
        ])->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $failed = static::getModel()::where('status', AnalysisStatus::Failed->value)->count();

        return $failed > 0 ? 'danger' : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CandidateAnalysisForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CandidateAnalysisInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CandidateAnalysesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidateAnalyses::route('/'),
            'create' => CreateCandidateAnalysis::route('/create'),
            'view' => ViewCandidateAnalysis::route('/{record}'),
            'edit' => EditCandidateAnalysis::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
