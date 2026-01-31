# Setup Database Neon untuk IoT Receiver

Panduan lengkap untuk mengatur database PostgreSQL menggunakan [Neon](https://neon.tech) untuk aplikasi IoT Receiver.

## 🚀 Langkah-langkah Setup

### 1. Buat Akun Neon

1. Kunjungi [neon.tech](https://neon.tech)
2. Sign up menggunakan GitHub atau email
3. Verifikasi email jika diperlukan

### 2. Buat Database Baru

1. Setelah login, klik **"Create a project"**
2. Isi detail project:
   - **Project name**: `iot-receiver` (atau nama yang Anda inginkan)
   - **PostgreSQL version**: Pilih versi terbaru (recommended: 16)
   - **Region**: Pilih yang terdekat (Singapore untuk Indonesia - `aws-ap-southeast-1`)
3. Klik **"Create Project"**

### 3. Dapatkan Connection String

Setelah project dibuat, Anda akan melihat **Connection String**. Ada dua format:

#### Format Pooled (Recommended untuk PHP)
```
postgresql://username:password@ep-xxxxx.region.aws.neon.tech/neondb?sslmode=require
```

#### Format Direct
```
postgresql://username:password@ep-xxxxx.region.aws.neon.tech/neondb?sslmode=require
```

> **💡 Tip**: Gunakan **Pooled connection** untuk aplikasi web PHP karena lebih efisien.

### 4. Konfigurasi File `.env`

Copy file `.env.example` menjadi `.env` jika belum:

```bash
cp .env.example .env
```

Edit file `.env` dan **uncomment** baris `DATABASE_URL`, lalu paste connection string dari Neon:

```env
# Option 2: PostgreSQL (Neon/Render/Fly)
DATABASE_URL=postgresql://username:password@ep-xxxxx.region.aws.neon.tech/neondb?sslmode=require
```

> **⚠️ Penting**: 
> - Ganti `username`, `password`, dan `ep-xxxxx.region.aws.neon.tech` dengan nilai dari Neon
> - **Jangan commit** file `.env` ke Git (sudah ada di `.gitignore`)

### 5. Buat Tabel Database

Jalankan script setup untuk membuat tabel:

```bash
php setup_db.php
```

Script ini akan membuat 2 tabel:
- `ph_readings` - untuk data pH
- `water_level_readings` - untuk data level air

### 6. (Opsional) Seed Data untuk Testing

Jika ingin mengisi database dengan data sample:

```bash
php seed.php
```

### 7. Test Koneksi

Untuk memastikan koneksi berhasil:

```bash
php debug_db.php
```

atau buka di browser:
```
http://localhost/debug_db.php
```

## 🔧 Troubleshooting

### Error: "could not find driver"

Install ekstensi PostgreSQL untuk PHP:

**Ubuntu/Debian:**
```bash
sudo apt-get install php-pgsql
sudo systemctl restart apache2
```

**Windows (XAMPP/WAMP):**
1. Edit `php.ini`
2. Uncomment line: `extension=pgsql`
3. Restart Apache

### Error: "SSL connection required"

Pastikan connection string memiliki `sslmode=require`:
```
?sslmode=require
```

### Error: "password authentication failed"

1. Periksa kembali connection string dari Neon Dashboard
2. Reset password database di Neon jika perlu
3. Pastikan tidak ada spasi atau karakter tersembunyi di `.env`

## 📊 Mengelola Database via Neon Console

1. Login ke [console.neon.tech](https://console.neon.tech)
2. Pilih project Anda
3. Klik tab **"SQL Editor"**
4. Anda bisa menjalankan query SQL langsung dari sini

### Query Berguna

**Lihat semua data pH:**
```sql
SELECT * FROM ph_readings ORDER BY timestamp DESC LIMIT 10;
```

**Lihat semua data water level:**
```sql
SELECT * FROM water_level_readings ORDER BY timestamp DESC LIMIT 10;
```

**Hapus semua data (reset):**
```sql
TRUNCATE TABLE ph_readings, water_level_readings;
```

## 🔒 Keamanan

1. **Jangan share** connection string di repository publik
2. Gunakan **environment variables** untuk production
3. Aktifkan **IP allowlist** di Neon jika diperlukan (Settings > IP Allow)
4. Gunakan **separate database** untuk development dan production

## 📈 Monitoring

Neon menyediakan monitoring gratis:
- **Metrics**: CPU, Memory, Storage usage
- **Query performance**
- **Connection pooling stats**

Akses via Dashboard > Project > Monitoring

## 🆓 Free Tier Limits

Neon Free Plan:
- ✅ 0.5 GB storage
- ✅ 1 project
- ✅ 10 branches
- ✅ Autoscaling & Autosuspend
- ✅ 100 jam compute/bulan

Untuk IoT dengan data yang tidak terlalu banyak, free tier sudah cukup!

## 🔗 Resources

- [Neon Documentation](https://neon.tech/docs)
- [Neon Console](https://console.neon.tech)
- [PostgreSQL PHP Tutorial](https://www.php.net/manual/en/book.pgsql.php)

## ✅ Checklist Setup

- [ ] Buat akun Neon
- [ ] Buat project database
- [ ] Copy connection string
- [ ] Update file `.env`
- [ ] Jalankan `setup_db.php`
- [ ] Test dengan `debug_db.php`
- [ ] (Opsional) Seed data dengan `seed.php`
- [ ] Test API endpoint `/api/save-ph.php`
