<?php
require_once "../koneksi.php";

// DELETE hanya boleh dilakukan melalui form POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: ../index.php");
    exit;
}

// Prepared statement mencegah input ID dimasukkan langsung ke SQL.
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../index.php?status=hapus");
exit;
