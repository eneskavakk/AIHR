# Sprint 06 - HR Dashboard, Raporlar ve Sıralama

## Amaç

Analiz sonuçlarını İK ekiplerinin hızlı karar destek alabileceği, okunabilir ve açıklanabilir bir panel deneyimine dönüştürmek.

Bu sprintte amaç şık bir vitrin değil, gerçek işe alım operasyonunda kullanılabilecek yoğun ama anlaşılır bir iş ekranı üretmektir.

## Kapsam

- İş ilanı bazlı aday listesi
- Uygunluk skoruna göre sıralama
- Aday analiz raporu
- Güçlü ve eksik yönler
- Eşleşen ve eksik yetenekler
- Deneyim ve eğitim analizi
- Analiz geçmişi
- Filtreleme

## Sorumlu Roller

- frontend-engineer
- product-manager
- backend-engineer
- reviewer

## Filament Ekranları

### İş İlanı Detay Ekranı

Gösterilecek alanlar:

- Pozisyon adı
- Departman
- Seniority
- Aktiflik durumu
- Yüklenen CV sayısı
- Tamamlanan analiz sayısı
- Ortalama skor
- En iyi eşleşen adaylar

### Aday Sıralama Ekranı

Tablo alanları:

- Aday adı
- CV dosyası
- Analiz durumu
- Uygunluk skoru
- Aday seviyesi
- Analiz tarihi
- Nihai karar özeti

Özellikler:

- Skora göre azalan sıralama
- Duruma göre filtre
- Seviyeye göre filtre
- İş ilanına göre filtre

### Aday Rapor Ekranı

Gösterilecek bölümler:

- Genel özet
- Uygunluk skoru
- Aday seviyesi
- Olumlu yönler
- Eksik yönler
- Eşleşen yetenekler
- Eksik yetenekler
- Deneyim analizi
- Eğitim analizi
- Nihai karar
- Raw JSON görüntüleme sadece teknik/debug yetkisi olan kullanıcıya

## UX İlkeleri

- Sistem son kararı vermemeli, öneri üretmelidir.
- Dil profesyonel Türkçe olmalıdır.
- Skor tek başına sunulmamalı, açıklamalarla birlikte gösterilmelidir.
- HR kullanıcısı en iyi adayları hızlıca görebilmelidir.
- Eksik bilgi varsa `Belirtilmemiş` olarak görünmelidir.

## Kabul Kriterleri

- İş ilanı bazında adaylar skorlarına göre sıralanabiliyor.
- Her tamamlanan analiz için okunabilir rapor var.
- Analiz geçmişi görüntülenebiliyor.
- Failed/pending/processing analizler rapor ekranında doğru durumla gösteriliyor.
- Kullanıcı skorun neden verildiğini anlayabiliyor.

## Dikkat Edilecek Noktalar

- Panel chatbot gibi tasarlanmamalıdır.
- "AI dediği için doğru" hissi verilmemelidir.
- Nihai karar insan kullanıcının sorumluluğunda kalmalıdır.
- Gereksiz pazarlama dili kullanılmamalıdır.

## Sprint Sonu Çıktıları

- HR dashboard
- Aday sıralama ekranı
- Aday analiz raporu
- Analiz geçmişi görünümü

