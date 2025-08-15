<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Path ke file JSON
$json_file = 'data/payments.json';

// Memastikan file JSON ada
if (!file_exists($json_file)) {
    die("File payments.json tidak ditemukan!");
}

// Membaca dan mengonversi data JSON ke array
$json_data = file_get_contents($json_file);
$payments = json_decode($json_data, true);

// Statistik pembayaran
$total_income = 0;
$total_lunas = 0;
$total_belum_lunas = 0;

foreach ($payments as $payment) {
    if (isset($payment['price_id'], $payment['payment_date'])) {
        // Pastikan price_id adalah angka
        $price = is_numeric($payment['price_id']) ? (float)$payment['price_id'] : 0;

        // Tambahkan ke total income
        $total_income += $price;

        // Hitung jumlah lunas dan belum lunas
        if (!empty($payment['payment_date'])) {
            $total_lunas++;
        } else {
            $total_belum_lunas++;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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

            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
                <h1 id="greeting"></h1>

                <script>
                    function updateGreeting() {
                        const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                        const now = new Date();
                        const dayName = days[now.getDay()];
                        const time = now.toLocaleTimeString();
                        const greeting = `Halo Admin! Selamat hari ${dayName} - ${time}`;
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
                                <h7 class="card-text">Rp <?= number_format($total_income, 0, ',', '.'); ?></h>
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
        </div>
    </div>

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
