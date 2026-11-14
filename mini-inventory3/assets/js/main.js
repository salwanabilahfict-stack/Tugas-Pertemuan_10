/**
 * MINI INVENTORY - Client-Side Enhancements
 * Pure Vanilla JS, zero dependencies, responsive, enterprise-grade UX.
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveClock();
    initMobileSidebar();
    initToasts();
    initInventoryTable();
    initDeleteModal();
    initFormHelpers();
});

/* -------------------------------------------------------------------------
   1. Live Clock & Date in Topbar
   ------------------------------------------------------------------------- */
function initLiveClock() {
    const clockEl = document.getElementById('liveClock');
    if (!clockEl) return;

    function update() {
        const now = new Date();
        const options = {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        };
        clockEl.textContent = now.toLocaleDateString('id-ID', options);
    }
    update();
    setInterval(update, 30000);
}

/* -------------------------------------------------------------------------
   2. Mobile Sidebar Drawer
   ------------------------------------------------------------------------- */
function initMobileSidebar() {
    const toggleBtn = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    if (!toggleBtn || !sidebar || !backdrop) return;

    function openSidebar() {
        sidebar.classList.add('mobile-open');
        backdrop.classList.add('mobile-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('mobile-open');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', openSidebar);
    backdrop.addEventListener('click', closeSidebar);
}

/* -------------------------------------------------------------------------
   3. Auto-Dismiss Toasts
   ------------------------------------------------------------------------- */
function initToasts() {
    const toasts = document.querySelectorAll('.toast-pill-modern');
    toasts.forEach(toast => {
        const closeBtn = toast.querySelector('.toast-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 250);
            });
        }
        // Auto dismiss after 4 seconds
        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    });
}

/* -------------------------------------------------------------------------
   4. Inventory Table: Live Search, Filter & Column Sorting
   ------------------------------------------------------------------------- */
function initInventoryTable() {
    const table = document.getElementById('productsTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-product-row]'));
    const searchInput = document.getElementById('tableSearchInput');
    const statusFilter = document.getElementById('tableStatusFilter');
    const categoryPills = document.querySelectorAll('.filter-pill[data-category]');
    const countBadge = document.getElementById('tableFilteredCount');
    const emptyRow = document.getElementById('tableEmptyRow');
    const resetFilterBtn = document.getElementById('btnResetFilters');

    let currentCategory = 'all';
    let currentStatus = 'all';
    let currentSearch = '';

    function applyFilters() {
        let visibleCount = 0;

        rows.forEach(row => {
            const code = (row.getAttribute('data-code') || '').toLowerCase();
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            const category = (row.getAttribute('data-category') || '');
            const status = (row.getAttribute('data-status') || '');

            const matchesSearch = currentSearch === '' ||
                code.includes(currentSearch) ||
                name.includes(currentSearch);

            const matchesCategory = currentCategory === 'all' ||
                category.toLowerCase() === currentCategory.toLowerCase();

            const matchesStatus = currentStatus === 'all' ||
                status.toLowerCase() === currentStatus.toLowerCase();

            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countBadge) {
            countBadge.textContent = visibleCount;
        }

        if (emptyRow) {
            emptyRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.trim().toLowerCase();
            applyFilters();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', (e) => {
            currentStatus = e.target.value;
            applyFilters();
        });
    }

    categoryPills.forEach(pill => {
        pill.addEventListener('click', () => {
            categoryPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            currentCategory = pill.getAttribute('data-category');
            applyFilters();
        });
    });

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            currentSearch = '';
            if (statusFilter) statusFilter.value = 'all';
            currentStatus = 'all';
            categoryPills.forEach(p => {
                if (p.getAttribute('data-category') === 'all') {
                    p.classList.add('active');
                } else {
                    p.classList.remove('active');
                }
            });
            currentCategory = 'all';
            applyFilters();
        });
    }

    // Column Sorting
    const sortableHeaders = table.querySelectorAll('th[data-sort]');
    let sortDirection = {};

    sortableHeaders.forEach(th => {
        const column = th.getAttribute('data-sort');
        sortDirection[column] = 'asc';

        th.addEventListener('click', () => {
            const isAsc = sortDirection[column] === 'asc';
            sortDirection[column] = isAsc ? 'desc' : 'asc';

            // Reset other headers indicator
            sortableHeaders.forEach(h => {
                const indicator = h.querySelector('.sort-indicator');
                if (indicator) indicator.textContent = '↕';
            });
            const activeIndicator = th.querySelector('.sort-indicator');
            if (activeIndicator) {
                activeIndicator.textContent = isAsc ? '↑' : '↓';
            }

            rows.sort((a, b) => {
                let valA = a.getAttribute(`data-${column}`) || '';
                let valB = b.getAttribute(`data-${column}`) || '';

                if (column === 'price' || column === 'stock') {
                    valA = parseInt(valA, 10) || 0;
                    valB = parseInt(valB, 10) || 0;
                    return isAsc ? valA - valB : valB - valA;
                }

                return isAsc
                    ? valA.localeCompare(valB, 'id')
                    : valB.localeCompare(valA, 'id');
            });

            rows.forEach(r => tbody.appendChild(r));
            applyFilters();
        });
    });

    // CSV Export
    const exportBtn = document.getElementById('btnExportCsv');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportTableToCsv);
    }
}

