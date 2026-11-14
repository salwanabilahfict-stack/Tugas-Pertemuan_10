<?php
require_once "koneksi.php";

// ==========================================
// TAMBAH PRODUK
// ==========================================
if (isset($_POST["tambah_produk"])) {

    $kode = trim($_POST["kode_produk"] ?? "");
    $nama = trim($_POST["nama_produk"] ?? "");
    $kategori = trim($_POST["kategori"] ?? "");

    $harga = filter_var($_POST["harga"] ?? null, FILTER_VALIDATE_FLOAT);
    $stok = filter_var($_POST["stok"] ?? null, FILTER_VALIDATE_INT);

    if (
        $kode === "" ||
        $nama === "" ||
        $kategori === "" ||
        $harga === false || $harga === null || $harga < 0 ||
        $stok === false || $stok === null || $stok < 0
    ) {
        header("Location: index.php?status=validasi");
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO products
            (kode_produk, nama_produk, kategori, harga, stok, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())"
        );

        $stmt->bind_param(
            "sssdi",
            $kode,
            $nama,
            $kategori,
            $harga,
            $stok
        );

        $stmt->execute();
        $stmt->close();

        header("Location: index.php?status=berhasil");
        exit;

    } catch (mysqli_sql_exception $e) {

        error_log($e->getMessage());

        header(
            "Location: index.php?status=gagal&pesan=" .
            urlencode($e->getMessage())
        );
        exit;
    }
}

// ==========================================
// EXPORT CSV
// ==========================================
if (isset($_GET["export"]) && $_GET["export"] === "csv") {

    $data = $conn->query(
        "SELECT id, kode_produk, nama_produk, kategori,
                harga, stok, created_at
         FROM products ORDER BY id DESC"
    );

    if (!$data) {
        die("Export gagal: " . htmlspecialchars($conn->error));
    }

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=database_produk.csv");

    $file = fopen("php://output", "w");

    // Agar karakter Excel terbaca dengan benar
    fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($file, [
        "ID",
        "Kode Produk",
        "Nama Produk",
        "Kategori",
        "Harga",
        "Stok",
        "Tanggal Dibuat"
    ]);

    while ($row = $data->fetch_assoc()) {
        fputcsv($file, $row);
    }

    fclose($file);
    exit;
}

// ==========================================
// EXPORT SQL
// ==========================================
if (isset($_GET["export"]) && $_GET["export"] === "sql") {

    $data = $conn->query(
        "SELECT id, kode_produk, nama_produk, kategori,
                harga, stok, created_at
         FROM products ORDER BY id ASC"
    );

    if (!$data) {
        die("Backup gagal: " . htmlspecialchars($conn->error));
    }

    header("Content-Type: application/sql; charset=utf-8");
    header("Content-Disposition: attachment; filename=backup_produk.sql");

    echo "-- BACKUP DATABASE MINI INVENTORY\n";
    echo "SET NAMES utf8mb4;\n\n";

    while ($row = $data->fetch_assoc()) {

        $id = (int)$row["id"];
        $kode = $conn->real_escape_string($row["kode_produk"]);
        $nama = $conn->real_escape_string($row["nama_produk"]);
        $kategori = $conn->real_escape_string($row["kategori"]);
        $harga = (float)$row["harga"];
        $stok = (int)$row["stok"];
        $created = $conn->real_escape_string($row["created_at"]);

        echo "INSERT INTO products
        (id, kode_produk, nama_produk, kategori, harga, stok, created_at)
        VALUES
        ($id, '$kode', '$nama', '$kategori', $harga, $stok, '$created');\n";
    }

    exit;
}

// ==========================================
// AMBIL DATA PRODUK
// ==========================================
$result = $conn->query(
    "SELECT * FROM products ORDER BY id DESC"
);

if (!$result) {
    die("Query gagal: " . htmlspecialchars($conn->error));
}

$products = [];

