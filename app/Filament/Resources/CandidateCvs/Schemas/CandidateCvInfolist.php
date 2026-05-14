<?php

namespace App\Filament\Resources\CandidateCvs\Schemas;

use App\Models\CandidateCv;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateCvInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('CV Bilgileri')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('jobPosting.title')->label('İş İlanı'),
                        TextEntry::make('candidate_name')->label('Aday')->placeholder('Belirtilmemiş'),
                        TextEntry::make('candidate_email')->label('E-posta')->placeholder('Belirtilmemiş'),
                        TextEntry::make('original_file_name')->label('Dosya Adı'),
                        TextEntry::make('mime_type')->label('MIME'),
                        TextEntry::make('file_size')
                            ->label('Boyut')
                            ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '-'),
                        TextEntry::make('parse_status')->label('PDF Durumu')->badge(),
                        TextEntry::make('analysis.status')->label('Analiz Durumu')->badge()->placeholder('pending'),
                        TextEntry::make('uploader.name')->label('Yükleyen')->placeholder('-'),
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
}
