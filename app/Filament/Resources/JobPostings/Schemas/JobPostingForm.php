<?php

namespace App\Filament\Resources\JobPostings\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobPostingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('İlan Bilgileri')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Pozisyon')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('department')
                            ->label('Departman')
                            ->maxLength(255),
                        Select::make('seniority_level')
                            ->label('Seviye')
                            ->options([
                                'Intern' => 'Stajyer',
                                'Junior' => 'Junior',
                                'Mid' => 'Mid',
                                'Senior' => 'Senior',
                                'Lead' => 'Lead',
                            ])
                            ->searchable(),
                        TextInput::make('location')
                            ->label('Lokasyon')
                            ->maxLength(255),
                        Select::make('employment_type')
                            ->label('Çalışma Tipi')
                            ->options([
                                'Full-time' => 'Tam Zamanlı',
                                'Part-time' => 'Yarı Zamanlı',
                                'Contract' => 'Sözleşmeli',
                                'Internship' => 'Staj',
                            ]),
                        Select::make('language')
                            ->label('İlan Dili')
                            ->options([
                                'tr' => 'Türkçe',
                                'en' => 'English',
                            ])
                            ->default('tr')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                        Hidden::make('created_by')
                            ->default(fn (): ?int => auth()->id()),
                    ]),
                Section::make('İlan İçeriği')
                    ->schema([
                        Textarea::make('description')
                            ->label('Açıklama')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('requirements')
                            ->label('Aranan Nitelikler')
                            ->rows(5)
                            ->columnSpanFull(),
                        Textarea::make('responsibilities')
                            ->label('Sorumluluklar')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