$totalStok = 0;
$totalNilai = 0;
$stokMenipis = 0;
$stokHabis = 0;

while ($row = $result->fetch_assoc()) {

    $products[] = $row;

    $stok = (int)$row["stok"];
    $harga = (float)$row["harga"];

    $totalStok += $stok;
    $totalNilai += $stok * $harga;

    if ($stok == 0) {
        $stokHabis++;
    } elseif ($stok <= 5) {
        $stokMenipis++;
    }
}

$totalProduk = count($products);

function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mini Inventory Dashboard</title>

    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6fb;
            color: #202d43;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #101d35;
            color: white;
            padding: 28px 20px;
            z-index: 1000;
        }

        .brand {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 42px;
        }

        .brand i {
            color: #60a5fa;
        }

        .menu-label {
            color: #8194b2;
            font-size: 10px;
            letter-spacing: 2px;
            margin: 25px 12px 12px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 12px 14px;
            margin-bottom: 7px;
            border-radius: 10px;
            color: #bdc9dc;
            text-decoration: none;
            font-size: 13px;
            transition: .2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2854bc;
            color: white;
        }

        .main {
            margin-left: 250px;
            padding: 30px 38px;
        }

        .topbar {
            margin-bottom: 28px;
        }

        .profile {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 12px;
            padding: 9px 15px;
        }

        .hero {
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px #142b4a12;
        }

        .hero-slide {
            min-height: 280px;
            padding: 45px 55px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            color: white;
        }

        .hero-1 {
            background: linear-gradient(115deg, #10213e, #28558f);
        }

        .hero-2 {
            background: linear-gradient(115deg, #123b4a, #18878b);
        }

        .hero-3 {
            background: linear-gradient(115deg, #29264f, #6255a2);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 520px;
        }

        .hero-content small {
            color: #a9ceff;
            letter-spacing: 2px;
            font-weight: 700;
        }

        .hero-content h1 {
            font-size: 34px;
            font-weight: 800;
            margin: 15px 0;
        }

        .hero-content p {
            font-size: 13px;
            color: #d9e6f5;
        }

        .hero-art {
            position: absolute;
            right: 12%;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: #ffffff18;
            display: grid;
            place-items: center;
        }

        .hero-art i {
            font-size: 85px;
            color: white;
        }

        .hero-btn {
            display: inline-block;
            background: white;
            color: #244779;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            margin-top: 10px;
        }

        .stat-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 22px;
            height: 100%;
            transition: .2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px #142b4a12;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 21px;
        }

        .stat-number {
            font-size: 25px;
            font-weight: 800;
            margin-top: 16px;
        }

        .stat-name {
            color: #8491a7;
            font-size: 12px;
        }

        .product-panel {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
        }

        .search-input {
            background: #f8faff;
            border: 1px solid #e9edf4;
            border-radius: 8px;
            font-size: 12px;
        }

        .table thead th {
            background: #f3f6fb;
            color: #78869c;
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 16px;
            border: none;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 16px;
            font-size: 12px;
            vertical-align: middle;
        }

        .stock-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 7px 11px;
            border-radius: 7px;
        }

        .action-buttons .btn {
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
        }

        .footer {
            color: #a0aaba;
            font-size: 11px;
            text-align: center;
            padding: 25px;
        }

        .modal-content {
            border: 0;
            border-radius: 15px;
        }

        .form-control,
        .form-select {
            border-radius: 9px;
            padding: 11px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            .sidebar,
            .topbar,
            .hero,
            .stat-card,
            .action-buttons,
            .footer {
                display: none !important;
            }

            .main {
                margin: 0 !important;
                padding: 0 !important;
            }

            .product-panel {
                border: none !important;
                padding: 0 !important;
            }
        }

        @media(max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .main {
                margin-left: 0;
                padding: 20px 15px;
            }

            .hero-slide {
                min-height: 240px;
                padding: 25px;
            }

            .hero-content h1 {
                font-size: 25px;
            }

            .hero-art {
                right: -65px;
                opacity: .3;
            }

            .product-panel {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="brand">
        <i class="bi bi-box-seam me-2"></i>
        INVENTORY
    </div>

    <div class="menu-label">WORKSPACE</div>

    <a href="index.php" class="active">
        <i class="bi bi-grid-fill"></i> Dashboard
    </a>

    <a href="#product-list">
        <i class="bi bi-boxes"></i> Data Produk
    </a>

    <a href="#" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-square"></i> Tambah Produk
    </a>

    <a href="#product-list" onclick="window.print(); return false;">
        <i class="bi bi-printer"></i> Cetak Laporan
    </a>

    <div class="menu-label">DATA MANAGEMENT</div>

    <a href="index.php?export=csv">
        <i class="bi bi-file-earmark-excel"></i> Download CSV
    </a>

    <a href="index.php?export=sql">
        <i class="bi bi-database-down"></i> Backup Database
    </a>

    <div class="mt-5 p-3 rounded-3" style="background:#ffffff10">
        <i class="bi bi-shield-check text-info fs-4"></i>
        <div class="fw-bold small mt-2">Inventory System</div>
        <small class="text-secondary">Manage your stock smarter.</small>
    </div>

</aside>

<!-- MAIN -->
<main class="main">

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET["status"])): ?>

        <?php if ($_GET["status"] === "berhasil"): ?>

            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                Produk berhasil ditambahkan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET["status"] === "gagal"): ?>

            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Gagal menambahkan produk.</strong>
                <div class="small mt-1">
                    <?= htmlspecialchars($_GET["pesan"] ?? "Periksa struktur database.") ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET["status"] === "validasi"): ?>

            <div class="alert alert-warning alert-dismissible fade show">
                Data tidak valid. Periksa kembali semua isian.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>

    <?php endif; ?>

    <!-- HEADER -->
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h3 class="fw-bold mb-1">Dashboard Overview</h3>
            <p class="text-secondary small mb-0">
                Welcome back! Here's your inventory summary.
            </p>
        </div>

        <div class="profile d-flex align-items-center gap-2">
            <i class="bi bi-person-circle text-primary fs-4"></i>
            <div>
                <strong class="d-block small">Administrator</strong>
                <small class="text-secondary">Super Admin</small>
            </div>
        </div>

    </div>

    <!-- CAROUSEL -->
    <div id="heroCarousel" class="carousel slide hero" data-bs-ride="carousel">

        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>

        <div class="carousel-inner">

            <div class="carousel-item active">
                <div class="hero-slide hero-1">
                    <div class="hero-content">
                        <small>SMART INVENTORY SOLUTION</small>
                        <h1>Manage Everything.<br>In One Place.</h1>
                        <p>Kelola persediaan produk dengan sistem modern, terstruktur, dan efisien.</p>
                        <a href="#product-list" class="hero-btn">Explore Dashboard <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="hero-art">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide hero-2">
                    <div class="hero-content">
                        <small>STOCK MONITORING</small>
                        <h1>Stay In Control<br>Of Your Inventory.</h1>
                        <p>Pantau jumlah stok dan ketahui produk yang perlu segera ditambahkan.</p>
                        <a href="#product-list" class="hero-btn">Monitor Stock <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="hero-art">
                        <i class="bi bi-stack"></i>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide hero-3">
                    <div class="hero-content">
                        <small>BUSINESS MANAGEMENT</small>
                        <h1>Better Data.<br>Better Decisions.</h1>
                        <p>Informasi inventaris tersusun rapi untuk pengelolaan produk.</p>
                        <a href="#product-list" class="hero-btn">View Products <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="hero-art">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>

        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>

    </div>

    <!-- STATISTIK -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold fs-6 mb-0">Inventory Statistics</h5>
        <span class="small text-secondary">
            <i class="bi bi-circle-fill text-success me-1" style="font-size:7px"></i>
            Live Summary
        </span>
    </div>

    <div class="row g-3">

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-number"><?= $totalProduk ?></div>
                <div class="stat-name">Total Produk</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-layers"></i>
                </div>
                <div class="stat-number"><?= $totalStok ?></div>
                <div class="stat-name">Total Stok</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?= $stokMenipis ?></div>
                <div class="stat-name">Stok Menipis</div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-number"><?= $stokHabis ?></div>
                <div class="stat-name">Stok Habis</div>
            </div>
        </div>

    </div>

    <!-- TOTAL NILAI PERSEDIAAN -->
    <div class="mt-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="stat-name">Total Nilai Persediaan</div>
                    <div class="fw-bold fs-4"><?= rupiah($totalNilai) ?></div>
                    <small class="text-secondary">Perhitungan total harga × jumlah stok</small>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL PRODUK -->
    <section class="product-panel" id="product-list">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>
                <h5 class="fw-bold mb-1">Product Inventory</h5>
                <p class="text-secondary small mb-0">
                    Manage and monitor your product availability.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2 action-buttons">

                <input type="text" id="searchProduct"
                    class="form-control search-input"
                    placeholder="Cari produk..."
                    style="width:170px">

                <button class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg"></i> Tambah
                </button>

                <button class="btn btn-success btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> Cetak
                </button>

                <a href="index.php?export=csv" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> CSV
                </a>

                <a href="index.php?export=sql" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-database-down"></i> SQL
                </a>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0" id="productTable">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($products as $product): ?>

                    <tr>
                        <td class="text-secondary">
                            #<?= htmlspecialchars($product["id"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($product["kode_produk"]) ?>
                        </td>

                        <td class="fw-semibold">
                            <?= htmlspecialchars($product["nama_produk"]) ?>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark">
                                <?= htmlspecialchars($product["kategori"]) ?>
                            </span>
                        </td>

                        <td>
                            <?= rupiah($product["harga"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($product["stok"]) ?> Unit
                        </td>

                        <td>
                            <?php if ((int)$product["stok"] == 0): ?>
                                <span class="stock-badge bg-danger-subtle text-danger">Habis</span>
                            <?php elseif ((int)$product["stok"] <= 5): ?>
                                <span class="stock-badge bg-warning-subtle text-warning-emphasis">Menipis</span>
                            <?php else: ?>
                                <span class="stock-badge bg-success-subtle text-success">Tersedia</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-secondary">
                            <?= htmlspecialchars($product["created_at"]) ?>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Belum ada data produk.
                        </td>
                    </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

    <footer class="footer">
        © 2026 Mini Inventory Dashboard. All rights reserved.
    </footer>

</main>

<!-- MODAL TAMBAH PRODUK -->
<div class="modal fade" id="addProductModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle text-primary me-2"></i>
                    Tambah Produk
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="index.php">

                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Produk</label>
                        <input type="text"
                            name="kode_produk"
                            class="form-control"
                            placeholder="Contoh: P006"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Produk</label>
                        <input type="text"
                            name="nama_produk"
                            class="form-control"
                            placeholder="Masukkan nama produk"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">Pilih kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Peralatan">Peralatan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga (Rp)</label>
                        <input type="number"
                            name="harga"
                            class="form-control"
                            placeholder="Contoh: 150000"
                            min="0"
                            step="0.01"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Stok</label>
                        <input type="number"
                            name="stok"
                            class="form-control"
                            placeholder="Masukkan jumlah stok"
                            min="0"
                            step="1"
                            required>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" name="tambah_produk" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Produk
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- BOOTSTRAP JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- PENCARIAN PRODUK -->
<script>
document.getElementById("searchProduct").addEventListener("input", function() {

    const keyword = this.value.toLowerCase();

    document.querySelectorAll("#productTable tbody tr").forEach(row => {

        row.style.display = row.innerText.toLowerCase().includes(keyword)
            ? ""
            : "none";

    });

});
</script>

</body>
</html>