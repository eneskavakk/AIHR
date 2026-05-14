<?php

namespace App\Filament\Resources\CandidateAnalyses\Schemas;

use App\Enums\AnalysisStatus;
use App\Enums\CandidateLevel;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateAnalysisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Analiz Durumu')
                    ->columns(2)
                    ->schema([
                        Select::make('job_posting_id')
                            ->label('İş İlanı')
                            ->relationship('jobPosting', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('candidate_cv_id')
                            ->label('Aday CV')
                            ->relationship('candidateCv', 'candidate_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->candidate_name ?? 'CV #'.$record->id)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Durum')
                            ->options(AnalysisStatus::class)
                            ->default(AnalysisStatus::Pending->value)
                            ->required(),
                        TextInput::make('score')
                            ->label('Uygunluk Skoru')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Select::make('candidate_level')
                            ->label('Aday Seviyesi')
                            ->options(CandidateLevel::class),
                        DateTimePicker::make('started_at')
                            ->label('Başlama Zamanı'),
                        DateTimePicker::make('completed_at')
                            ->label('Tamamlanma Zamanı'),
                    ]),
                Section::make('AI Çıktısı')
                    ->schema([
                        KeyValue::make('result_json')
                            ->label('Doğrulanmış JSON')
                            ->columnSpanFull(),
                        Textarea::make('raw_ai_response')
                            ->label('Ham AI Yanıtı')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('error_message')
                            ->label('Hata Mesajı')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
