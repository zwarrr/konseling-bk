# Panduan Rasio Gambar (Semua Input Image)

Berikut tabel ringkas untuk seluruh input gambar yang digunakan form admin/user.

| Modul | Field Input | Rasio Final | Rekomendasi Ukuran (px) | Catatan |
|---|---|---|---|---|
| Beranda (Landing) | `img` | 1:1 | 1080 x 1080 | Dipakai di section beranda, crop square |
| Tentang (Landing) | `img_1`, `img_2`, `img_3`, `img_4` | 1:1 | 1080 x 1080 | Grid foto 2x2, crop square |
| Layanan (Landing) | `img` | 1:1 | 1080 x 1080 | Gambar tengah section layanan |
| Program (Admin Landing) | `img` | 384:241 | 1152 x 723 | Thumbnail program |
| Program (Admin Landing) | `img_detail_1`, `img_detail_2` | 16:9 | 1600 x 900 | Slide detail program |
| Program (User BK Form) | `img` | 384:241 | 1152 x 723 | Konsisten dengan form admin |
| Program (User BK Form) | `img_detail_1`, `img_detail_2` | 16:9 | 1600 x 900 | Konsisten dengan form admin |
| Berita BK | `img_card` | 2560:1130 | 2560 x 1130 | Cover utama/slider |
| Berita BK | `img_cards` | 384:214 | 1536 x 856 | Thumbnail card/listing |
| Berita BK | `img_detail_1`, `img_detail_2` | 16:9 | 1600 x 900 | Gambar detail berita |
| Profil BK (Section) | `img` | 4:3 | 1200 x 900 | Ilustrasi utama profil BK |
| Profil BK (Galeri) | `img` | 4:3 | 1200 x 900 | Item galeri profil BK |
| Tim BK | `img` | 3:4 | 900 x 1200 | Foto anggota tim (portrait) |

## Catatan Umum

- Batas ukuran upload saat validasi backend: maksimal 5 MB per gambar.
- Format yang aman: JPG, PNG, WEBP.
- Jika ingin lebih tajam di layar besar, boleh upload 2x ukuran rekomendasi selama rasio tetap sama.
