# Panduan Deployment ke Vercel

## Langkah-langkah Deployment

1. **Push kode ke repository Git**
   ```bash
   git add .
   git commit -m "Fix path issues for Vercel deployment"
   git push origin main
   ```

2. **Deploy ke Vercel**
   - Buka [Vercel Dashboard](https://vercel.com/dashboard)
   - Import project dari repository Git
   - Pilih framework: **Other**
   - Build Command: (kosongkan)
   - Output Directory: (kosongkan)

3. **Set Environment Variables**
   Di Vercel Dashboard, buka Settings > Environment Variables dan tambahkan:
   ```
   DB_HOST=gp9fn8.h.filess.io
   DB_PORT=3307
   DB_NAME=tvri_struggleno
   DB_USER=tvri_struggleno
   DB_PASS=909602de3cabb6bdcf8271209cb4a6a12e682157
   VERCEL=1
   ```

4. **Redeploy**
   Setelah menambahkan environment variables, redeploy aplikasi.

## Perubahan yang Dilakukan

1. **Perbaikan Path Relatif**: Semua `require_once` dan `include` sekarang menggunakan `__DIR__` untuk path absolut
2. **Konfigurasi Vercel**: Routing diperbaiki untuk menangani semua request
3. **Environment Variables**: Database connection sekarang mendukung environment variables
4. **File Konfigurasi**: Ditambahkan `.vercelignore` dan `php.ini`

## Troubleshooting

Jika masih ada error:
1. Periksa environment variables sudah di-set dengan benar
2. Pastikan database server dapat diakses dari Vercel
3. Periksa logs di Vercel Dashboard untuk error detail
