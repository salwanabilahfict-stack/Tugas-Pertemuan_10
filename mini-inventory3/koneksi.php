<?php
$conn = new mysqli("localhost", "root", "", "mini_inventory");

if ($conn->connect_error) {
    die("Koneksi database gagal: " . htmlspecialchars($conn->connect_error));
}

$conn->set_charset("utf8mb4");
?>
