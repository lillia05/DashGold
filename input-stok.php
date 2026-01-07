<?php
include 'cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Stok - SS Gold</title>
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
                <a href="stok.php" class="flex items-center px-4 py-3 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-semibold">Stok</a>
                <a href="transaksi.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">Riwayat Transaksi</a>
                <a href="laporan.php" class="flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg text-sm transition">
                    Laporan Keuangan
                </a>
            </nav>
            <div class="p-4 border-t text-xs text-gray-400 text-center">SS Gold &copy; <?php echo date('Y'); ?></div>
        </aside>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
            <header class="flex items-center mb-6 md:hidden">
                <button onclick="toggleSidebar()" class="p-2 -ml-2 text-gray-600 rounded-md">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </header>

            <div class="w-full mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 text-center">
                        <h2 class="text-2xl font-bold text-gray-800">Form Input Stok Baru</h2>
                        <p class="text-sm text-gray-500 mt-1">Masukkan data lengkap untuk memperbarui inventaris gudang.</p>
                    </div>
                    
                    <form class="p-6 md:p-10" action="proses_stok.php?act=tambah" method="POST" onsubmit="prepareSubmit()">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            
                            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                                <input name="nama_barang" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all placeholder-gray-400" placeholder="Contoh: Emas Antam 10g" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Terbit</label>
                                <select name="tahun_terbit" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer" required>
                                    <option value="" disabled selected>Pilih Tahun</option>
                                    <?php 
                                    for($thn = 2020; $thn <= 2026; $thn++) {
                                        echo "<option value='$thn'>$thn</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                <input name="supplier" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none placeholder-gray-400" placeholder="Nama Supplier" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Stok (Gr)</label>
                                <input name="berat" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none placeholder-gray-400" placeholder="0" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Barang</label>
                                <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none cursor-pointer">
                                    <option value="available">Tersedia</option>
                                    <option value="sold">Terjual</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Beli</label>
                                <input name="tanggal_beli" type="date" value="<?php echo date('Y-m-d'); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-gray-600" required>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Beli (Total)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500 text-sm font-medium">Rp</span>
                                    
                                    <input type="text" id="displayHarga" class="w-full border border-gray-300 rounded-lg pl-12 px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none placeholder-gray-400" placeholder="0" onkeyup="formatRupiah(this)" required>
                                    
                                    <input type="hidden" name="harga_beli" id="realHarga">
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end gap-4 pt-10 mt-10 border-t border-gray-100">
                            <a href="stok.php" class="px-8 py-3 text-gray-600 hover:bg-gray-100 border border-emerald-700 rounded-lg text-sm font-medium transition">Batal</a>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-12 rounded-lg shadow-md hover:shadow-lg transition text-sm flex items-center gap-2">Simpan</button>
                        </div>
                    </form>
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

            document.getElementById('realHarga').value = rupiah.replace(/\./g, '');
        }

        function prepareSubmit() {
            let displayVal = document.getElementById('displayHarga').value;
            document.getElementById('realHarga').value = displayVal.replace(/\./g, '');
        }
    </script>
</body>
</html>