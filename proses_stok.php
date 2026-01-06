<?php
include 'koneksi.php';

$act = $_GET['act'];

if ($act == 'tambah') {
    $nama = $_POST['nama_barang'];
    $tahun = $_POST['tahun_terbit']; 
    $supplier = $_POST['supplier'];
    $berat = $_POST['berat'];
    $status = $_POST['status'];
    $tgl_beli = $_POST['tanggal_beli'];
    $harga_beli = $_POST['harga_beli'];

    $query = "INSERT INTO stocks (nama_barang, tahun_terbit, supplier, berat, status, tanggal_beli, harga_beli_total) 
              VALUES ('$nama', '$tahun', '$supplier', '$berat', '$status', '$tgl_beli', '$harga_beli')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: stok.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

elseif ($act == 'update') {
    $id = $_POST['id'];
    $nama = $_POST['nama_barang'];
    $tahun = $_POST['tahun_terbit']; // BARU
    $supplier = $_POST['supplier'];
    $berat = $_POST['berat'];
    $status = $_POST['status'];
    $tgl_beli = $_POST['tanggal_beli'];
    $harga_beli = $_POST['harga_beli'];

    $queryStock = "UPDATE stocks SET 
                    nama_barang='$nama', 
                    tahun_terbit='$tahun', 
                    supplier='$supplier', 
                    berat='$berat', 
                    status='$status', 
                    tanggal_beli='$tgl_beli', 
                    harga_beli_total='$harga_beli' 
                   WHERE id='$id'";
    
    if (mysqli_query($conn, $queryStock)) {
        if ($status == 'sold') {
            $nama_pembeli = $_POST['nama_pembeli'];
            $tgl_jual = $_POST['tanggal_jual'];
            $harga_jual = $_POST['harga_jual'];
            $profit = $harga_jual - $harga_beli;

            $cekTrans = mysqli_query($conn, "SELECT id FROM transactions WHERE stock_id='$id'");
            if (mysqli_num_rows($cekTrans) > 0) {
                $queryTrans = "UPDATE transactions SET nama_pembeli='$nama_pembeli', tanggal_jual='$tgl_jual', harga_jual_total='$harga_jual', profit='$profit' WHERE stock_id='$id'";
            } else {
                $queryTrans = "INSERT INTO transactions (stock_id, nama_pembeli, tanggal_jual, harga_jual_total, profit) VALUES ('$id', '$nama_pembeli', '$tgl_jual', '$harga_jual', '$profit')";
            }
            mysqli_query($conn, $queryTrans);
        }
        header("Location: stok.php?status=updated");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

elseif ($act == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM stocks WHERE id='$id'");
    header("Location: stok.php?status=deleted");
}
?>