function exportTableToCsv() {
    const table = document.getElementById('productsTable');
    if (!table) return;

    const rows = table.querySelectorAll('tr[data-product-row]');
    let csv = [];
    csv.push(['Kode Produk', 'Nama Produk', 'Kategori', 'Harga (Rp)', 'Stok', 'Status'].join(';'));

    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const code = row.getAttribute('data-code') || '';
            const name = `"${(row.getAttribute('data-name') || '').replace(/"/g, '""')}"`;
            const cat = row.getAttribute('data-category') || '';
            const price = row.getAttribute('data-price') || '';
            const stock = row.getAttribute('data-stock') || '';
            const status = row.getAttribute('data-status') || '';
            csv.push([code, name, cat, price, stock, status].join(';'));
        }
    });

    const csvBlob = new Blob(["\uFEFF" + csv.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(csvBlob);
    link.setAttribute('download', `inventaris_produk_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* -------------------------------------------------------------------------
   5. Delete Confirmation Modal
   ------------------------------------------------------------------------- */
function initDeleteModal() {
    const modalBackdrop = document.getElementById('deleteConfirmModal');
    if (!modalBackdrop) return;

    const hiddenInputId = document.getElementById('modalDeleteId');
    const targetNameEl = document.getElementById('modalTargetName');
    const targetCodeEl = document.getElementById('modalTargetCode');
    const btnCancel = document.getElementById('btnCancelDelete');

    function openModal(id, name, code) {
        hiddenInputId.value = id;
        if (targetNameEl) targetNameEl.textContent = name;
        if (targetCodeEl) targetCodeEl.textContent = code;
        modalBackdrop.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modalBackdrop.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-open-delete-modal').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const code = btn.getAttribute('data-code');
            openModal(id, name, code);
        });
    });

    if (btnCancel) {
        btnCancel.addEventListener('click', closeModal);
    }

    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.style.display === 'flex') {
            closeModal();
        }
    });
}

/* -------------------------------------------------------------------------
   6. Form Helpers (Tambah & Edit: Live formatting & Preview)
   ------------------------------------------------------------------------- */
function initFormHelpers() {
    const inputCode = document.getElementById('kode_produk');
    const inputName = document.getElementById('nama_produk');
    const selectCat = document.getElementById('kategori');
    const inputPrice = document.getElementById('harga');
    const inputStock = document.getElementById('stok');

    // Auto SKU generator button
    const btnGenSku = document.getElementById('btnGenerateSku');
    if (btnGenSku && inputCode) {
        btnGenSku.addEventListener('click', () => {
            const prefix = selectCat ? (selectCat.value.substring(0, 3).toUpperCase() || 'PRD') : 'PRD';
            const randomNum = Math.floor(1000 + Math.random() * 9000);
            inputCode.value = `${prefix}-${randomNum}`;
            inputCode.dispatchEvent(new Event('input'));
        });
    }

    // Rupiah preview formatter
    function formatRupiah(val) {
        if (!val || isNaN(val)) return 'Rp 0';
        return 'Rp ' + parseInt(val, 10).toLocaleString('id-ID');
    }

    const pricePreviewEl = document.getElementById('pricePreviewBadge');
    if (inputPrice && pricePreviewEl) {
        inputPrice.addEventListener('input', () => {
            pricePreviewEl.textContent = formatRupiah(inputPrice.value);
        });
        if (inputPrice.value) {
            pricePreviewEl.textContent = formatRupiah(inputPrice.value);
        }
    }

    // Live preview card updater (if present)
    const prevCode = document.getElementById('prevCode');
    const prevName = document.getElementById('prevName');
    const prevCat = document.getElementById('prevCat');
    const prevPrice = document.getElementById('prevPrice');
    const prevStatusPill = document.getElementById('prevStatusPill');

    function updatePreviewCard() {
        if (prevCode && inputCode) {
            prevCode.textContent = inputCode.value.trim() || 'KODE-XXX';
        }
        if (prevName && inputName) {
            prevName.textContent = inputName.value.trim() || 'Nama Produk Inventaris';
        }
        if (prevCat && selectCat) {
            prevCat.textContent = selectCat.value || 'Pilih Kategori';
        }
        if (prevPrice && inputPrice) {
            prevPrice.textContent = formatRupiah(inputPrice.value);
        }
        if (prevStatusPill && inputStock) {
            const stockVal = parseInt(inputStock.value, 10);
            if (isNaN(stockVal) || stockVal <= 0) {
                prevStatusPill.className = 'status-indicator-pill status-pill-empty';
                prevStatusPill.innerHTML = '<span class="dot"></span> Habis';
            } else if (stockVal <= 5) {
                prevStatusPill.className = 'status-indicator-pill status-pill-low';
                prevStatusPill.innerHTML = '<span class="dot"></span> Menipis';
            } else {
                prevStatusPill.className = 'status-indicator-pill status-pill-available';
                prevStatusPill.innerHTML = '<span class="dot"></span> Tersedia';
            }
        }
    }

    if (prevCode) {
        [inputCode, inputName, selectCat, inputPrice, inputStock].forEach(el => {
            if (el) {
                el.addEventListener('input', updatePreviewCard);
                el.addEventListener('change', updatePreviewCard);
            }
        });
        updatePreviewCard();
    }
}
