# TVRI Case Reporting System

Sistem pelaporan kasus untuk TVRI Stasiun TVRI Kalimantan Tengah. Aplikasi web berbasis PHP native dengan penyimpanan data menggunakan file TXT (JSON format).

## 🚀 Fitur Utama

### ✅ **Dashboard**
- Statistik real-time (total kasus, hari ini, bulan ini)
- Grafik Chart.js untuk visualisasi data per kategori dan status
- Daftar kasus terbaru

### ✅ **Manajemen Kasus**
- **Daftar Kasus**: Lihat semua kasus dengan filter dan pencarian
- **Tambah Kasus**: Form input data kasus baru
- **Edit Kasus**: Modifikasi data kasus yang ada
- **Detail Kasus**: Lihat informasi lengkap kasus
- **Hapus Kasus**: Hapus kasus yang tidak diperlukan

### ✅ **Export Data**
- Export ke CSV dengan filter
- Data lengkap dengan headers yang informatif

### ✅ **Profil User**
- Informasi profil
- Ubah password
- Quick actions

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native (tanpa framework)
- **Database**: File TXT dengan format JSON
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Custom CSS dengan Flexbox/Grid
- **Icons**: Font Awesome
- **Charts**: Chart.js
- **Design**: Mobile-first, modern UI

## 📁 Struktur Folder

```
tvri-case-reporting/
├── config/
│   ├── database.php      # Konfigurasi database file TXT
│   └── auth.php          # Fungsi autentikasi
├── views/
│   ├── layouts/
│   │   ├── header.php    # Header template
│   │   └── footer.php    # Footer template
│   ├── auth/
│   │   └── login.php     # Halaman login
│   ├── dashboard/
│   │   └── index.php     # Dashboard utama
│   ├── cases/
│   │   ├── index.php     # Daftar kasus
│   │   ├── create.php    # Form tambah kasus
│   │   ├── edit.php      # Form edit kasus
│   │   └── view.php      # Detail kasus
│   └── profile/
│       └── index.php     # Halaman profil
├── controllers/
│   ├── export.php        # Export CSV
│   ├── logout.php        # Logout
│   └── cases/
│       └── delete.php    # Hapus kasus
├── assets/
│   ├── css/
│   │   └── style.css     # Custom CSS
│   └── js/
│       └── app.js        # JavaScript
├── database/
│   └── data.txt          # File data JSON (auto-generated)
├── index.php             # Entry point
├── .htaccess            # Apache configuration
├── .gitignore           # Git ignore rules
├── README.md            # Dokumentasi
└── STRUCTURE.md         # Struktur aplikasi
```

## 🎨 Desain UI/UX

### **Mobile-First Design**
- Responsive design untuk semua device
- Bottom navigation seperti aplikasi mobile
- Touch-friendly interface

