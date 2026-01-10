<?php
include 'cek_session.php';
include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');
$hariIni = date('Y-m-d'); 

function format_singkat($angka) {
    if ($angka >= 1000000000) return number_format($angka / 1000000000, 2, ',', '.') . ' M'; 
    elseif ($angka >= 1000000) return number_format($angka / 1000000, 1, ',', '.') . ' Jt'; 
    elseif ($angka >= 1000) return number_format($angka / 1000, 0, ',', '.') . ' Rb'; 
    return number_format($angka, 0, ',', '.');
}

function getFontSize($angka) {
    if ($angka >= 1000000000) return 'text-xs md:text-base';
    elseif ($angka >= 100000000) return 'text-sm md:text-lg';
    elseif ($angka >= 10000000) return 'text-base md:text-lg';
    return 'text-lg md:text-lg';
}
$qProfitToday = mysqli_query($conn, "SELECT SUM(profit) as total FROM transactions WHERE DATE(tanggal_jual) = '$hariIni'");
$profitKotorToday = mysqli_fetch_assoc($qProfitToday)['total'] ?? 0;

$qProfitWeek = mysqli_query($conn, "SELECT SUM(profit) as total FROM transactions WHERE YEARWEEK(tanggal_jual, 1) = YEARWEEK('$hariIni', 1)");
$profitKotorWeek = mysqli_fetch_assoc($qProfitWeek)['total'] ?? 0;

$qProfitMonth = mysqli_query($conn, "SELECT SUM(profit) as total FROM transactions WHERE MONTH(tanggal_jual) = MONTH('$hariIni') AND YEAR(tanggal_jual) = YEAR('$hariIni')");
$profitKotorMonth = mysqli_fetch_assoc($qProfitMonth)['total'] ?? 0;


$qBebanToday = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM financial_records WHERE (kategori='prive' OR kategori='piutang' OR kategori='cicilan') AND tanggal = '$hariIni'");
$bebanToday = mysqli_fetch_assoc($qBebanToday)['total'] ?? 0;

$qBebanWeek = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM financial_records WHERE (kategori='prive' OR kategori='piutang' OR kategori='cicilan') AND YEARWEEK(tanggal, 1) = YEARWEEK('$hariIni', 1)");
$bebanWeek = mysqli_fetch_assoc($qBebanWeek)['total'] ?? 0;

$qBebanMonth = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM financial_records WHERE (kategori='prive' OR kategori='piutang' OR kategori='cicilan') AND MONTH(tanggal) = MONTH('$hariIni') AND YEAR(tanggal) = YEAR('$hariIni')");
$bebanMonth = mysqli_fetch_assoc($qBebanMonth)['total'] ?? 0;


$profitToday = $profitKotorToday - $bebanToday;
$profitWeek  = $profitKotorWeek - $bebanWeek;
$profitMonth = $profitKotorMonth - $bebanMonth;

$queryStock = mysqli_query($conn, "SELECT SUM(berat) as total_berat FROM stocks WHERE status = 'available'");
$rowStock = mysqli_fetch_assoc($queryStock);
$stockGudang = $rowStock['total_berat'] ?? 0;

