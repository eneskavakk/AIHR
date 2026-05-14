<?php

namespace App\Filament\Resources\JobPostings\RelationManagers;

use App\Actions\Candidates\ParseCandidateCvAction;
use App\Enums\ParseStatus;
use App\Jobs\ProcessCandidateAnalysisJob;
use App\Models\CandidateCv;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateCvsRelationManager extends RelationManager
{
    protected static string $relationship = 'candidateCvs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Aday Bilgileri')
                    ->columns(2)
                    ->schema([
                        TextInput::make('candidate_name')
                            ->label('Aday Adı')
                            ->maxLength(255),
                        TextInput::make('candidate_email')
                            ->label('Aday E-posta')
                            ->email()
                            ->maxLength(255),
                    ]),
                Section::make('CV Dosyası')
                    ->schema([
                        FileUpload::make('stored_file_path')
                            ->label('PDF CV')
                            ->disk('local')
                            ->directory('candidate-cvs')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize((int) config('aihr.max_cv_upload_size_kb'))
                            ->storeFileNamesIn('original_file_name')
                            ->downloadable()
                            ->required(),
                        Hidden::make('original_file_name')
                            ->default('cv.pdf'),
                        Hidden::make('mime_type')
                            ->default('application/pdf'),
                        Hidden::make('file_size')
                            ->default(0),
                        Hidden::make('parse_status')
                            ->default(ParseStatus::Pending->value),
                        Hidden::make('uploaded_by')
                            ->default(fn (): ?int => auth()->id()),
                    ]),
                Section::make('Ayrıştırma Metinleri')
                    ->schema([
                        Textarea::make('raw_extracted_text')
                            ->label('Ham Çıkarılan Metin')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Textarea::make('cleaned_text')
                            ->label('Temizlenmiş Metin')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('CV Bilgileri')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('candidate_name')->label('Aday')->placeholder('Belirtilmemiş'),
                        TextEntry::make('candidate_email')->label('E-posta')->placeholder('Belirtilmemiş'),
                        TextEntry::make('original_file_name')->label('Dosya'),
                        TextEntry::make('file_size')
                            ->label('Boyut')
                            ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '-'),
                        TextEntry::make('parse_status')->label('PDF Durumu')->badge(),
                        TextEntry::make('analysis.status')->label('Analiz Durumu')->badge()->placeholder('pending'),
                        TextEntry::make('created_at')->label('Yüklendi')->dateTime()->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Silindi')
                            ->dateTime()
                            ->visible(fn (CandidateCv $record): bool => $record->trashed()),
                    ]),
                Section::make('Ayrıştırma Metinleri')
                    ->schema([
                        TextEntry::make('raw_extracted_text')
                            ->label('Ham Çıkarılan Metin')
                            ->placeholder('Henüz ayrıştırılmadı. PDF Metnini Çıkar aksiyonunu çalıştırın.')
                            ->columnSpanFull(),
                        TextEntry::make('cleaned_text')
                            ->label('Temizlenmiş Metin')
                            ->placeholder('Henüz ayrıştırılmadı. PDF Metnini Çıkar aksiyonunu çalıştırın.')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('candidate_name')
            ->columns([
                TextColumn::make('candidate_name')
                    ->label('Aday')
                    ->searchable(),
                TextColumn::make('candidate_email')
                    ->label('E-posta')
                    ->searchable(),
                TextColumn::make('original_file_name')
                    ->label('Dosya')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('Boyut')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '-')
                    ->sortable(),
                TextColumn::make('parse_status')
                    ->label('PDF Durumu')
                    ->badge()
                    ->searchable(),
                TextColumn::make('analysis.status')
                    ->label('Analiz')
                    ->badge()
                    ->placeholder('pending'),
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
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
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
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
