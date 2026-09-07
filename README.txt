SISTEM INFORMASI PENYEWAAN - PHP MYSQL

CARA INSTALL:
1. Install XAMPP dan jalankan Apache + MySQL.
2. Salin folder sistem_penyewaan ke C:\xampp\htdocs\
3. Buka http://localhost/phpmyadmin
4. Import file database.sql.
5. Buka http://localhost/sistem_penyewaan/
6. Login:
   Username: admin
   Password: admin123

FITUR:
- Login/logout
- Dashboard
- CRUD barang
- CRUD pelanggan
- Transaksi penyewaan
- Pengembalian + denda otomatis Rp25.000/hari
- Laporan berdasarkan periode + cetak
- Pengaturan akun

Catatan: database menggunakan SHA-256 untuk password contoh. Untuk aplikasi produksi sebaiknya gunakan password_hash/password_verify.
