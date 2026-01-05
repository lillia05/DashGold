<?php
include 'koneksi.php';

header('Content-Type: application/json');

if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    $query = "SELECT 
                SUM(t.profit) as total_profit, 
                COUNT(t.id) as total_transaksi,
                SUM(s.berat) as total_berat_terjual
              FROM transactions t
              JOIN stocks s ON t.stock_id = s.id
              WHERE t.tanggal_jual BETWEEN '$start' AND '$end'";

    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    echo json_encode([
        'status' => 'success',
        'profit' => number_format($data['total_profit'] ?? 0, 0, ',', '.'),
        'transaksi' => $data['total_transaksi'] ?? 0,
        'berat' => ($data['total_berat_terjual'] ?? 0)
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tanggal tidak lengkap']);
}
?>