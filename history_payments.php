<?php
session_start();

// Cek apakah session username sudah ada, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Membaca data dari file payments.json
$payments_file = 'data/payments.json';
$payments_data = json_decode(file_get_contents($payments_file), true);

// Membaca data dari file JSON lainnya
$classes_file = 'data/classes.json';
$years_file = 'data/years.json';
$prices_file = 'data/prices.json';

$classes_data = json_decode(file_get_contents($classes_file), true);
$years_data = json_decode(file_get_contents($years_file), true);
$prices_data = json_decode(file_get_contents($prices_file), true);

// Validasi jika data tidak dapat dibaca
if ($payments_data === null) {
    die("Gagal membaca data pembayaran.");
}
if ($classes_data === null) {
    die("Gagal membaca data kelas.");
}
if ($years_data === null) {
    die("Gagal membaca data tahun ajaran.");
}
if ($prices_data === null) {
    die("Gagal membaca data jenis pembayaran.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom fixed-top">
        <div class="container-fluid">
        <marquee><h4 class="text-center mt-3">Aplikasi Pembayaran Kas Version 1.0</h4></marquee>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://e7.pngegg.com/pngimages/9/763/png-clipart-computer-icons-login-user-system-administrator-admin-desktop-wallpaper-megaphone-thumbnail.png" alt="Profile" class="rounded-circle" width="30" height="30">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
    <div class="row">
    <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky">
        <a class="text-center mt-3 active" href="admin_dashboard.php">
            <h4 class="text-center mt-3">Admin Dashboard</h4>
        </a>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="manage_students.php">
                    <i class="bi bi-person"></i>
                    Manajemen Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#" role="button" id="paymentDropdown" data-bs-toggle="collapse" data-bs-target="#paymentMenu" aria-expanded="false" aria-controls="paymentMenu">
                    <i class="bi bi-wallet"></i>
                    Manajemen Pembayaran
                </a>
                <div class="collapse <?php if (basename($_SERVER['PHP_SELF']) == 'manage_payments.php' || basename($_SERVER['PHP_SELF']) == 'history_payments.php') echo 'show'; ?>" id="paymentMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'manage_payments.php') echo 'active'; ?>" href="manage_payments.php">Kelola Pembayaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == 'history_payments.php') echo 'active'; ?>" href="history_payments.php">History Pembayaran</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_classes.php">
                    <i class="bi bi-building"></i>
                    Manajemen Kelas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_years.php">
                    <i class="bi bi-calendar"></i>
                    Manajemen Tahun Ajaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_prices.php">
                    <i class="bi bi-tag"></i>
                    Manajemen Harga
                </a>
            </li>
        </ul>
    </div>
</nav>
            </div>
</div>
        
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
                <h1>History Pembayaran</h1>

                <!-- Tabel History Pembayaran -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Jenis Pembayaran</th>
                                <th>Tanggal Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments_data as $index => $payment): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= htmlspecialchars($payment['student_id']); ?></td>
                                    <td><?= htmlspecialchars($classes_data[$payment['class_id']] ?? 'Kelas Tidak Ditemukan'); ?></td>
                                    <td><?= htmlspecialchars($years_data[$payment['year_id']] ?? 'Tahun Tidak Ditemukan'); ?></td>
                                    <td><?= htmlspecialchars($prices_data[$payment['price_id']] ?? 'Jenis Tidak Ditemukan'); ?></td>
                                    <td><?= htmlspecialchars($payment['payment_date']); ?></td>
                                    <td>
                                        <a href="cetak_pdf.php?student_id=<?= urlencode($payment['student_id']); ?>" 
                                           class="btn btn-success btn-sm">
                                            Cetak PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>