<?php
include 'cek_session.php';
include 'koneksi.php';

$qProfit = mysqli_query($conn, "SELECT SUM(profit) as total FROM transactions");
$dProfit = mysqli_fetch_assoc($qProfit);
$totalProfit = $dProfit['total'] ?? 0;

$qFinance = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN kategori = 'modal' THEN jumlah ELSE 0 END) as total_modal_manual,
    SUM(CASE WHEN kategori = 'prive' THEN jumlah ELSE 0 END) as total_prive,
    SUM(CASE WHEN kategori = 'hutang' THEN jumlah ELSE 0 END) as total_hutang,
    SUM(CASE WHEN kategori = 'piutang' THEN jumlah ELSE 0 END) as total_piutang
    FROM financial_records");
$dFinance = mysqli_fetch_assoc($qFinance);

$modalManual = $dFinance['total_modal_manual'] ?? 0;
$totalPrive = $dFinance['total_prive'] ?? 0;
$totalHutang = $dFinance['total_hutang'] ?? 0;
$totalPiutang = $dFinance['total_piutang'] ?? 0;

$totalAsetBersih = ($modalManual + $totalProfit + $totalHutang) - ($totalPrive + $totalPiutang);

$whereClause = "";
if (isset($_GET['kategori']) && !empty($_GET['kategori']) && $_GET['kategori'] != 'semua') {
    $kategori = mysqli_real_escape_string($conn, $_GET['kategori']);
    $whereClause = "WHERE kategori = '$kategori'";
}

