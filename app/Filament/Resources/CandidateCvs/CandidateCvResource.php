<?php

namespace App\Filament\Resources\CandidateCvs;

use App\Filament\Resources\CandidateCvs\Pages\CreateCandidateCv;
use App\Filament\Resources\CandidateCvs\Pages\EditCandidateCv;
use App\Filament\Resources\CandidateCvs\Pages\ListCandidateCvs;
use App\Filament\Resources\CandidateCvs\Pages\ViewCandidateCv;
use App\Filament\Resources\CandidateCvs\Schemas\CandidateCvForm;
use App\Filament\Resources\CandidateCvs\Schemas\CandidateCvInfolist;
use App\Filament\Resources\CandidateCvs\Tables\CandidateCvsTable;
use App\Models\CandidateCv;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CandidateCvResource extends Resource
{
    protected static ?string $model = CandidateCv::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-document-text';

    protected static ?string $recordTitleAttribute = 'candidate_name';

    protected static ?string $navigationLabel = 'CV Yüklemeleri';

    protected static ?string $modelLabel = 'Aday CV';

    protected static ?string $pluralModelLabel = 'Aday CVleri';

    protected static string|UnitEnum|null $navigationGroup = 'Aday Yönetimi';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CandidateCvForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CandidateCvInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CandidateCvsTable::configure($table);
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
            'index' => ListCandidateCvs::route('/'),
            'create' => CreateCandidateCv::route('/create'),
            'view' => ViewCandidateCv::route('/{record}'),
            'edit' => EditCandidateCv::route('/{record}/edit'),
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
