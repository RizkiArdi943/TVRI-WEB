# Clear Vercel Cache

Jika file masih corrupt atau tidak ter-update, coba langkah berikut:

## 1. Force Redeploy
```bash
vercel --prod --force
```

## 2. Clear Vercel Cache via Dashboard
1. Buka Vercel Dashboard
2. Pilih project Anda
3. Go to Settings > Functions
4. Clear function cache

## 3. Manual Cache Bust
Tambahkan parameter timestamp ke URL:
```
https://your-app.vercel.app/controllers/download_surat.php?id=61&v=1732531200&t=<?php echo time(); ?>
```

## 4. Check Vercel Logs
```bash
vercel logs --follow
```

## 5. Alternative: Use Different Route
Coba akses langsung:
```
https://your-app.vercel.app/api/index.php?page=download_surat&id=61
```

## 6. Force Browser Cache Clear
- Hard refresh: Ctrl+Shift+R (Windows) atau Cmd+Shift+R (Mac)
- Clear browser cache
- Try incognito/private mode

## 7. Check File Update
Pastikan file ter-update dengan cek:
```bash
curl -I https://your-app.vercel.app/controllers/download_surat.php?id=61&v=1732531200
```

Header response harus menunjukkan:
- Cache-Control: no-cache, no-store, must-revalidate
- Pragma: no-cache
- Expires: 0