### **Color Scheme**
- **Primary**: Blue (#3B82F6)
- **Secondary**: Green (#10B981)
- **Background**: Light gray (#f8fafc)
- **Text**: Dark gray (#1e293b)

### **Components**
- Rounded corners (12px border-radius)
- Subtle shadows
- Modern typography
- Clean, flat design

## 📊 Database Schema (File TXT)

Data disimpan dalam file `database/data.txt` dengan format JSON:

```json
{
  "users": [
    {
      "id": 1,
      "username": "admin",
      "password": "hashed_password",
      "full_name": "Administrator",
      "email": "admin@tvri.id",
      "role": "admin",
      "created_at": "2024-01-01 00:00:00"
    }
  ],
  "categories": [
    {
      "id": 1,
      "name": "Transmisi",
      "color": "#3B82F6",
      "created_at": "2024-01-01 00:00:00"
    }
  ],
  "cases": [
    {
      "id": 1,
      "title": "Judul Kasus",
      "description": "Deskripsi kasus",
      "location": "Lokasi",
      "category_id": 1,
      "status": "pending",
      "priority": "medium",
      "reported_by": 1,
      "assigned_to": null,
      "created_at": "2024-01-01 00:00:00",
      "updated_at": "2024-01-01 00:00:00"
    }
  ],
  "settings": [
    {
      "id": 1,
      "setting_key": "system_name",
      "setting_value": "TVRI Kalimantan Tengah",
      "description": "Nama sistem",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

## 🚀 Instalasi

### **Persyaratan**
- PHP 7.4 atau lebih tinggi
- Web server (Apache/Nginx) atau PHP built-in server
- Write permission untuk folder `database/`

### **Langkah Instalasi**

1. **Clone atau download project**
   ```bash
   git clone <repository-url>
   cd tvri-case-reporting
   ```

2. **Set permission folder database**
   ```bash
   chmod 755 database/
   ```

3. **Jalankan web server**
   ```bash
   php -S localhost:8000
   ```

4. **Akses aplikasi**
   ```
   http://localhost:8000
   ```

### **Login Default**
- **Username**: `admin`
- **Password**: `admin123`

## 📱 Fitur Mobile-First

### **Responsive Design**
- Optimized untuk mobile device
- Touch-friendly buttons
- Swipe gestures support
- Fast loading

### **Bottom Navigation**
- Home (Dashboard)
- Laporan (Cases)
- Tambah (Create)
- Profil (Profile)

### **Mobile Optimizations**
- Optimized images
- Minimal HTTP requests
- Efficient CSS/JS
- Progressive enhancement

## ⚙️ Konfigurasi

### **Database Auto-Setup**
File data akan dibuat otomatis saat pertama kali mengakses aplikasi dengan:
- User admin default
- Kategori kasus (Transmisi, Studio, Perangkat, Jaringan, Lainnya)
- Sample data untuk testing

### **Security Headers**
File `.htaccess` mengatur:
- Security headers
- Prevent direct access to sensitive files
- Custom error pages
- Gzip compression

## 📤 Export Features

### **CSV Export**
- Export semua data kasus
- Filter berdasarkan search, kategori, status
- UTF-8 encoding dengan BOM
- Headers dalam bahasa Indonesia

### **Filter Options**
- Search: Judul, deskripsi, lokasi
- Kategori: Semua kategori
- Status: Menunggu, Sedang Dikerjakan, Selesai, Dibatalkan
- Sort: Terbaru, Terlama, Judul A-Z, Prioritas

## 🔒 Security Features

### **Authentication**
- Session-based authentication
- Password hashing (bcrypt)
- Login/logout functionality

### **Data Protection**
- Input validation
- XSS prevention
- SQL injection prevention (via JSON storage)
- File access protection

### **Server Security**
- `.htaccess` security headers
- Prevent direct file access
- Custom error pages

## 🐛 Troubleshooting

### **Common Issues**

1. **File permission error**
   ```bash
   chmod 755 database/
   chmod 644 database/data.txt
   ```

2. **PHP version compatibility**
   - Ensure PHP 7.4+
   - Check error logs

3. **Data not loading**
   - Check file permissions
   - Verify JSON format in data.txt
   - Check PHP error logs

### **Debug Mode**
Enable error reporting in `index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🚀 Roadmap

### **Future Enhancements**
- [ ] PDF export functionality
- [ ] Email notifications
- [ ] File attachments
- [ ] Advanced reporting
- [ ] User management
- [ ] API endpoints
- [ ] Mobile app
- [ ] Real-time updates

### **Performance Optimizations**
- [ ] Data caching
- [ ] Image optimization
- [ ] CDN integration
- [ ] Database indexing

## 📞 Support

Untuk bantuan atau pertanyaan:
- Email: support@tvri.id
- Phone: +62-xxx-xxxx-xxxx
- Address: TVRI Stasiun Kalimantan Tengah

---

**TVRI Case Reporting System v1.0.0**  
© 2024 TVRI Kalimantan Tengah. All rights reserved. 