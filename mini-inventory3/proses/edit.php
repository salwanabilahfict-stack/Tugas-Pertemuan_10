<?php
require_once "../koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
$kode = trim($_POST["kode_produk"] ?? "");
$nama = trim($_POST["nama_produk"] ?? "");
$kategori = trim($_POST["kategori"] ?? "");
$harga = filter_var($_POST["harga"] ?? null, FILTER_VALIDATE_INT);
$stok = filter_var($_POST["stok"] ?? null, FILTER_VALIDATE_INT);

$kategoriValid = ["Elektronik", "Aksesoris", "Stationery"];

if (!$id || $id <= 0 || $kode === "" || $nama === "" ||
    !in_array($kategori, $kategoriValid, true) ||
    $harga === false || $harga === null || $harga <= 0 ||
    $stok === false || $stok === null || $stok < 0) {
    die("Input tidak valid. <a href='../index.php'>Kembali</a>");
}

// UPDATE: ubah data produk sesuai ID.
$stmt = $conn->prepare(
    "UPDATE products
     SET kode_produk = ?, nama_produk = ?, kategori = ?, harga = ?, stok = ?
     WHERE id = ?"
);
$stmt->bind_param("sssiii", $kode, $nama, $kategori, $harga, $stok, $id);

if (!$stmt->execute()) {
    if ($stmt->errno === 1062) {
        die("Kode produk sudah digunakan. <a href='../edit.php?id=" . (int) $id . "'>Kembali</a>");
    }
    die("Gagal memperbarui produk. <a href='../index.php'>Kembali</a>");
}

header("Location: ../index.php?status=edit");
exit;
