<?php
require_once "../koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$kode = trim($_POST["kode_produk"] ?? "");
$nama = trim($_POST["nama_produk"] ?? "");
$kategori = trim($_POST["kategori"] ?? "");
$hargaInput = $_POST["harga"] ?? "";
$stokInput = $_POST["stok"] ?? "";

$kategoriValid = ["Elektronik", "Aksesoris", "Stationery"];
$harga = filter_var($hargaInput, FILTER_VALIDATE_INT);
$stok = filter_var($stokInput, FILTER_VALIDATE_INT);

if ($kode === "" || $nama === "" ||
    !in_array($kategori, $kategoriValid, true) ||
    $harga === false || $harga <= 0 ||
    $stok === false || $stok < 0) {
    die("Input tidak valid. <a href='../tambah.php'>Kembali</a>");
}

// CREATE: prepared statement untuk menyimpan produk baru.
$stmt = $conn->prepare(
    "INSERT INTO products (kode_produk, nama_produk, kategori, harga, stok)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssii", $kode, $nama, $kategori, $harga, $stok);

if (!$stmt->execute()) {
    if ($stmt->errno === 1062) {
        die("Kode produk sudah digunakan. <a href='../tambah.php'>Kembali</a>");
    }
    die("Gagal menambahkan produk. <a href='../tambah.php'>Kembali</a>");
}

header("Location: ../index.php?status=tambah");
exit;
