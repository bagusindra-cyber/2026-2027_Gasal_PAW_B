-- =====================================================================
-- DATABASE: db_penjualan
-- Tugas Praktikum - Sistem Penjualan (Master-Detail Transaksi)
-- =====================================================================
-- Dibuat sesuai diagram ERD yang diberikan:
--   supplier (1) ---- (N) barang
--   barang   (1) ---- (N) transaksi_detail
--   transaksi(1) ---- (N) transaksi_detail
--   transaksi(1) ---- (N) pembayaran
--   pelanggan(1) ---- (N) transaksi
--   user     : tabel mandiri (tidak punya relasi ke tabel lain di diagram)
--
-- CATATAN PENTING / ASUMSI:
-- 1. Pada tabel `transaksi_detail`, diagram TIDAK menampilkan kolom id
--    tersendiri (hanya transaksi_id, barang_id, harga, qty). Karena itu
--    tabel ini dibuat dengan PRIMARY KEY GABUNGAN (transaksi_id, barang_id).
--    Efeknya: secara struktur database, satu barang memang tidak akan
--    pernah bisa ganda pada transaksi yang sama (duplicate key akan
--    ditolak MySQL) - ini melengkapi validasi di level aplikasi (poin 2).
-- 2. FK barang_id pada transaksi_detail diberi ON DELETE RESTRICT, artinya
--    MySQL sendiri akan menolak penghapusan barang yang masih dipakai di
--    transaksi_detail. Ini jadi lapisan pengaman kedua selain pengecekan
--    manual di hapus_barang.php (poin 3).
-- 3. Kolom `harga` di transaksi_detail sengaja terpisah dari `harga` di
--    barang, karena harga barang bisa berubah sewaktu-waktu. Nilai yang
--    disimpan di transaksi_detail adalah harga barang PADA SAAT transaksi
--    terjadi (price snapshot), bukan referensi ke harga barang saat ini.
-- 4. Nama tabel `user` diberi backtick karena USER adalah kata yang juga
--    dipakai MySQL sebagai fungsi bawaan (USER()).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_penjualan;
USE db_penjualan;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS transaksi_detail;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS `user`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Tabel master: supplier
-- ---------------------------------------------------------------------
CREATE TABLE supplier (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nama    VARCHAR(100) NOT NULL,
    telp    VARCHAR(12),
    alamat  TEXT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel master: barang
-- ---------------------------------------------------------------------
CREATE TABLE barang (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang  VARCHAR(10) NOT NULL,
    nama_barang  VARCHAR(100) NOT NULL,
    harga        INT NOT NULL,
    stok         INT NOT NULL DEFAULT 0,
    supplier_id  INT,
    UNIQUE KEY uq_kode_barang (kode_barang),
    CONSTRAINT fk_barang_supplier
        FOREIGN KEY (supplier_id) REFERENCES supplier(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel master: pelanggan  (id berupa kode, VARCHAR sesuai diagram)
-- ---------------------------------------------------------------------
CREATE TABLE pelanggan (
    id              VARCHAR(20) PRIMARY KEY,
    nama            VARCHAR(100) NOT NULL,
    jenis_kelamin   ENUM('L','P') NOT NULL,
    telp            VARCHAR(12),
    alamat          TEXT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel master: user (akun login)
-- ---------------------------------------------------------------------
CREATE TABLE `user` (
    id_user     TINYINT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(30) NOT NULL,
    password    VARCHAR(35) NOT NULL,
    nama        VARCHAR(50) NOT NULL,
    alamat      VARCHAR(150),
    hp          VARCHAR(20),
    level       TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel transaksi (master)
-- ---------------------------------------------------------------------
CREATE TABLE transaksi (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    waktu_transaksi   DATE NOT NULL,
    keterangan        TEXT,
    total             INT NOT NULL DEFAULT 0,
    pelanggan_id      VARCHAR(20),
    CONSTRAINT fk_transaksi_pelanggan
        FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel transaksi_detail (detail) - PK gabungan, lihat catatan di atas
-- ---------------------------------------------------------------------
CREATE TABLE transaksi_detail (
    transaksi_id    INT NOT NULL,
    barang_id       INT NOT NULL,
    harga           INT NOT NULL,
    qty             INT NOT NULL,
    PRIMARY KEY (transaksi_id, barang_id),
    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (transaksi_id) REFERENCES transaksi(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_barang
        FOREIGN KEY (barang_id) REFERENCES barang(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabel pembayaran
-- ---------------------------------------------------------------------
CREATE TABLE pembayaran (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    waktu_bayar     DATETIME NOT NULL,
    total           INT NOT NULL,
    metode          ENUM('TUNAI','TRANSFER','EDC') NOT NULL,
    transaksi_id    INT NOT NULL,
    CONSTRAINT fk_pembayaran_transaksi
        FOREIGN KEY (transaksi_id) REFERENCES transaksi(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =====================================================================
-- DATA MASTER (masing-masing >= 5 baris)
-- =====================================================================

INSERT INTO supplier (id, nama, telp, alamat) VALUES
(1, 'CV Sumber Makmur',    '031555123',  'Jl. Raya Sidoarjo No. 1'),
(2, 'PT Elektronik Jaya',  '031559876',  'Jl. Ahmad Yani No. 10'),
(3, 'Toko Grosir Madura',  '081234567890','Jl. Trunojoyo No. 5'),
(4, 'CV Mitra Sejahtera',  '081298765432','Jl. Diponegoro No. 8'),
(5, 'PT Sumber Rezeki',    '031777889',  'Jl. Veteran No. 12');

INSERT INTO barang (id, kode_barang, nama_barang, harga, stok, supplier_id) VALUES
(1, 'BRG001', 'Pulpen Standard',       3000,   200, 1),
(2, 'BRG002', 'Buku Tulis 38 Lembar',  5000,   150, 1),
(3, 'BRG003', 'Mouse Wireless',        75000,  50,  2),
(4, 'BRG004', 'Keyboard USB',          120000, 40,  2),
(5, 'BRG005', 'Flashdisk 32GB',        60000,  60,  3),
(6, 'BRG006', 'Kertas A4 1 Rim',       45000,  80,  3),
(7, 'BRG007', 'Tinta Printer Hitam',   35000,  70,  4),
(8, 'BRG008', 'Stapler Besar',         25000,  45,  5);

INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES
('PLG001', 'Budi Santoso',   'L', '081211112222', 'Jl. Melati No. 3'),
('PLG002', 'Siti Aminah',    'P', '081333334444', 'Jl. Mawar No. 7'),
('PLG003', 'Andi Wijaya',    'L', '081555556666', 'Jl. Kenanga No. 2'),
('PLG004', 'Rina Kartika',   'P', '081777778888', 'Jl. Anggrek No. 9'),
('PLG005', 'Dedi Kurniawan', 'L', '081999990000', 'Jl. Flamboyan No. 4');

-- password disimpan dalam bentuk MD5 (contoh praktikum). Password asli = "rahasia123" untuk semua akun contoh.
INSERT INTO `user` (id_user, username, password, nama, alamat, hp, level) VALUES
(1, 'admin',  MD5('rahasia123'), 'Administrator',  'Jl. Kantor No. 1',    '081200001111', 1),
(2, 'kasir1', MD5('rahasia123'), 'Kasir Satu',      'Jl. Kasir No. 2',     '081200002222', 2),
(3, 'kasir2', MD5('rahasia123'), 'Kasir Dua',       'Jl. Kasir No. 3',     '081200003333', 2),
(4, 'owner',  MD5('rahasia123'), 'Pemilik Toko',    'Jl. Pemilik No. 4',   '081200004444', 0),
(5, 'gudang', MD5('rahasia123'), 'Staff Gudang',    'Jl. Gudang No. 5',    '081200005555', 3);

-- =====================================================================
-- DATA TRANSAKSI (>= 10 baris transaksi, lengkap dengan detail & pembayaran)
-- Kolom `total` di sini sudah dihitung manual agar konsisten dengan
-- transaksi_detail-nya masing-masing (mensimulasikan hasil akhir setelah
-- fitur auto-update total pada poin 4 berjalan).
-- =====================================================================

INSERT INTO transaksi (id, waktu_transaksi, keterangan, total, pelanggan_id) VALUES
(1,  '2026-11-01', 'Pembelian alat tulis',               55000,  'PLG001'),
(2,  '2026-11-01', 'Pembelian perlengkapan komputer',     195000, 'PLG002'),
(3,  '2026-11-02', 'Pembelian ATK kantor',                205000, 'PLG003'),
(4,  '2026-11-02', 'Pembelian flashdisk dan stapler',     145000, 'PLG004'),
(5,  '2026-11-03', 'Pembelian pulpen dan tinta',          95000,  'PLG005'),
(6,  '2026-11-03', 'Pembelian buku dan kertas',           95000,  'PLG001'),
(7,  '2026-11-04', 'Pembelian mouse dan flashdisk',       210000, 'PLG002'),
(8,  '2026-11-04', 'Pembelian keyboard',                  120000, 'PLG003'),
(9,  '2026-11-05', 'Pembelian lengkap ATK',               135000, 'PLG004'),
(10, '2026-11-05', 'Pembelian tinta dan kertas',          195000, 'PLG005');

INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES
-- Transaksi 1 (total 55.000)
(1, 1, 3000,   10),
(1, 2, 5000,   5),
-- Transaksi 2 (total 195.000)
(2, 3, 75000,  1),
(2, 4, 120000, 1),
-- Transaksi 3 (total 205.000)
(3, 6, 45000,  3),
(3, 7, 35000,  2),
-- Transaksi 4 (total 145.000)
(4, 5, 60000,  2),
(4, 8, 25000,  1),
-- Transaksi 5 (total 95.000)
(5, 1, 3000,   20),
(5, 7, 35000,  1),
-- Transaksi 6 (total 95.000)
(6, 2, 5000,   10),
(6, 6, 45000,  1),
-- Transaksi 7 (total 210.000)
(7, 3, 75000,  2),
(7, 5, 60000,  1),
-- Transaksi 8 (total 120.000)
(8, 4, 120000, 1),
-- Transaksi 9 (total 135.000)
(9, 1, 3000,   15),
(9, 2, 5000,   8),
(9, 8, 25000,  2),
-- Transaksi 10 (total 195.000)
(10, 7, 35000, 3),
(10, 6, 45000, 2);

INSERT INTO pembayaran (waktu_bayar, total, metode, transaksi_id) VALUES
('2026-11-01 09:15:00', 55000,  'TUNAI',    1),
('2026-11-01 11:40:00', 195000, 'TRANSFER', 2),
('2026-11-02 10:05:00', 205000, 'EDC',      3),
('2026-11-02 14:20:00', 145000, 'TUNAI',    4),
('2026-11-03 09:50:00', 95000,  'TRANSFER', 5),
('2026-11-03 13:10:00', 95000,  'TUNAI',    6),
('2026-11-04 10:30:00', 210000, 'EDC',      7),
('2026-11-04 15:00:00', 120000, 'TUNAI',    8),
('2026-11-05 09:05:00', 135000, 'TRANSFER', 9),
('2026-11-05 16:45:00', 195000, 'EDC',      10);
