<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Ambil data dari file JSON
$json_file = 'data/payments.json'; // Lokasi file JSON
if (!file_exists($json_file)) {
    die("Error: File JSON tidak ditemukan.");
}

$json_data = file_get_contents($json_file);
$payments = json_decode($json_data, true);

// Jika JSON tidak valid atau kosong, buat array kosong
if (!is_array($payments)) {
    $payments = [];
}

// Statistik pembayaran
$total_income = 0;
$total_lunas = 0;
$total_belum_lunas = 0;

foreach ($payments as $payment) {
    $amount = isset($payment['amount']) ? (int) $payment['amount'] : 0;
    $status = isset($payment['status']) ? $payment['status'] : 'belum lunas';

    $total_income += $amount;
    if ($status === 'lunas') {
        $total_lunas++;
    } else {
        $total_belum_lunas++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <img src="https://e7.pngegg.com/pngimages/9/763/png-clipart-computer-icons-login-user-system-administrator-admin-desktop-wallpaper-megaphone-thumbnail.png" 
                             alt="Profile" class="rounded-circle" width="30" height="30">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky">
        <h4 class="text-center mt-3">Petugas Dashboard</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="manage_payments.php">
                    <i class="bi bi-person"></i> Manajemen Pembayaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="history_payments.php">
                    <i class="bi bi-wallet"></i> History Pembayaran
                </a>
            </li>
        </ul>
    </div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
    <h1 id="greeting"></h1>

    <script>
        function updateGreeting() {
            const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const now = new Date();
            const dayName = days[now.getDay()];
            const time = now.toLocaleTimeString();
            const greeting = `Halo Petugas! Selamat hari ${dayName} - ${time}`;
            document.getElementById("greeting").textContent = greeting;
        }
        setInterval(updateGreeting, 1000);
        updateGreeting();
    </script>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Uang Masuk</h5>
                    <h7 class="card-text">Rp <?= number_format($total_income, 0, ',', '.'); ?></h7>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-body">
                    <canvas id="paymentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const ctx = document.getElementById('paymentStatusChart').getContext('2d');
    const paymentStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Lunas', 'Belum Lunas'],
            datasets: [{
                label: 'Status Pembayaran',
                data: [<?= $total_lunas; ?>, <?= $total_belum_lunas; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            const data = tooltipItem.raw;
                            return `${tooltipItem.label}: ${data} siswa`;
                        }
                    }
                }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
