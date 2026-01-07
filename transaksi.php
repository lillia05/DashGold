<?php
include 'koneksi.php';

$where = "WHERE 1=1";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (t.nama_pembeli LIKE '%$search%' OR s.nama_barang LIKE '%$search%')";
}

if (isset($_GET['dari']) && !empty($_GET['dari']) && isset($_GET['sampai']) && !empty($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    $where .= " AND (t.tanggal_jual BETWEEN '$dari' AND '$sampai')";
}

$query = "SELECT t.*, s.nama_barang, s.supplier, s.berat, s.tanggal_beli, s.harga_beli_total 
          FROM transactions t 
          JOIN stocks s ON t.stock_id = s.id 
          $where 
          ORDER BY t.tanggal_jual DESC";

$result = mysqli_query($conn, $query);
$totalData = mysqli_num_rows($result);

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (t.nama_pembeli LIKE '%$search%' OR s.nama_barang LIKE '%$search%')";
}

if (isset($_GET['dari']) && !empty($_GET['dari']) && isset($_GET['sampai']) && !empty($_GET['sampai'])) {
    $dari = $_GET['dari'];
    $sampai = $_GET['sampai'];
    $where .= " AND (t.tanggal_jual BETWEEN '$dari' AND '$sampai')";
}

$queryCount = "SELECT COUNT(*) as total FROM transactions t JOIN stocks s ON t.stock_id = s.id $where";
$resultCount = mysqli_query($conn, $queryCount);
$rowCount = mysqli_fetch_assoc($resultCount);
$totalData = $rowCount['total'];
$totalPages = ceil($totalData / $limit);

$query = "SELECT t.*, s.nama_barang, s.supplier, s.berat, s.tanggal_beli, s.harga_beli_total 
          FROM transactions t 
          JOIN stocks s ON t.stock_id = s.id 
          $where 
          ORDER BY t.tanggal_jual DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

$firstItem = ($totalData > 0) ? ($offset + 1) : 0;
$lastItem = ($offset + $limit < $totalData) ? ($offset + $limit) : $totalData;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - SS Gold</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
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
                <a href="stok.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Stok</a>
                <a href="transaksi.php" class="flex items-center px-4 py-3 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-semibold">Riwayat Transaksi</a>
                <a href="laporan.html" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">
                    Laporan Keuangan
                </a>
            </nav>
            <div class="p-4 border-t text-xs text-gray-400 text-center">SS Gold &copy; <?php echo date('Y'); ?></div>
        </aside>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
            
            <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="p-2 -ml-2 text-gray-600 rounded-md md:hidden">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Riwayat Penjualan</h2>
                        <p class="text-sm text-gray-500 mt-1">Laporan lengkap transaksi dan margin keuntungan.</p>
                    </div>
                </div>
            </header>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
                <form method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                    
                    <div class="relative flex-1 w-full">
                        <input type="text" name="search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>" placeholder="Cari Pembeli atau Nama Barang..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                        <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                        <div class="relative w-full md:w-40">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="date" name="dari" value="<?= isset($_GET['dari']) ? $_GET['dari'] : '' ?>" class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-10 p-2.5 outline-none" placeholder="Dari Tanggal">
                        </div>

                        <div class="relative w-full md:w-40">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="date" name="sampai" value="<?= isset($_GET['sampai']) ? $_GET['sampai'] : '' ?>" class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-10 p-2.5 outline-none" placeholder="Sampai Tanggal">
                        </div>

                        <button type="submit" class="w-full md:w-auto bg-teal-600 hover:bg-teal-700 text-white font-medium py-2.5 px-6 rounded-lg text-sm transition shadow-sm">
                            Filter
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
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Tanggal</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Nama Barang</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Supplier</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Pembeli</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Jml (Gr)</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Harga Beli</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Harga Jual</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Total Jual</th>
                                <th class="px-6 py-3 font-bold text-emerald-700 bg-emerald-50 text-right">Profit</th>
                            </tr>
                        </thead>
                        
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            if($totalData == 0) {
                                echo '<tr><td colspan="10" class="px-6 py-8 text-center text-gray-500 italic">Belum ada data transaksi.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($result)) { 
                                $berat = $row['berat'];
                                $hargaBeliPerGr = ($berat > 0) ? $row['harga_beli_total'] / $berat : 0;
                                $hargaJualPerGr = ($berat > 0) ? $row['harga_jual_total'] / $berat : 0;
                            ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 text-gray-500"><?= $no++; ?></td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase w-8">Beli:</span>
                                            <span class="text-xs text-gray-500 italic"><?= date('d M Y', strtotime($row['tanggal_beli'])); ?></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span class="text-[10px] font-bold text-emerald-600 uppercase w-8">Jual:</span>
                                            <span class="text-sm font-bold text-gray-800"><?= date('d M Y', strtotime($row['tanggal_jual'])); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900"><?= $row['nama_barang']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?= $row['supplier']; ?></td>
                                <td class="px-6 py-4 text-gray-700"><?= $row['nama_pembeli']; ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold"><?= $berat; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-500"><?= number_format($hargaBeliPerGr, 0, ',', '.'); ?> <span class="text-xs font-normal text-gray-400">/gr</span></td>
                                <td class="px-6 py-4 text-right font-medium text-gray-600"><?= number_format($hargaJualPerGr, 0, ',', '.'); ?> <span class="text-xs font-normal text-gray-400">/gr</span></td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900"><?= number_format($row['harga_jual_total'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 text-right bg-emerald-50/50">
                                    <span class="text-emerald-600 font-bold">+ <?= number_format($row['profit'], 0, ',', '.'); ?></span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-col md:flex-row items-center justify-between gap-4">
                <span class="text-xs text-gray-500">
                    Menampilkan <b><?= $firstItem; ?></b> - <b><?= $lastItem; ?></b> dari <b><?= $totalData; ?></b> transaksi
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

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>
</body>
</html>