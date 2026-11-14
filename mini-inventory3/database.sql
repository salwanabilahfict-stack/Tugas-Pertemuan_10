CREATE DATABASE IF NOT EXISTS mini_inventory;
USE mini_inventory;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(20) NOT NULL UNIQUE,
    nama_produk VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    harga INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (kode_produk, nama_produk, kategori, harga, stok) VALUES
('P001','Mechanical Keyboard','Elektronik',550000,8),
('P002','Mouse Wireless','Elektronik',180000,3),
('P003','Laptop Stand','Aksesoris',250000,12),
('P004','USB Hub','Elektronik',150000,0),
('P005','Desk Mat','Aksesoris',120000,7);
