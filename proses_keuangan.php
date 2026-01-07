<?php
include 'koneksi.php';

$act = $_GET['act'];

if ($act == 'tambah') {
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];

    $query = "INSERT INTO financial_records (nama, kategori, jumlah, tanggal, keterangan) 
              VALUES ('$nama', '$kategori', '$jumlah', '$tanggal', '$keterangan')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: laporan.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

elseif ($act == 'update') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];

    $query = "UPDATE financial_records SET 
              nama='$nama', kategori='$kategori', jumlah='$jumlah', 
              tanggal='$tanggal', keterangan='$keterangan' 
              WHERE id='$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: laporan.php?status=updated");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

elseif ($act == 'hapus') {
    $id = $_GET['id'];
    $query = "DELETE FROM financial_records WHERE id='$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: laporan.php?status=deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>