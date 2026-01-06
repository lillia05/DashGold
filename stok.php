<?php
include 'koneksi.php';

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

$whereClause = "WHERE 1=1"; 

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $whereClause .= " AND (s.nama_barang LIKE '%$search%' OR s.supplier LIKE '%$search%')";
}

if (isset($_GET['status_filter']) && !empty($_GET['status_filter']) && $_GET['status_filter'] != 'all') {
    $statusFilter = $_GET['status_filter'] == 'Tersedia' ? 'available' : 'sold';
    $whereClause .= " AND s.status = '$statusFilter'";
}

$queryCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM stocks s $whereClause");
$dataCount = mysqli_fetch_assoc($queryCount);
$totalData = $dataCount['total'];
$totalPages = ceil($totalData / $limit);

$query = mysqli_query($conn, "SELECT s.*, t.nama_pembeli, t.profit, t.harga_jual_total, t.tanggal_jual 
                              FROM stocks s 
                              LEFT JOIN transactions t ON s.id = t.stock_id 
                              $whereClause 
                              ORDER BY s.created_at DESC 
                              LIMIT $limit OFFSET $offset");

$firstItem = ($totalData > 0) ? ($offset + 1) : 0;
$lastItem = ($offset + $limit < $totalData) ? ($offset + $limit) : $totalData;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok - SS Gold</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .table-row { transition: all 0.2s ease; }
        .table-row:hover { background-color: #f0fdf4; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300 md:hidden"></div>

    <div class="flex h-screen overflow-hidden">
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-56 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:flex">
           <div class="h-16 flex items-center justify-between md:justify-center px-4 border-b border-gray-100 relative">
                <div class="flex items-center gap-3">
                    <img src="logo.png" alt="Logo Icon" class="h-12 w-auto object-contain">
                    <img src="namalogo.png" alt="Nama Logo" class="h-5 w-auto object-contain">
                </div>
                <button onclick="toggleSidebar()" class="text-gray-500 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="index.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Dashboard</a>
                <a href="stok.php" class="flex items-center px-4 py-3 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-semibold">Stok</a>
                <a href="transaksi.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Riwayat Transaksi</a>
            </nav>
            <div class="p-4 border-t text-xs text-gray-400 text-center">SS Gold &copy; <?php echo date('Y'); ?></div>
        </aside>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
            
            <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-12">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="p-2 -ml-2 text-gray-600 rounded-md md:hidden">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Manajemen Stok</h2>
                        <p class="text-sm text-gray-500 mt-1">Pantau ketersediaan dan status barang.</p>
                    </div>
                </div>
                <a href="input-stok.php" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-sm shadow-emerald-200 transition flex items-center gap-2 text-sm w-full md:w-auto justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Input Stok Baru</span>
                </a>
            </header>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-8">
                <form method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 w-full">
                        <input type="text" name="search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>" placeholder="Cari Nama Barang / Supplier..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                        <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                        <select name="status_filter" onchange="this.form.submit()" class="w-full md:w-48 py-2.5 px-4 border border-gray-300 rounded-lg text-sm bg-white text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer">
                            <option value="all" <?= (!isset($_GET['status_filter']) || $_GET['status_filter'] == 'all') ? 'selected' : '' ?>>Semua Status</option>
                            <option value="Tersedia" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Tersedia') ? 'selected' : '' ?>>Tersedia</option>
                            <option value="Terjual" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Terjual') ? 'selected' : '' ?>>Terjual / Habis</option>
                        </select>
                        <button type="submit" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-6 rounded-lg text-sm transition shadow-sm">
                            Cari
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-white text-gray-500 uppercase text-xs border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">No</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Nama Barang</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Tahun</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Supplier</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Stok (Gr)</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Status</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Harga Beli</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php 
                            $no = $offset + 1;
                            while($row = mysqli_fetch_assoc($query)) { 
                            ?>
                            <tr class="table-row">
                                <td class="px-6 py-4 text-gray-500"><?= $no++; ?></td>
                                <td class="px-6 py-4 font-bold text-gray-900"><?= $row['nama_barang']; ?></td>
                                <td class="px-6 py-4 text-center text-gray-600 font-medium bg-gray-50/50 rounded"><?= $row['tahun_terbit']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= $row['supplier']; ?></td>
                                <td class="px-6 py-4 text-center"><?= $row['berat']; ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if($row['status'] == 'available'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Terjual
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-500"><?= number_format($row['harga_beli_total'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <button onclick="bukaModalDetail(
                                            '<?= addslashes($row['nama_barang']) ?>', 
                                            '<?= addslashes($row['tahun_terbit']) ?>',
                                            '<?= addslashes($row['supplier']) ?>', 
                                            '<?= $row['berat'] ?>', 
                                            '<?= $row['status'] ?>', 
                                            '<?= date('d M Y', strtotime($row['tanggal_beli'])) ?>', 
                                            '<?= number_format($row['harga_beli_total'],0,',','.') ?>',
                                            '<?= addslashes($row['nama_pembeli'] ?? '-') ?>',
                                            '<?= number_format($row['profit'] ?? 0,0,',','.') ?>'
                                        )" class="text-emerald-500 hover:text-emerald-700 transition" title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        
                                        <button onclick="bukaModalEdit(
                                            '<?= $row['id'] ?>', 
                                            '<?= addslashes($row['nama_barang']) ?>', 
                                            '<?= addslashes($row['tahun_terbit']) ?>',
                                            '<?= addslashes($row['supplier']) ?>', 
                                            '<?= $row['berat'] ?>', 
                                            '<?= $row['status'] ?>', 
                                            '<?= $row['tanggal_beli'] ?>', 
                                            '<?= $row['harga_beli_total'] ?>',
                                            '<?= addslashes($row['nama_pembeli'] ?? '') ?>',
                                            '<?= $row['tanggal_jual'] ?? '' ?>',
                                            '<?= $row['harga_jual_total'] ?? '' ?>'
                                        )" class="text-amber-500 hover:text-amber-700 transition" title="Edit Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        
                                        <button onclick="bukaModalHapus('<?= $row['id'] ?>', '<?= addslashes($row['nama_barang']) ?>')" class="text-red-500 hover:text-red-700 transition" title="Hapus Stok">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="modalEdit" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
                <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl overflow-hidden overflow-y-auto max-h-[90vh]">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">Form Edit Data Stok</h2>
                        <button onclick="tutupModalEdit()" class="text-gray-400 hover:text-red-500 transition text-2xl">&times;</button>
                    </div>
                    <form class="p-6 md:p-8" action="proses_stok.php?act=update" method="POST" onsubmit="prepareSubmitEdit()">
                        <input type="hidden" name="id" id="editId">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                                <input name="nama_barang" id="editNama" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Terbit</label>
                                <input name="tahun_terbit" id="editTahun" type="number" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                <input name="supplier" id="editSupplier" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Stok (Gr)</label>
                                <input name="berat" id="editStok" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Beli</label>
                                <input name="tanggal_beli" id="editTanggal" type="date" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Beli (Total)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm font-medium">Rp</span>
                                    <input type="text" id="editDisplayHarga" class="w-full border border-gray-300 rounded-lg pl-10 p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" onkeyup="formatRupiah(this)">
                                    <input type="hidden" name="harga_beli" id="editRealHarga">
                                </div>
                            </div>
                            
                            <div class="col-span-1 md:col-span-2 pt-4 border-t border-dashed border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Barang</label>
                                <select name="status" id="editStatus" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer">
                                    <option value="available">Tersedia</option>
                                    <option value="sold">Terjual</option>
                                </select>
                            </div>
                            
                            <div id="salesFields" class="hidden col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="font-bold text-emerald-800 text-sm flex items-center gap-2">
                                        Informasi Penjualan (Wajib diisi jika Terjual)
                                    </h4>
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pembeli</label>
                                    <input name="nama_pembeli" id="editPembeli" type="text" placeholder="Masukkan nama pembeli..." class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Jual</label>
                                    <input name="tanggal_jual" id="editTglJual" type="date" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga Jual (Total)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-gray-500 text-sm font-medium">Rp</span>
                                        <input type="text" id="editDisplayHargaJual" class="w-full border border-gray-300 rounded-lg pl-10 p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" onkeyup="formatRupiah(this)">
                                        <input type="hidden" name="harga_jual" id="editRealHargaJual">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-8 mt-4 border-t border-gray-100">
                            <button type="button" onclick="tutupModalEdit()" class="px-6 py-2.5 text-gray-600 hover:bg-gray-100 border border-emerald-700 rounded-lg text-sm font-medium transition">Batal</button>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-8 rounded-lg shadow-md transition text-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="modalDetail" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
                <div class="bg-white rounded-xl w-full max-w-2xl shadow-2xl transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Detail Data Stok</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Informasi lengkap barang ini.</p>
                        </div>
                        <div class="flex items-center gap-5">
                            <span id="detailBadge" class="px-4 py-1.5 text-xs font-bold uppercase rounded-full border tracking-wide">Status</span>
                            <button onclick="tutupModalDetail()" class="text-gray-400 hover:text-red-500 transition text-2xl">&times;</button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Nama Barang</label>
                                <input type="text" id="detailNama" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-900 focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Tahun Terbit</label>
                                <input type="text" id="detailTahun" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm font-medium text-gray-800 focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Supplier</label>
                                <input type="text" id="detailSupplier" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm font-medium text-gray-800 focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Jumlah Stok (Gr)</label>
                                <input type="text" id="detailStok" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm font-medium text-gray-800 focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Tanggal Beli</label>
                                <input type="text" id="detailTgl" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm font-medium text-gray-800 focus:outline-none" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Harga Beli (Total)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-gray-500 text-sm font-bold">Rp</span>
                                    <input type="text" id="detailHarga" class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 p-3 text-sm font-bold text-gray-900 focus:outline-none" readonly>
                                </div>
                            </div>
                            
                            <div class="col-span-1 md:col-span-2 hidden" id="infoTerjual">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Status Barang Saat Ini</label>
                                <div class="w-full bg-red-50 border border-red-100 rounded-lg p-3 text-sm font-bold text-red-700 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Barang Sudah Terjual
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-100 hidden" id="infoRiwayat">
                            <h3 class="text-sm font-bold text-gray-900 mb-4">Riwayat Penjualan Terkait</h3>
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] text-blue-600 font-bold uppercase mb-1">Pembeli</p>
                                    <p class="text-sm font-bold text-gray-800" id="valPembeli">-</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-blue-600 font-bold uppercase mb-1">Profit Didapat</p>
                                    <p class="text-lg font-black text-emerald-600" id="valProfit">+ Rp 0</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4">
                            <button type="button" onclick="tutupModalDetail()" class="hover:bg-gray-200 text-gray-700 font-bold py-2.5 px-6 border border-emerald-700 rounded-lg transition text-sm">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-col md:flex-row items-center justify-between gap-4">
                <span class="text-xs text-gray-500">
                    Menampilkan <b><?= $firstItem; ?></b> - <b><?= $lastItem; ?></b> dari <b><?= $totalData; ?></b> data stok
                </span>
                <div class="flex gap-1">
                    <?php if($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-1 text-xs border rounded bg-white text-gray-600 hover:bg-gray-100 transition">Prev</a>
                    <?php else: ?>
                        <button class="px-3 py-1 text-xs border rounded bg-gray-100 text-gray-400 cursor-not-allowed">Prev</button>
                    <?php endif; ?>

                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="px-3 py-1 text-xs border rounded <?= ($i == $page) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 hover:bg-gray-50' ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-1 text-xs border rounded bg-white text-gray-600 hover:bg-gray-100 transition">Next</a>
                    <?php else: ?>
                        <button class="px-3 py-1 text-xs border rounded bg-gray-100 text-gray-400 cursor-not-allowed">Next</button>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <div id="modalHapus" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-[60] backdrop-blur-sm px-4">
        <div class="bg-white rounded-2xl w-full max-w-sm p-8 shadow-2xl">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Hapus Data?</h3>
                <p class="text-sm text-gray-500">Anda yakin ingin menghapus <span id="namaBarangHapus" class="font-bold text-gray-900"></span>? Tindakan ini permanen.</p>
                <div class="flex w-full gap-3 mt-8">
                    <button onclick="tutupModalHapus()" class="flex-1 px-4 py-2.5 text-gray-700 border border-red-700 rounded-xl text-sm font-bold hover:bg-gray-200 transition">Batal</button>
                    <button onclick="eksekusiHapus()" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700 transition">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let idHapusGlobal = null;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        document.getElementById('editStatus').addEventListener('change', function() {
            const salesFields = document.getElementById('salesFields');
            if (this.value === 'sold') {
                salesFields.classList.remove('hidden');
                document.getElementById('editPembeli').setAttribute('required', 'required');
            } else {
                salesFields.classList.add('hidden');
                document.getElementById('editPembeli').removeAttribute('required');
            }
        });

        function formatRupiah(element) {
            let value = element.value.replace(/[^,\d]/g, '');
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            element.value = rupiah;

            if (element.id === 'editDisplayHarga') {
                document.getElementById('editRealHarga').value = rupiah.replace(/\./g, '');
            } else if (element.id === 'editDisplayHargaJual') {
                document.getElementById('editRealHargaJual').value = rupiah.replace(/\./g, '');
            }
        }

        function prepareSubmitEdit() {
            let displayVal = document.getElementById('editDisplayHarga').value;
            document.getElementById('editRealHarga').value = displayVal.replace(/\./g, '');

            let displayValJual = document.getElementById('editDisplayHargaJual').value;
            document.getElementById('editRealHargaJual').value = displayValJual ? displayValJual.replace(/\./g, '') : '';
        }

        function bukaModalEdit(id, nama, tahun, supplier, stok, status, tglBeli, hargaBeli, pembeli, tglJual, hargaJual) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editTahun').value = tahun;
            document.getElementById('editSupplier').value = supplier;
            document.getElementById('editStok').value = stok;
            document.getElementById('editStatus').value = status;
            document.getElementById('editTanggal').value = tglBeli;
            
            let formattedHargaBeli = new Intl.NumberFormat('id-ID').format(hargaBeli);
            document.getElementById('editDisplayHarga').value = formattedHargaBeli;
            document.getElementById('editRealHarga').value = hargaBeli;

            document.getElementById('editPembeli').value = pembeli || '';
            document.getElementById('editTglJual').value = tglJual || '';
            
            if(hargaJual && hargaJual > 0) {
                let formattedHargaJual = new Intl.NumberFormat('id-ID').format(hargaJual);
                document.getElementById('editDisplayHargaJual').value = formattedHargaJual;
                document.getElementById('editRealHargaJual').value = hargaJual;
            } else {
                document.getElementById('editDisplayHargaJual').value = '';
                document.getElementById('editRealHargaJual').value = '';
            }

            document.getElementById('salesFields').classList.add('hidden');
            if(status === 'sold') {
                document.getElementById('salesFields').classList.remove('hidden');
            }
            document.getElementById('modalEdit').classList.remove('hidden');
        }

        function tutupModalEdit() { 
            document.getElementById('modalEdit').classList.add('hidden'); 
        }

        function bukaModalDetail(nama, tahun, supplier, stok, status, tgl, harga, pembeli, profit) {
            document.getElementById('detailNama').value = nama;
            document.getElementById('detailTahun').value = tahun;
            document.getElementById('detailSupplier').value = supplier;
            document.getElementById('detailStok').value = stok + ' Gr';
            document.getElementById('detailTgl').value = tgl;
            document.getElementById('detailHarga').value = harga; 
            
            const badge = document.getElementById('detailBadge');
            const infoTerjual = document.getElementById('infoTerjual');
            const infoRiwayat = document.getElementById('infoRiwayat');

            if(status === 'available') {
                badge.innerText = 'Tersedia';
                badge.className = 'px-4 py-1.5 text-xs font-bold uppercase rounded-full border tracking-wide bg-emerald-100 text-emerald-700 border-emerald-200';
                infoTerjual.classList.add('hidden');
                infoRiwayat.classList.add('hidden');
            } else {
                badge.innerText = 'Terjual';
                badge.className = 'px-4 py-1.5 text-xs font-bold uppercase rounded-full border tracking-wide bg-red-100 text-red-700 border-red-200';
                document.getElementById('valPembeli').innerText = pembeli;
                document.getElementById('valProfit').innerText = '+ Rp ' + profit;
                infoTerjual.classList.remove('hidden');
                infoRiwayat.classList.remove('hidden');
            }
            document.getElementById('modalDetail').classList.remove('hidden');
        }
        function tutupModalDetail() { document.getElementById('modalDetail').classList.add('hidden'); }

        function bukaModalHapus(id, nama) {
            idHapusGlobal = id;
            document.getElementById('namaBarangHapus').innerText = nama;
            document.getElementById('modalHapus').classList.remove('hidden');
        } 
        function tutupModalHapus() { document.getElementById('modalHapus').classList.add('hidden'); }
        
        function eksekusiHapus() { 
            window.location.href = 'proses_stok.php?act=hapus&id=' + idHapusGlobal;
        }
    </script>
</body>
</html>