# Struktur Aplikasi TVRI Case Reporting System

## 📁 Struktur Folder Lengkap

```
tvri-case-reporting/
├── index.php                     # Entry point utama aplikasi
├── .htaccess                     # Konfigurasi Apache server
├── .gitignore                    # File yang diabaikan Git
├── README.md                     # Dokumentasi utama
├── STRUCTURE.md                  # Dokumentasi struktur ini
│
├── config/                       # Konfigurasi aplikasi
│   ├── database.php             # Sistem database file TXT
│   └── auth.php                 # Fungsi autentikasi
│
├── views/                        # Template dan halaman
│   ├── layouts/                 # Layout template
│   │   ├── header.php          # Header dengan logo dan user info
│   │   └── footer.php          # Footer dengan bottom navigation
│   │
│   ├── auth/                    # Halaman autentikasi
│   │   └── login.php           # Form login
│   │
│   ├── dashboard/               # Dashboard utama
│   │   └── index.php           # Statistik dan grafik
│   │
│   ├── cases/                   # Manajemen kasus
│   │   ├── index.php           # Daftar kasus dengan filter
│   │   ├── create.php          # Form tambah kasus
│   │   ├── edit.php            # Form edit kasus
│   │   └── view.php            # Detail kasus
│   │
│   ├── profile/                 # Profil user
│   │   └── index.php           # Info profil dan ubah password
│   │
│   └── error.php                # Halaman error custom
│
├── controllers/                  # Logic aplikasi
│   ├── export.php              # Export data ke CSV
│   ├── logout.php              # Proses logout
│   └── cases/
│       └── delete.php          # Hapus kasus
│
├── assets/                       # Asset statis
│   ├── css/
│   │   └── style.css           # Stylesheet utama
│   └── js/
│       └── app.js              # JavaScript interaksi
│
└── database/                     # Data aplikasi
    └── data.txt                # File data JSON (auto-generated)
```

## 📄 Deskripsi File Utama

### **Entry Point**
- **`index.php`**: Router utama aplikasi, menangani routing berdasarkan parameter `page`

### **Konfigurasi**
- **`config/database.php`**: Sistem database menggunakan file TXT dengan format JSON
- **`config/auth.php`**: Fungsi autentikasi, session management, dan authorization

### **Views (Template)**
- **`views/layouts/header.php`**: Header template dengan logo TVRI dan info user
- **`views/layouts/footer.php`**: Footer dengan bottom navigation mobile-first
- **`views/auth/login.php`**: Form login dengan validasi
- **`views/dashboard/index.php`**: Dashboard dengan statistik dan grafik Chart.js
- **`views/cases/index.php`**: Daftar kasus dengan filter dan pencarian
- **`views/cases/create.php`**: Form input kasus baru
- **`views/cases/edit.php`**: Form edit kasus
- **`views/cases/view.php`**: Detail lengkap kasus
- **`views/profile/index.php`**: Profil user dan ubah password
- **`views/error.php`**: Halaman error custom

### **Controllers (Logic)**
- **`controllers/export.php`**: Export data ke CSV dengan filter
- **`controllers/logout.php`**: Proses logout dan destroy session
- **`controllers/cases/delete.php`**: Hapus kasus dengan konfirmasi

### **Assets**
- **`assets/css/style.css`**: CSS utama dengan desain mobile-first
- **`assets/js/app.js`**: JavaScript untuk interaksi dan UX

### **Data**
- **`database/data.txt`**: File data JSON yang berisi users, categories, cases, dan settings

### **Server Configuration**
- **`.htaccess`**: Konfigurasi Apache untuk security dan performance
- **`.gitignore`**: File yang diabaikan oleh Git

## 🔧 Teknologi yang Digunakan

### **Backend**
- **PHP Native**: Tanpa framework, menggunakan struktur MVC sederhana
- **File Storage**: Data disimpan dalam file TXT dengan format JSON
- **Session Management**: PHP session untuk autentikasi

### **Frontend**
- **HTML5**: Semantic markup
- **CSS3**: Flexbox, Grid, custom properties
- **JavaScript**: Vanilla JS untuk interaksi
- **Chart.js**: Visualisasi data grafik
- **Font Awesome**: Icons

### **Design System**
- **Mobile-First**: Responsive design untuk semua device
- **Modern UI**: Flat design, rounded corners, subtle shadows
- **Color Scheme**: Blue primary, green secondary, light background

## 📊 Database Schema (JSON Format)

Data disimpan dalam file `database/data.txt` dengan struktur:

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

## 🎯 Fitur Utama yang Diimplementasi

### **Authentication & Security**
- Login/logout system
- Password hashing dengan bcrypt
- Session management
- Role-based access control

### **Dashboard**
- Statistik real-time (total, hari ini, bulan ini)
- Grafik Chart.js (doughnut dan bar chart)
- Daftar kasus terbaru

### **Case Management**
- CRUD operations untuk kasus
- Filter dan pencarian
- Export ke CSV
- Detail view lengkap

### **User Profile**
- Informasi profil
- Ubah password
- Quick actions

### **Mobile-First Design**
- Responsive layout
- Bottom navigation
- Touch-friendly interface
- Modern UI/UX

## 🚀 Cara Menjalankan

1. **Setup environment**
   ```bash
   php -S localhost:8000
   ```

2. **Akses aplikasi**
   ```
   http://localhost:8000
   ```

3. **Login default**
   - Username: `admin`
   - Password: `admin123`

## 📱 Mobile-First Features

### **Responsive Design**
- Breakpoint mobile: < 768px
- Breakpoint tablet: 768px - 1024px
- Breakpoint desktop: > 1024px

### **Bottom Navigation**
- Home (Dashboard)
- Laporan (Cases)
- Tambah (Create)
- Profil (Profile)

### **Touch-Friendly**
- Button size minimal 44px
- Adequate spacing
- Smooth scrolling
- Gesture support

## 🔒 Security Features

### **Authentication**
- Session-based login
- Password hashing
- Role-based access

### **Data Protection**
- Input validation
- XSS prevention
- File access protection
- Security headers

### **Server Security**
- `.htaccess` configuration
- Prevent direct file access
- Custom error pages

## 📤 Export Features

### **CSV Export**
- UTF-8 encoding dengan BOM
- Headers dalam bahasa Indonesia
- Filter support
- Complete data export

### **Filter Options**
- Search: Judul, deskripsi, lokasi
- Kategori: Semua kategori
- Status: Menunggu, Sedang Dikerjakan, Selesai, Dibatalkan
- Sort: Terbaru, Terlama, Judul A-Z, Prioritas

---

**TVRI Case Reporting System v1.0.0**  
Sistem pelaporan kasus untuk TVRI Kalimantan Tengah dengan penyimpanan data file TXT. 