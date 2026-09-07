CREATE DATABASE IF NOT EXISTS db_penyewaan;
USE db_penyewaan;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE barang (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    harga_sewa DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 1,
    status ENUM('Tersedia','Disewa') DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE penyewaan (
    id_sewa INT AUTO_INCREMENT PRIMARY KEY,
    kode_sewa VARCHAR(30) NOT NULL UNIQUE,
    id_pelanggan INT NOT NULL,
    id_barang INT NOT NULL,
    tanggal_sewa DATE NOT NULL,
    tanggal_rencana_kembali DATE NOT NULL,
    lama_sewa INT NOT NULL,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('Disewa','Selesai') DEFAULT 'Disewa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_sewa INT NOT NULL UNIQUE,
    tanggal_pengembalian DATE NOT NULL,
    keterlambatan INT NOT NULL DEFAULT 0,
    denda DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sewa) REFERENCES penyewaan(id_sewa) ON UPDATE CASCADE ON DELETE RESTRICT
);

INSERT INTO users (nama, username, password)
VALUES ('Administrator', 'admin', SHA2('admin123',256));

INSERT INTO barang (nama_barang,kategori,harga_sewa,stok,status) VALUES
('Kamera DSLR','Elektronik',150000,3,'Tersedia'),
('Proyektor','Elektronik',100000,2,'Tersedia'),
('Tenda Camping','Outdoor',75000,5,'Tersedia');

INSERT INTO pelanggan (nama,no_hp,alamat,email) VALUES
('Andi Saputra','081234567890','Padang','andi@email.com'),
('Siti Rahma','082345678901','Padang','siti@email.com');
