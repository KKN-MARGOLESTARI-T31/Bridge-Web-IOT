# Upload Missing Files ke Azure VM

## Masalah
File `config_psql.php` dan `.env` tidak ada di server Azure, menyebabkan error:
```
Failed to open stream: No such file or directory in /var/www/html/input.php
```

## File yang Harus Di-Upload

1. **config_psql.php** - Database configuration dan helper functions
2. **.env** - Environment variables dengan DATABASE_URL

## Cara Upload

### Opsi 1: Via SCP (Secure Copy)

```bash
# Upload config_psql.php
scp config_psql.php your-username@20.2.138.40:/var/www/html/

# Upload .env
scp .env your-username@20.2.138.40:/var/www/html/
```

### Opsi 2: Via SSH + Manual Copy

```bash
# 1. SSH ke server
ssh your-username@20.2.138.40

# 2. Masuk ke directory web root
cd /var/www/html/

# 3. Buat file config_psql.php
nano config_psql.php
# (paste content dari file lokal Anda, lalu Ctrl+X, Y, Enter)

# 4. Buat file .env
nano .env
# (paste content dari file lokal Anda, lalu Ctrl+X, Y, Enter)

# 5. Set permissions yang benar
chmod 644 config_psql.php
chmod 644 .env
chown www-data:www-data config_psql.php .env
```

### Opsi 3: Via FTP/SFTP Client

Gunakan software seperti FileZilla atau WinSCP:
1. Connect ke `20.2.138.40` via SFTP
2. Navigate ke `/var/www/html/`
3. Upload `config_psql.php` dan `.env`

### Opsi 4: Via Azure Portal Console

1. Buka Azure Portal
2. Masuk ke VM Anda
3. Klik "Serial console" atau "Connect"
4. Ikuti langkah Opsi 2 di atas

## Verifikasi

Setelah upload, cek apakah file sudah ada:

```bash
ssh your-username@20.2.138.40
ls -la /var/www/html/config_psql.php
ls -la /var/www/html/.env
```

Atau test langsung dari ESP32 device.

## PENTING: Keamanan File .env

File `.env` berisi credential database yang sensitif. Pastikan:

1. **Jangan expose via web**:
   ```bash
   # Tambahkan ke .htaccess di /var/www/html/
   <Files ".env">
       Order allow,deny
       Deny from all
   </Files>
   ```

2. **Set permission yang ketat**:
   ```bash
   chmod 600 .env  # hanya owner yang bisa read/write
   ```

3. **Pastikan .env tidak ter-commit ke Git** (sudah ada di .gitignore)

## Next Steps

Setelah kedua file ter-upload dengan benar:
1. Test lagi dari ESP32
2. Response seharusnya "OK" 
3. Data akan masuk ke database
