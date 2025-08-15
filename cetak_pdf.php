<?php
require('lib/fpdf.php');

// Membaca data pembayaran
$payments_file = 'data/payments.json';
$payments_data = json_decode(file_get_contents($payments_file), true);

// Cek apakah ada parameter siswa
if (!isset($_GET['student_id'])) {
    die("ID siswa tidak ditemukan.");
}

$student_id = $_GET['student_id'];

// Filter data untuk siswa tertentu
$student_payments = array_filter($payments_data, function ($payment) use ($student_id) {
    return $payment['student_id'] === $student_id;
});

if (empty($student_payments)) {
    die("Tidak ada data pembayaran untuk siswa dengan ID: $student_id.");
}

// Membuat instance FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Header
$pdf->Cell(0, 10, 'Laporan Transaksi Pembayaran', 0, 1, 'C');
$pdf->Ln(5);

// Informasi Siswa
$first_payment = reset($student_payments);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 10, 'Nama Siswa: ' . $first_payment['student_id'], 0, 1);
$pdf->Cell(40, 10, 'Kelas: ' . $first_payment['class_id'], 0, 1);
$pdf->Cell(40, 10, 'Tahun Ajaran: ' . $first_payment['year_id'], 0, 1);
$pdf->Ln(5);

// Tabel Transaksi
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10, 'No', 1);
$pdf->Cell(80, 10, 'Jenis Pembayaran', 1);
$pdf->Cell(40, 10, 'Tanggal Pembayaran', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
foreach ($student_payments as $index => $payment) {
    $pdf->Cell(10, 10, $index + 1, 1);
    $pdf->Cell(80, 10, $payment['price_id'], 1);
    $pdf->Cell(40, 10, $payment['payment_date'], 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('D', 'Laporan Pembayaran ' . $student_id . '.pdf');
?>
