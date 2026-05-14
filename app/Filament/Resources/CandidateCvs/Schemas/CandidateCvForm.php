<?php

namespace App\Filament\Resources\CandidateCvs\Schemas;

use App\Enums\ParseStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateCvForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Aday ve İlan')
                    ->columns(2)
                    ->schema([
                        Select::make('job_posting_id')
                            ->label('İş İlanı')
                            ->relationship('jobPosting', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                            ->rows(6)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Textarea::make('cleaned_text')
                            ->label('Temizlenmiş Metin')
                            ->rows(6)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