$queryTopBuyers = mysqli_query($conn, "SELECT t.nama_pembeli, 
                                              SUM(t.profit) as total_profit,
                                              SUM(s.berat) as total_berat
                                       FROM transactions t
                                       JOIN stocks s ON t.stock_id = s.id
                                       WHERE MONTH(t.tanggal_jual) = MONTH('$hariIni') 
                                       AND YEAR(t.tanggal_jual) = YEAR('$hariIni')
                                       GROUP BY t.nama_pembeli 
                                       ORDER BY total_profit DESC 
                                       LIMIT 3");

$queryRecent = mysqli_query($conn, "SELECT t.*, s.nama_barang, s.supplier, s.berat 
                                    FROM transactions t 
                                    JOIN stocks s ON t.stock_id = s.id 
                                    ORDER BY t.tanggal_jual DESC, t.id DESC 
                                    LIMIT 5");

$array_hari = ['Sunday'=>'Minggu', 'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'];
$array_bulan = [1=>'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$hari_inggris = date('l'); 
$bulan_angka = date('n');
$hari_indo = $array_hari[$hari_inggris]; 
$bulan_indo = $array_bulan[$bulan_angka]; 
$tanggal_angka = date('d');
$tahun_angka = date('Y');

$tanggal_lengkap_indo = "$hari_indo, $tanggal_angka $bulan_indo $tahun_angka"; 
$bulan_tahun_indo = "$bulan_indo $tahun_angka"; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SS Gold</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        [title]:hover::after {
            content: attr(title);
            position: absolute;
            top: -100%;
            left: 0;
            background: #333;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }
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
                <a href="index.php" class="flex items-center px-4 py-3 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-semibold">Dashboard</a>
                <a href="stok.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Stok</a>
                <a href="transaksi.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Riwayat Transaksi</a>
                <a href="laporan.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">
                    Laporan Keuangan
                </a>
            </nav>
            <div class="p-4 border-t text-xs text-gray-400 text-center">SS Gold &copy; <?php echo date('Y'); ?></div>
        </aside>

        <main class="flex-1 overflow-y-auto p-4 md:p-8"> 
            <header class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="p-2 -ml-2 text-gray-600 rounded-md hover:bg-gray-200 focus:outline-none md:hidden">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900">Overview Performa</h2>
                        <p class="text-sm text-gray-500 mt-1 hidden md:block">Ringkasan profit dan stok emas terkini.</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 px-3 py-2 rounded-lg text-xs md:text-sm font-medium text-gray-600 shadow-sm flex items-center gap-2">
                    <span class="relative flex h-2 w-2 md:h-3 md:w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 md:h-3 md:w-3 bg-emerald-500"></span>
                    </span>
                    <span id="dateMobile" class="block md:hidden">Loading...</span>
                    <span id="dateDesktop" class="hidden md:block">Loading...</span>
                </div>
            </header>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-8">
                
                <a href="transaksi.php" class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-teal-600 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group cursor-pointer">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Profit Hari Ini</p>
                            <h3 class="<?php echo getFontSize($profitToday); ?> font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($profitToday, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-700 text-white shadow-md shadow-teal-200/50 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-teal-700">
                        <span class="bg-teal-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap"><?php echo $tanggal_lengkap_indo; ?></span>
                    </div>
                </a>

                <a href="transaksi.php" class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-sky-500 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group cursor-pointer">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Minggu Ini</p>
                            <h3 class="<?php echo getFontSize($profitWeek); ?> font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($profitWeek, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 text-white shadow-md shadow-sky-200/50 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-sky-700">
                        <span class="bg-sky-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap">Senin-Minggu</span>
                    </div>
                </a>

                <a href="transaksi.php" class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-blue-700 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group cursor-pointer">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Bulan Ini</p>
                            <h3 class="<?php echo getFontSize($profitMonth); ?> font-bold text-gray-800 mt-1 truncate">
                                Rp <?php echo number_format($profitMonth, 0, ',', '.'); ?>
                            </h3>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-blue-600 to-blue-800 text-white shadow-md shadow-blue-200/50 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-blue-800">
                        <span class="bg-blue-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap"><?php echo $bulan_tahun_indo; ?></span>
                    </div>
                </a>

                <a href="stok.php" class="bg-white p-4 md:p-6 rounded-xl shadow-sm border-t-4 border-orange-500 flex flex-col justify-between relative overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg group cursor-pointer">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-[10px] md:text-sm text-gray-500 font-semibold uppercase tracking-wider">Stok Gudang</p>
                            <h3 class="text-lg md:text-lg font-bold text-gray-800 mt-1 truncate"><?php echo $stockGudang; ?> <span class="text-xs md:text-lg font-normal text-gray-500">gr</span></h3>
                        </div>
                        <div class="p-2 rounded-lg bg-gradient-to-br from-orange-400 to-amber-600 text-white shadow-md shadow-orange-200/50 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-center text-[10px] md:text-xs font-medium text-orange-700">
                        <span class="bg-orange-50 px-1.5 py-0.5 md:px-2 md:py-1 rounded-full whitespace-nowrap">Siap Jual</span>
                    </div>
                </a>
            </div>

            <div class="mb-8 w-full">
                <button onclick="toggleCustomCheck()" class="w-full bg-white p-4 md:p-5 rounded-xl shadow-sm border border-gray-200 flex justify-between items-center hover:bg-gray-50 transition duration-200 group text-left">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 3.666A5.976 5.976 0 0112 16.5a5.976 5.976 0 01-4.747-2.327M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm md:text-base">Cek Profit (Custom)</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Filter laporan berdasarkan rentang tanggal pilihan Anda.</p>
                        </div>
                    </div>
                    <svg id="arrowIcon" class="w-5 h-5 text-gray-400 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div id="customCheckPanel" class="hidden w-full bg-white border-x border-b border-gray-200 rounded-b-xl shadow-sm p-4 md:p-6 transition-all">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full flex-1">
                            <label class="block text-xs font-bold text-gray-600 mb-2 ml-1">Dari Tanggal</label>
                            <input type="date" id="startDate" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition text-gray-600">
                        </div>
                        <div class="w-full flex-1">
                            <label class="block text-xs font-bold text-gray-600 mb-2 ml-1">Sampai Tanggal</label>
                            <input type="date" id="endDate" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-emerald-500 transition text-gray-600">
                        </div>
                        <div class="w-full md:w-auto">
                            <button onclick="fetchProfit()" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-8 rounded-lg shadow-md transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Cek Data
                            </button>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-gray-500 text-xs uppercase font-semibold">Total Profit</span>
                            <div class="font-bold text-emerald-600 text-lg md:text-xl mt-1" id="resProfit">Rp 0</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-gray-500 text-xs uppercase font-semibold">Jumlah Transaksi</span>
                            <div class="font-bold text-gray-800 text-lg md:text-xl mt-1" id="resTrans">0</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-gray-500 text-xs uppercase font-semibold">Emas Terjual</span>
                            <div class="font-bold text-blue-600 text-lg md:text-xl mt-1" id="resBerat">0 gr</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-pink-50">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-pink-100 rounded-md text-pink-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm">Top Pembeli Bulan Ini</h3>
                    </div>
                    <span class="text-xs font-medium text-pink-600"><?php echo $bulan_tahun_indo; ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap text-sm">
                        <thead class="bg-white text-gray-500 uppercase text-[10px] border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold">No</th>
                                <th class="px-6 py-3 font-semibold">Nama Pembeli</th>
                                <th class="px-6 py-3 font-semibold text-center">Total (Gr)</th>
                                <th class="px-6 py-3 font-semibold text-right text-emerald-600">Total Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php 
                            $noBuyer = 1;
                            if(mysqli_num_rows($queryTopBuyers) > 0) {
                                while($rowBuyer = mysqli_fetch_assoc($queryTopBuyers)) { 
                                    $beratBersih = $rowBuyer['total_berat'] + 0; 
                            ?>
                            <tr class="hover:bg-pink-50/30 transition-colors">
                                <td class="px-6 py-3 text-gray-400 font-medium"><?= $noBuyer++; ?></td>
                                <td class="px-6 py-3 font-bold text-gray-800"><?= $rowBuyer['nama_pembeli']; ?></td>
                                <td class="px-6 py-3 text-center font-medium text-gray-700 bg-gray-50/50">
                                    <?= $beratBersih; ?> gr
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-emerald-600">+ Rp <?= number_format($rowBuyer['total_profit'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php 
                                } 
                            } else {
                                echo '<tr><td colspan="4" class="px-6 py-6 text-center text-gray-400 italic text-xs">Belum ada penjualan bulan ini.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-900">Transaksi Terbaru</h3>
                    <a href="transaksi.php" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Lihat Semua ></a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead class="bg-white text-gray-500 uppercase text-xs border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">No</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Tgl Jual</th> 
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Nama Barang</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Supplier</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50">Pembeli</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-center">Jml (Gr)</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Harga Jual (Gr)</th>
                                <th class="px-6 py-3 font-semibold bg-gray-50/50 text-right">Total Jual</th>
                                <th class="px-6 py-3 font-bold bg-emerald-50 text-emerald-700 text-right">Profit</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($queryRecent)) { 
                                $berat = $row['berat'];
                                $totalJual = $row['harga_jual_total'];
                                $hargaPerGram = ($berat > 0) ? ($totalJual / $berat) : 0;
                            ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 text-gray-500 text-sm"><?= $no++; ?></td>
                                <td class="px-6 py-4 text-gray-900 font-medium text-sm"><?= date('d M Y', strtotime($row['tanggal_jual'])); ?></td>
                                <td class="px-6 py-4 font-bold text-gray-900 text-sm"><?= $row['nama_barang']; ?></td>
                                <td class="px-6 py-4 text-gray-600 text-sm"><?= $row['supplier']; ?></td>
                                <td class="px-6 py-4 text-gray-600 text-sm"><?= $row['nama_pembeli']; ?></td>
                                <td class="px-6 py-4 text-center font-medium bg-gray-50 rounded-lg text-gray-800 text-sm"><?= number_format($berat, 2, ',', '.'); ?></td> 
                                <td class="px-6 py-4 text-right text-gray-500 text-sm"><?= number_format($hargaPerGram, 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900 text-sm"><?= number_format($totalJual, 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 text-right text-emerald-600 font-bold bg-emerald-50/50 text-sm">+ <?= number_format($row['profit'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                    <span class="text-xs text-gray-500 font-medium">Menampilkan 5 transaksi profit tertinggi</span>
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

        function toggleCustomCheck() {
            const panel = document.getElementById('customCheckPanel');
            const arrow = document.getElementById('arrowIcon');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden'); 
                arrow.classList.add('rotate-180');
            } else {
                panel.classList.add('hidden');   
                arrow.classList.remove('rotate-180');
            }
        }

        function fetchProfit() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            if(!start || !end) {
                alert('Silakan pilih rentang tanggal awal dan akhir.');
                return;
            }

            document.getElementById('resProfit').innerText = "Loading...";

            const formData = new FormData();
            formData.append('start_date', start);
            formData.append('end_date', end);

            fetch('api_profit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('resProfit').innerText = "Rp " + data.profit;
                    document.getElementById('resTrans').innerText = data.transaksi;
                    document.getElementById('resBerat').innerText = data.berat + " gr";
                } else {
                    alert('Gagal mengambil data.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem.');
            });
        }

        function updateDate() {
            const dateMobile = document.getElementById('dateMobile');
            const dateDesktop = document.getElementById('dateDesktop');
            const now = new Date();
            
            const optionsDesktop = { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' };
            const formattedDesktop = new Intl.DateTimeFormat('id-ID', optionsDesktop).format(now);
            
            const optionsMobile = { day: '2-digit', month: 'short', year: 'numeric' };
            const formattedMobile = new Intl.DateTimeFormat('id-ID', optionsMobile).format(now);

            if(dateDesktop) dateDesktop.innerText = formattedDesktop;
            if(dateMobile) dateMobile.innerText = formattedMobile;
        }
        updateDate();
    </script>
</body>
</html>