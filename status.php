<?php
session_start();

// Fungsi untuk membaca dan mendekode file JSON dengan penanganan error
function loadJsonData($filePath) {
    if (!file_exists($filePath)) {
        die("File tidak ditemukan: " . $filePath);
    }

    $jsonData = file_get_contents($filePath);
    if ($jsonData === false) {
        die("Gagal membaca file: " . $filePath);
    }

    $data = json_decode($jsonData, true);
    if ($data === null) {
        $jsonError = json_last_error_msg();
        $fileSnippet = substr($jsonData, 0, 200);
        if (strlen($jsonData) > 200) {
            $fileSnippet .= "...";
        }
        die("Data JSON tidak valid di " . $filePath . " (Error JSON: " . $jsonError . "). Potongan konten file: " . $fileSnippet);
    }

    return $data;
}

// Validasi sesi dan pemuatan data
if (!isset($_SESSION['student_username'])) {
    header("Location: index.php");
    exit();
}

$studentFile = 'data/students.json';
$paymentsFile = 'data/payments.json';

$students = loadJsonData($studentFile);
$payments = loadJsonData($paymentsFile);

$student = null;
foreach ($students as $s) {
    if (isset($s['username']) && $s['username'] === $_SESSION['student_username']) {
        $student = $s;
        break;
    }
}

if ($student === null) {
    die("Data siswa tidak ditemukan.");
}

// Filter data pembayaran berdasarkan nama siswa
$studentPayments = array_filter($payments, function ($payment) use ($student) {
    return isset($payment['student_id']) && $payment['student_id'] === $student['name'];
});

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
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img id="navProfilePicture" src="<?= !empty($student['image']) ? htmlspecialchars($student['image']) : 'default-profile.png'; ?>" alt="Profile" class="rounded-circle" width="30" height="30">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-center mt-3">Student Dashboard</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="status.php">
                            <i class="bi bi-calendar"></i> Payment Status
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-tag"></i> Profile
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
        <h1>History Pembayaran</h1>

        <?php if (empty($studentPayments)): ?>
            <p>Belum ada data pembayaran.</p>
        <?php else: ?>
            <h4>Nama Siswa: <?= htmlspecialchars($student['name']); ?></h4>
            <h5>Kelas: <?= htmlspecialchars($student['class']); ?></h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Pembayaran</th>
                        <th>Tanggal Pembayaran</th>
                        <th>Tahun Ajaran</th>
                        <th>Kelas</th>
                        <th>Harga</th>
                        <th>Pengunduhan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($studentPayments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($payment['payment_date'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($payment['year_id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($payment['class_id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($payment['price_id'] ?? ''); ?></td>
                            <td>
                                <a href="cetak_pdf.php?student_id=<?= urlencode($student['name']); ?>&payment_id=<?= urlencode($payment['id']); ?>" class="btn btn-success btn-sm" target="_blank">Unduh</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>