$queryRecords = mysqli_query($conn, "SELECT * FROM financial_records $whereClause ORDER BY tanggal DESC, created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - SS Gold</title>
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
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:flex">
            <div class="h-16 flex items-center justify-between md:justify-center px-4 border-b border-gray-100 relative">
                <div class="flex items-center gap-3">
                    <img src="logo.png" alt="Logo" class="h-10 w-auto object-contain">
                    <img src="namalogo.png" alt="Nama" class="h-8 w-auto object-contain">
                </div>
                <button onclick="toggleSidebar()" class="text-gray-500 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="index.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Dashboard</a>
                <a href="stok.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Stok</a>
                <a href="transaksi.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Riwayat Transaksi</a>
                <a href="laporan.php" class="flex items-center px-4 py-3 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-semibold">
                    Laporan Keuangan
                </a>
            </nav>
            <div class="p-4 border-t text-xs text-gray-400 text-center">
                Project KP BSI &copy; 2026
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
            
            <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="p-2 -ml-2 text-gray-600 rounded-md md:hidden">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h2>
                        <p class="text-sm text-gray-500 mt-1">Kelola Modal, Prive, Hutang, dan Piutang Usaha Anda.</p>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8">
                
                <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-blue-600 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group">
                    <div class="flex justify-between items-center mb-2">
                        <div class="overflow-hidden">
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Total Modal (Aset)</p>
                            <h3 class="text-lg md:text-2xl font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($totalAsetBersih, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-blue-100 text-blue-600 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-blue-600">
                        <span class="bg-blue-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap">Aset</span>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-orange-500 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group">
                    <div class="flex justify-between items-center mb-2">
                        <div class="overflow-hidden">
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Total Prive</p>
                            <h3 class="text-lg md:text-2xl font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($totalPrive, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-orange-100 text-orange-600 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-orange-600">
                        <span class="bg-orange-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap">Penarikan</span>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-red-600 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group">
                    <div class="flex justify-between items-center mb-2">
                        <div class="overflow-hidden">
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Total Hutang</p>
                            <h3 class="text-lg md:text-2xl font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($totalHutang, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-red-100 text-red-600 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-red-600">
                        <span class="bg-red-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap">Kewajiban</span>
                    </div>
                </div>

                <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-teal-600 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group">
                    <div class="flex justify-between items-center mb-2">
                        <div class="overflow-hidden">
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Total Piutang</p>
                            <h3 class="text-lg md:text-2xl font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($totalPiutang, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-teal-100 text-teal-600 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-teal-600">
                        <span class="bg-teal-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap">Hak Tagih</span>
                    </div>
                </div>
            </div>

            <div class="mb-8 flex justify-end">
                <button onclick="bukaModalKeuangan()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm shadow-emerald-200 transition flex items-center gap-2 text-sm w-full md:w-auto justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Input Keuangan</span>
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex flex-row justify-between items-center gap-4">
                    <h3 class="font-bold text-gray-900 text-sm md:text-base">Riwayat Keuangan</h3>
                    
                    <form method="GET" class="relative">
                        <select name="kategori" onchange="this.form.submit()" class="pl-3 pr-8 py-2 text-xs border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer shadow-sm">
                            <option value="semua" <?= (!isset($_GET['kategori']) || $_GET['kategori'] == 'semua') ? 'selected' : '' ?>>Semua Kategori</option>
                            <option value="modal" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'modal') ? 'selected' : '' ?>>Modal</option>
                            <option value="prive" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'prive') ? 'selected' : '' ?>>Prive</option>
                            <option value="hutang" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'hutang') ? 'selected' : '' ?>>Hutang</option>
                            <option value="piutang" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'piutang') ? 'selected' : '' ?>>Piutang</option>
                        </select>
                    </form>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-white text-gray-500 uppercase text-xs border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">No</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Tanggal</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Nama</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Kategori</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Keterangan</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Jumlah (Rp)</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($queryRecords)) { 
                                // Warna badge kategori
                                $badgeColor = "bg-gray-100 text-gray-700 border-gray-200";
                                if ($row['kategori'] == 'modal') $badgeColor = "bg-blue-100 text-blue-700 border-blue-200";
                                else if ($row['kategori'] == 'prive') $badgeColor = "bg-orange-100 text-orange-700 border-orange-200";
                                else if ($row['kategori'] == 'hutang') $badgeColor = "bg-red-100 text-red-700 border-red-200";
                                else if ($row['kategori'] == 'piutang') $badgeColor = "bg-teal-100 text-teal-700 border-teal-200";
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-3 text-gray-500"><?= $no++; ?></td>
                                <td class="px-6 py-3 font-medium text-gray-900"><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                <td class="px-6 py-3 font-medium text-gray-900"><?= $row['nama']; ?></td>
                                <td class="px-6 py-3">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wide <?= $badgeColor; ?>">
                                        <?= $row['kategori']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-600 truncate max-w-[200px]" title="<?= $row['keterangan']; ?>">
                                    <?= $row['keterangan']; ?>
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-gray-800"><?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="bukaModalEdit(
                                            '<?= $row['id']; ?>',
                                            '<?= addslashes($row['nama']); ?>',
                                            '<?= $row['kategori']; ?>',
                                            '<?= $row['jumlah']; ?>',
                                            '<?= $row['tanggal']; ?>',
                                            '<?= addslashes($row['keterangan']); ?>'
                                        )" class="text-amber-500 hover:text-amber-700 transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button onclick="bukaModalHapus('<?= $row['id']; ?>')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div id="modalKeuangan" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
                <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl transform transition-all">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-emerald-50 rounded-t-xl">
                        <h3 class="font-bold text-lg text-gray-800">Input Keuangan</h3>
                        <button onclick="tutupModalKeuangan()" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                    </div>

                    <form class="p-6" action="proses_keuangan.php?act=tambah" method="POST" onsubmit="prepareSubmitTambah()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                                <input name="nama" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Contoh: Bapak Budi" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi</label>
                                <select name="kategori" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="modal">Modal</option>
                                    <option value="prive">Prive</option>
                                    <option value="hutang">Hutang</option>
                                    <option value="piutang">Piutang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm font-bold">Rp</span>
                                    <input type="text" id="tambahJumlahDisplay" class="w-full border border-gray-300 rounded-lg pl-10 p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="0" required onkeyup="formatRupiah(this)">
                                    <input type="hidden" name="jumlah" id="tambahJumlahReal">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                <input name="tanggal" type="date" value="<?= date('Y-m-d'); ?>" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-emerald-500 outline-none" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="Keterangan transaksi..."></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                            <button type="button" onclick="tutupModalKeuangan()" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg border border-emerald-500 font-bold text-sm">Batal</button>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm hover:shadow-md transition text-sm">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="modalEditKeuangan" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
                <div class="bg-white rounded-xl w-full max-w-lg shadow-2xl transform transition-all">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-amber-50 rounded-t-xl">
                        <h3 class="font-bold text-lg text-gray-800">Edit Data Keuangan</h3>
                        <button onclick="tutupModalEdit()" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                    </div>

                    <form class="p-6" action="proses_keuangan.php?act=update" method="POST" onsubmit="prepareSubmitEdit()">
                        <input type="hidden" name="id" id="editId">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                                <input name="nama" id="editNama" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-amber-500 outline-none font-medium text-gray-700">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi</label>
                                <select name="kategori" id="editKategori" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="modal">Modal</option>
                                    <option value="prive">Prive</option>
                                    <option value="hutang">Hutang</option>
                                    <option value="piutang">Piutang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm font-bold">Rp</span>
                                    <input type="text" id="editJumlahDisplay" class="w-full border border-gray-300 rounded-lg pl-10 p-2.5 text-sm focus:ring-2 focus:ring-amber-500 outline-none font-medium text-gray-700" onkeyup="formatRupiah(this)">
                                    <input type="hidden" name="jumlah" id="editJumlahReal">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                <input name="tanggal" id="editTanggal" type="date" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm text-gray-600 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                                <textarea name="keterangan" id="editKeterangan" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                            <button type="button" onclick="tutupModalEdit()" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg border border-amber-300 font-bold text-sm">Batal</button>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm hover:shadow-md transition text-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="modalHapus" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50 backdrop-blur-sm p-4">
                <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl transform transition-all p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Riwayat Keuangan?</h3>
                    <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data ini? Data yang sudah dihapus tidak dapat dikembalikan.</p>
                    <div class="flex gap-3 justify-center">
                        <button onclick="tutupModalHapus()" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg border border-red-300 font-bold text-sm w-full transition">Batal</button>
                        <button onclick="konfirmasiHapus()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-sm hover:shadow-md w-full transition text-sm">Hapus</button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        let idHapus = null;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // --- FUNGSI FORMAT RUPIAH ---
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

            // Simpan ke hidden input yang sesuai
            if (element.id === 'tambahJumlahDisplay') {
                document.getElementById('tambahJumlahReal').value = rupiah.replace(/\./g, '');
            } else if (element.id === 'editJumlahDisplay') {
                document.getElementById('editJumlahReal').value = rupiah.replace(/\./g, '');
            }
        }

        // --- PREPARE SUBMIT ---
        function prepareSubmitTambah() {
            let displayVal = document.getElementById('tambahJumlahDisplay').value;
            document.getElementById('tambahJumlahReal').value = displayVal.replace(/\./g, '');
        }

        function prepareSubmitEdit() {
            let displayVal = document.getElementById('editJumlahDisplay').value;
            document.getElementById('editJumlahReal').value = displayVal.replace(/\./g, '');
        }

        // Modal Functions
        function bukaModalKeuangan() {
            document.getElementById('modalKeuangan').classList.remove('hidden');
        }
        function tutupModalKeuangan() {
            document.getElementById('modalKeuangan').classList.add('hidden');
        }

        function bukaModalEdit(id, nama, kategori, jumlah, tanggal, keterangan) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editKategori').value = kategori;
            
            // Format jumlah untuk tampilan edit
            let formattedJumlah = new Intl.NumberFormat('id-ID').format(jumlah);
            document.getElementById('editJumlahDisplay').value = formattedJumlah;
            document.getElementById('editJumlahReal').value = jumlah;
            
            document.getElementById('editTanggal').value = tanggal;
            document.getElementById('editKeterangan').value = keterangan;
            
            document.getElementById('modalEditKeuangan').classList.remove('hidden');
        }
        function tutupModalEdit() {
            document.getElementById('modalEditKeuangan').classList.add('hidden');
        }

        function bukaModalHapus(id) {
            idHapus = id;
            document.getElementById('modalHapus').classList.remove('hidden');
        }
        function tutupModalHapus() {
            document.getElementById('modalHapus').classList.add('hidden');
        }
        function konfirmasiHapus() {
            window.location.href = 'proses_keuangan.php?act=hapus&id=' + idHapus;
        }
    </script>
</body>
</html>