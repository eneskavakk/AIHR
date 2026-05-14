<?php

namespace App\Filament\Resources\CandidateAnalyses\Schemas;

use App\Enums\AnalysisStatus;
use App\Models\CandidateAnalysis;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateAnalysisInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Status messages for non-completed analyses
                Section::make('Analiz Durumu')
                    ->visible(fn (CandidateAnalysis $record): bool => $record->status !== AnalysisStatus::Completed)
                    ->schema([
                        TextEntry::make('status_message')
                            ->label('')
                            ->html()
                            ->getStateUsing(function (CandidateAnalysis $record): string {
                                return match ($record->status) {
                                    AnalysisStatus::Pending => '<div style="padding:1rem;background:rgba(245,158,11,0.1);border-radius:0.5rem;border-left:4px solid #f59e0b;">
                                        <div style="font-weight:600;color:#f59e0b;margin-bottom:0.25rem;">⏳ Analiz Bekliyor</div>
                                        <div style="font-size:0.875rem;color:#94a3b8;">Bu analiz kuyruğa alınmış ve sırasını bekliyor.</div>
                                    </div>',
                                    AnalysisStatus::Processing => '<div style="padding:1rem;background:rgba(59,130,246,0.1);border-radius:0.5rem;border-left:4px solid #3b82f6;">
                                        <div style="font-weight:600;color:#3b82f6;margin-bottom:0.25rem;">🔄 Analiz Devam Ediyor</div>
                                        <div style="font-size:0.875rem;color:#94a3b8;">AI modeli CV\'yi değerlendiriyor. Bu işlem 10-30 saniye sürebilir.</div>
                                    </div>',
                                    AnalysisStatus::Failed => '<div style="padding:1rem;background:rgba(239,68,68,0.1);border-radius:0.5rem;border-left:4px solid #ef4444;">
                                        <div style="font-weight:600;color:#ef4444;margin-bottom:0.25rem;">❌ Analiz Başarısız</div>
                                        <div style="font-size:0.875rem;color:#94a3b8;">'.e($record->error_message ?? 'Bilinmeyen hata.').' Yeniden denemek için üstteki "Yeniden Dene" butonunu kullanabilirsiniz.</div>
                                    </div>',
                                    default => '',
                                };
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Analiz Özeti')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('jobPosting.title')->label('İş İlanı'),
                        TextEntry::make('candidateCv.candidate_name')->label('Aday')->placeholder('Belirtilmemiş'),
                        TextEntry::make('status')->label('Durum')->badge(),
                        TextEntry::make('score')
                            ->label('Uygunluk Skoru')
                            ->placeholder('-')
                            ->html()
                            ->getStateUsing(function (CandidateAnalysis $record): ?string {
                                if ($record->score === null) {
                                    return null;
                                }

                                $score = $record->score;
                                $color = match (true) {
                                    $score >= 80 => '#22c55e',
                                    $score >= 60 => '#3b82f6',
                                    $score >= 40 => '#f59e0b',
                                    default => '#ef4444',
                                };

                                $explanation = match (true) {
                                    $score >= 80 => 'Mükemmel uyum — Aday pozisyon gereksinimlerinin büyük çoğunluğunu karşılıyor.',
                                    $score >= 60 => 'Güçlü uyum — Aday pozisyona uygun ancak bazı eksikler mevcut.',
                                    $score >= 40 => 'Kısmi uyum — Aday temel gereksinimleri kısmen karşılıyor.',
                                    default => 'Zayıf uyum — Aday pozisyon gereksinimleriyle düşük örtüşme gösteriyor.',
                                };

                                return "<div>
                                    <span style='font-size:1.5rem;font-weight:700;color:$color;'>$score</span>
                                    <span style='font-size:0.875rem;color:#94a3b8;'> / 100</span>
                                    <div style='font-size:0.75rem;color:#94a3b8;margin-top:0.25rem;'>$explanation</div>
                                </div>";
                            }),
                        TextEntry::make('candidate_level')->label('Seviye')->badge()->placeholder('-'),
                        TextEntry::make('completed_at')->label('Tamamlandı')->dateTime()->placeholder('-'),
                    ]),

                // Score breakdown
                Section::make('Puan Kırılımı')
                    ->icon('heroicon-o-chart-bar')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json['puan_kirilimi'] ?? null))
                    ->schema([
                        TextEntry::make('score_breakdown_visual')
                            ->label('')
                            ->html()
                            ->getStateUsing(function (CandidateAnalysis $record): ?string {
                                $breakdown = $record->result_json['puan_kirilimi'] ?? null;
                                if (! $breakdown) {
                                    return null;
                                }

                                $categories = [
                                    'teknik_yetenekler' => ['label' => 'Teknik Yetenekler', 'icon' => '💻'],
                                    'backend_deneyimi' => ['label' => 'Backend Deneyimi', 'icon' => '⚙️'],
                                    'database_bilgisi' => ['label' => 'Database Bilgisi', 'icon' => '🗄️'],
                                    'devops_ve_queue' => ['label' => 'DevOps & Queue', 'icon' => '🐳'],
                                    'takim_calismasi' => ['label' => 'Takım Çalışması', 'icon' => '👥'],
                                    'cloud_deneyimi' => ['label' => 'Cloud Deneyimi', 'icon' => '☁️'],
                                ];

                                $rows = '';
                                foreach ($categories as $key => $meta) {
                                    $cat = $breakdown[$key] ?? null;
                                    if (! $cat) {
                                        continue;
                                    }

                                    $puan = (int) ($cat['puan'] ?? 0);
                                    $max = (int) ($cat['maksimum'] ?? 1);
                                    $yorum = e($cat['yorum'] ?? '');
                                    $pct = $max > 0 ? round(($puan / $max) * 100) : 0;

                                    $barColor = match (true) {
                                        $pct >= 80 => '#22c55e',
                                        $pct >= 60 => '#3b82f6',
                                        $pct >= 40 => '#f59e0b',
                                        default => '#ef4444',
                                    };

                                    $rows .= <<<HTML
                                    <div style="margin-bottom:1rem;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
                                            <span style="font-weight:600;font-size:0.875rem;">{$meta['icon']} {$meta['label']}</span>
                                            <span style="font-weight:700;color:{$barColor};">{$puan}/{$max}</span>
                                        </div>
                                        <div style="background:rgba(148,163,184,0.15);border-radius:0.5rem;height:8px;overflow:hidden;">
                                            <div style="background:{$barColor};height:100%;width:{$pct}%;border-radius:0.5rem;transition:width 0.6s ease;"></div>
                                        </div>
                                        <div style="font-size:0.75rem;color:#94a3b8;margin-top:0.25rem;">{$yorum}</div>
                                    </div>
                                    HTML;
                                }

                                return "<div>{$rows}</div>";
                            })
                            ->columnSpanFull(),
                    ]),

                // General assessment
                Section::make('Genel Değerlendirme')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json))
                    ->schema([
                        TextEntry::make('result_json.genel_ozet')
                            ->label('Genel Özet')
                            ->columnSpanFull()
                            ->placeholder('Belirtilmemiş'),
                    ]),

                // Strengths and weaknesses
                Section::make('Güçlü ve Eksik Yönler')
                    ->icon('heroicon-o-scale')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('result_json.olumlu_yonler')
                            ->label('✅ Olumlu Yönler')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->columnSpanFull()
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.eksik_yonler')
                            ->label('⚠️ Eksik Yönler')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->columnSpanFull()
                            ->placeholder('Eksik yön tespit edilmemiştir.'),
                    ]),

                // Development suggestions
                Section::make('Gelişim Önerileri')
                    ->icon('heroicon-o-light-bulb')
                    ->description('İş gereksinimlerinden bağımsız, adayın kariyer gelişimine yönelik öneriler.')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json['gelisim_onerileri'] ?? null))
                    ->schema([
                        TextEntry::make('result_json.gelisim_onerileri')
                            ->label('')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->columnSpanFull(),
                    ]),

                // Skill matching
                Section::make('Yetenek Eşleşmesi')
                    ->icon('heroicon-o-puzzle-piece')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('result_json.eslesen_yetenekler')
                            ->label('Eşleşen Yetenekler')
                            ->badge()
                            ->color('success')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.eksik_yetenekler')
                            ->label('Eksik Yetenekler')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Belirtilmemiş'),
                    ]),

                // Required / Preferred skill analysis
                Section::make('Required & Preferred Skill Analizi')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json['required_skill_analizi'] ?? null) || ! empty($record->result_json['preferred_skill_analizi'] ?? null))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('result_json.required_skill_analizi.karsilananlar')
                            ->label('✅ Karşılanan Required Skills')
                            ->badge()
                            ->color('success')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.required_skill_analizi.eksik_olanlar')
                            ->label('🚫 Eksik Required Skills')
                            ->badge()
                            ->color('danger')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.preferred_skill_analizi.karsilananlar')
                            ->label('✅ Karşılanan Preferred Skills')
                            ->badge()
                            ->color('info')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.preferred_skill_analizi.eksik_olanlar')
                            ->label('💡 Eksik Preferred Skills')
                            ->badge()
                            ->color('warning')
                            ->placeholder('Belirtilmemiş'),
                    ]),

                // Experience analysis
                Section::make('Deneyim Analizi')
                    ->icon('heroicon-o-briefcase')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json['deneyim_analizi'] ?? null))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('result_json.deneyim_analizi.istenen_deneyim')
                            ->label('İstenen Deneyim')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.deneyim_analizi.tespit_edilen_deneyim')
                            ->label('Tespit Edilen Deneyim')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.deneyim_analizi.sonuc')
                            ->label('Deneyim Değerlendirmesi')
                            ->columnSpanFull()
                            ->placeholder('Belirtilmemiş'),
                    ]),

                // Education analysis
                Section::make('Eğitim Analizi')
                    ->icon('heroicon-o-academic-cap')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json['egitim_analizi'] ?? null))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('result_json.egitim_analizi.istenen_egitim')
                            ->label('İstenen Eğitim')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.egitim_analizi.tespit_edilen_egitim')
                            ->label('Tespit Edilen Eğitim')
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('result_json.egitim_analizi.sonuc')
                            ->label('Eğitim Değerlendirmesi')
                            ->columnSpanFull()
                            ->placeholder('Belirtilmemiş'),
                    ]),

                // Final verdict
                Section::make('Nihai Karar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json))
                    ->schema([
                        TextEntry::make('result_json.nihai_karar')
                            ->label('AI Nihai Değerlendirmesi')
                            ->columnSpanFull()
                            ->placeholder('Belirtilmemiş'),
                        TextEntry::make('hr_disclaimer')
                            ->label('')
                            ->html()
                            ->getStateUsing(fn (): string => '<div style="padding:0.75rem;background:rgba(59,130,246,0.08);border-radius:0.375rem;font-size:0.813rem;color:#94a3b8;border-left:3px solid #3b82f6;">
                                ℹ️ <strong>Not:</strong> Bu değerlendirme AI tarafından üretilmiş bir öneridir. Nihai işe alım kararı İK ekibinin sorumluluğundadır.
                            </div>')
                            ->columnSpanFull(),
                    ]),

                // Interview questions
                Section::make('🎯 Önerilen Mülakat Soruları')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->description('Analiz sonucuna göre AI tarafından üretilen, adaya yöneltilmesi önerilen sorular.')
                    ->visible(fn (CandidateAnalysis $record): bool => ! empty($record->result_json['mulakat_sorulari'] ?? null))
                    ->schema([
                        TextEntry::make('interview_questions_visual')
                            ->label('')
                            ->html()
                            ->getStateUsing(function (CandidateAnalysis $record): ?string {
                                $questions = $record->result_json['mulakat_sorulari'] ?? [];
                                if (empty($questions)) {
                                    return null;
                                }

                                $categoryLabels = [
                                    'belirsizlik' => ['label' => 'Belirsizlik', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.12)'],
                                    'eksik_yetenek' => ['label' => 'Eksik Yetenek', 'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)'],
                                    'derinlik' => ['label' => 'Derinlik', 'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.12)'],
                                    'kultur_uyumu' => ['label' => 'Kültür Uyumu', 'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.12)'],
                                    'motivasyon' => ['label' => 'Motivasyon', 'color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.12)'],
                                ];

                                $priorityLabels = [
                                    'yüksek' => ['label' => '🔴 Yüksek', 'color' => '#ef4444'],
                                    'orta' => ['label' => '🟡 Orta', 'color' => '#f59e0b'],
                                    'düşük' => ['label' => '🟢 Düşük', 'color' => '#22c55e'],
                                ];

                                $html = '';
                                foreach ($questions as $i => $q) {
                                    $num = $i + 1;
                                    $soru = e($q['soru'] ?? '');
                                    $neden = e($q['neden'] ?? '');
                                    $kategori = $q['kategori'] ?? 'belirsizlik';
                                    $oncelik = $q['oncelik'] ?? 'orta';

                                    $cat = $categoryLabels[$kategori] ?? $categoryLabels['belirsizlik'];
                                    $pri = $priorityLabels[$oncelik] ?? $priorityLabels['orta'];

                                    $html .= <<<HTML
                                    <div style="background:rgba(30,41,59,0.5);border:1px solid rgba(148,163,184,0.1);border-radius:0.75rem;padding:1rem;margin-bottom:0.75rem;transition:all 0.2s ease;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <span style="background:rgba(139,92,246,0.15);color:#a78bfa;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.813rem;">{$num}</span>
                                                <span style="background:{$cat['bg']};color:{$cat['color']};padding:0.15rem 0.5rem;border-radius:0.375rem;font-size:0.75rem;font-weight:600;">{$cat['label']}</span>
                                            </div>
                                            <span style="color:{$pri['color']};font-size:0.75rem;font-weight:600;">{$pri['label']}</span>
                                        </div>
                                        <div style="font-size:0.938rem;color:#e2e8f0;font-weight:500;margin-bottom:0.375rem;line-height:1.5;">"{$soru}"</div>
                                        <div style="font-size:0.75rem;color:#64748b;line-height:1.4;">💡 {$neden}</div>
                                    </div>
                                    HTML;
                                }

                                return "<div>{$html}</div>";
                            })
                            ->columnSpanFull(),
                    ]),

                // Technical details (collapsed)
                Section::make('Teknik Detaylar')
                    ->schema([
                        TextEntry::make('result_json')
                            ->label('Doğrulanmış JSON')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->html()
                            ->getStateUsing(function (CandidateAnalysis $record): ?string {
                                if (empty($record->result_json)) {
                                    return null;
                                }
                                $json = json_encode($record->result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                                return '<pre style="background:#1e293b;color:#e2e8f0;padding:1rem;border-radius:0.5rem;overflow-x:auto;font-size:0.813rem;line-height:1.6;max-height:500px;overflow-y:auto;">'
                                    .e($json)
                                    .'</pre>';
                            }),
                        TextEntry::make('raw_ai_response')->label('Ham AI Yanıtı')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('error_message')->label('Hata Mesajı')->placeholder('-')->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
