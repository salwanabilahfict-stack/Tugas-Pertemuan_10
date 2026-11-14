<?php
require_once "koneksi.php";

// Ambil ID dari URL dan pastikan berupa bilangan bulat positif.
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

// UPDATE step 1: ambil satu produk berdasarkan ID.
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk • <?= htmlspecialchars($product["nama_produk"]) ?> • Mini Inventory</title>
    
    <!-- Custom Modern Refined Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- SIDEBAR -->
    <aside class="app-sidebar" id="appSidebar">
        
        <div class="sidebar-header">
            <div class="brand-wrapper">
                <div class="brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m7.5 4.27 9 5.15"></path>
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                        <path d="m3.3 7 8.7 5 8.7-5"></path>
                        <path d="M12 22V12"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="brand-title">Mini Inventory</h1>
                    <span class="brand-badge">Sistem Stok</span>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div>
                <div class="nav-group-title">Menu Utama</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="index.php">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                            </svg>
                            <span>Dashboard & Data</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="tambah.php">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 12h8"></path>
                                <path d="M12 8v8"></path>
                            </svg>
                            <span>Tambah Produk</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="system-status-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; font-weight: 700; color: #cbd5e1; text-transform: uppercase;">Status Server</span>
                    <span class="status-dot-pulse"></span>
                </div>
                <div style="font-size: 12px; color: #94a3b8;">
                    MySQL Database • Aktif
                </div>
            </div>
        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="app-main">

        <!-- TOPBAR -->
        <header class="app-topbar">
            <div class="topbar-left">
                <button type="button" class="btn-mobile-menu" id="btnToggleSidebar" aria-label="Buka Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="4" x2="20" y1="12" y2="12"></line>
                        <line x1="4" x2="20" y1="6" y2="6"></line>
                        <line x1="4" x2="20" y1="18" y2="18"></line>
                    </svg>
                </button>

                <nav class="breadcrumb-nav">
                    <a href="index.php" style="color: #64748b;">Dashboard</a>
                    <span>/</span>
                    <span class="breadcrumb-item active">Edit Produk: <?= htmlspecialchars($product["kode_produk"]) ?></span>
                </nav>
            </div>

            <div class="topbar-right">
                <div class="clock-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span id="liveClock">Memuat waktu...</span>
                </div>

                <div class="user-profile-chip">
                    <div class="avatar-initial">AD</div>
                    <div style="display: flex; flex-direction: column; text-align: left; padding-right: 6px;">
                        <span style="font-size: 12px; font-weight: 700; line-height: 1.1;">Admin</span>
                        <span style="font-size: 10px; color: #64748b;">Pengelola</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <div class="content-body">

            <div class="page-header">
                <div>
                    <h2 class="page-title">Edit Data Produk</h2>
                    <p class="page-subtitle">Perbarui data spesifikasi, harga satuan, atau penyesuaian stok barang.</p>
                </div>

                <div>
                    <a href="index.php" class="btn-secondary-custom">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali ke Tabel
                    </a>
                </div>
            </div>

            <div class="form-card-container">
                <div class="form-layout-grid">
                    
                    <!-- MAIN FORM -->
                    <div class="form-main-card">
                        
                        <div class="form-section-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                <path d="m15 5 4 4"></path>
                            </svg>
                            Perbarui Data #<?= (int)$product["id"] ?>
                        </div>

                        <!-- EXACT BACKEND COMPATIBLE FORM -->
                        <form action="proses/edit.php" method="POST">
                            <input type="hidden" name="id" value="<?= (int)$product["id"] ?>">

                            <!-- Kode Produk -->
                            <div class="form-group-custom">
                                <label for="kode_produk" class="form-label-custom">Kode Produk (SKU)</label>
                                <div class="input-with-icon-wrap">
                                    <span class="input-icon-prefix">#</span>
                                    <input id="kode_produk"
                                           name="kode_produk"
                                           type="text"
                                           maxlength="20"
                                           class="form-control-custom has-icon"
                                           value="<?= htmlspecialchars($product["kode_produk"]) ?>"
                                           required>
                                </div>
                                <div class="form-help-hint">
                                    Identitas kode barang unik di database.
                                </div>
                            </div>

                            <!-- Nama Produk -->
                            <div class="form-group-custom">
                                <label for="nama_produk" class="form-label-custom">Nama Produk</label>
                                <input id="nama_produk"
                                       name="nama_produk"
                                       type="text"
                                       maxlength="100"
                                       class="form-control-custom"
                                       value="<?= htmlspecialchars($product["nama_produk"]) ?>"
                                       required>
                            </div>

                            <!-- Kategori -->
                            <div class="form-group-custom">
                                <label for="kategori" class="form-label-custom">Kategori Produk</label>
                                <select id="kategori" name="kategori" class="form-control-custom" required>
                                    <?php foreach (["Elektronik", "Aksesoris", "Stationery"] as $kategori): ?>
                                        <option value="<?= htmlspecialchars($kategori) ?>" <?= $product["kategori"] === $kategori ? "selected" : "" ?>>
                                            <?= htmlspecialchars($kategori) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Harga & Stok Grid -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                
                                <div class="form-group-custom">
                                    <label for="harga" class="form-label-custom">Harga Satuan</label>
                                    <div class="input-with-icon-wrap">
                                        <span class="input-icon-prefix" style="font-size: 13px;">Rp</span>
                                        <input id="harga"
                                               name="harga"
                                               type="number"
                                               min="1"
                                               step="1"
                                               class="form-control-custom has-icon"
                                               value="<?= (int)$product["harga"] ?>"
                                               required>
                                    </div>
                                    <span class="form-price-preview-badge" id="pricePreviewBadge">
                                        Rp <?= number_format($product["harga"], 0, ",", ".") ?>
                                    </span>
                                </div>

                                <div class="form-group-custom">
                                    <label for="stok" class="form-label-custom">Stok Saat Ini</label>
                                    <input id="stok"
                                           name="stok"
                                           type="number"
                                           min="0"
                                           step="1"
                                           class="form-control-custom"
                                           value="<?= (int)$product["stok"] ?>"
                                           required>
                                    <div class="form-help-hint">
                                        Perbarui jumlah unit fisik di rak.
                                    </div>
                                </div>

                            </div>

                            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;">

                            <div style="display: flex; gap: 12px; align-items: center;">
                                <button type="submit" class="btn-primary-custom" style="padding: 11px 24px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Update Produk
                                </button>
                                <a class="btn-secondary-custom" href="index.php" style="padding: 11px 20px;">
                                    Batal
                                </a>
                            </div>

                        </form>

                    </div>


                    <!-- LIVE PREVIEW CARD -->
                    <div class="form-preview-card">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                            Pratinjau Hasil Perubahan
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-lg); padding: 18px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <span class="sku-tag" id="prevCode"><?= htmlspecialchars($product["kode_produk"]) ?></span>
                                <span class="category-tag" id="prevCat"><?= htmlspecialchars($product["kategori"]) ?></span>
                            </div>

                            <div style="font-weight: 700; font-size: 16px; color: #0f172a; margin-bottom: 10px; line-height: 1.3;" id="prevName">
                                <?= htmlspecialchars($product["nama_produk"]) ?>
                            </div>

                            <div style="font-size: 20px; font-weight: 800; color: #2563eb; margin-bottom: 16px;" id="prevPrice">
                                Rp <?= number_format($product["harga"], 0, ",", ".") ?>
                            </div>

                            <div style="padding-top: 12px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 12px; color: #64748b;">Status Terkini:</span>
                                <?php
                                $stokAwal = (int)$product["stok"];
                                if ($stokAwal == 0) {
                                    $pClass = "status-pill-empty";
                                    $pText = "Habis";
                                } elseif ($stokAwal <= 5) {
                                    $pClass = "status-pill-low";
                                    $pText = "Menipis";
                                } else {
                                    $pClass = "status-pill-available";
                                    $pText = "Tersedia";
                                }
                                ?>
                                <span class="status-indicator-pill <?= $pClass ?>" id="prevStatusPill">
                                    <span class="dot"></span> <?= $pText ?>
                                </span>
                            </div>
                        </div>

                        <div style="margin-top: 16px; font-size: 12px; color: #94a3b8; line-height: 1.4;">
                            ℹ️ Perubahan harga atau stok akan langsung mempengaruhi nilai aset dan filter tabel.
                        </div>
                    </div>

                </div>
            </div>

            <!-- FOOTER -->
            <footer style="margin-top: 40px; text-align: center; font-size: 12px; color: #94a3b8; padding-bottom: 20px;">
                <p style="margin: 0;">Mini Inventory Management System • Ditenagai oleh PHP & MySQL</p>
            </footer>

        </div>

    </main>

</div>

<!-- SCRIPTS -->
<script src="assets/js/main.js"></script>

</body>
</html>
