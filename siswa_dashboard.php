<?php
session_start();

// Cek apakah sesi sudah ada
if (!isset($_SESSION['student_username'])) {
    header("Location: index.php");
    exit();
}

// Pastikan file data siswa sudah tersedia
$studentFile = 'data/students.json';
if (!file_exists($studentFile)) {
    die("File data siswa tidak ditemukan.");
}
$students = json_decode(file_get_contents($studentFile), true);

// Ambil data siswa berdasarkan username yang login
$student = null;
foreach ($students as $s) {
    if ($s['username'] === $_SESSION['student_username']) {
        $student = $s;
        break;
    }
}

// Pastikan data siswa ditemukan
if (!$student) {
    die("Data siswa tidak ditemukan.");
}

// Atur path gambar profil
$profilePicture = !empty($student['profile_picture']) ? "uploads/{$student['profile_picture']}" : "images/default-profile.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
            <h1 id="greeting"></h1>
            <script>
                function updateGreeting() {
                    const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                    const now = new Date();
                    const dayName = days[now.getDay()];
                    const time = now.toLocaleTimeString();

                    const greeting = `Halo <?php echo htmlspecialchars($student['name'] ?? 'Siswa'); ?>! Selamat hari ${dayName} - ${time}`;
                    document.getElementById("greeting").textContent = greeting;
                }

                setInterval(updateGreeting, 1000);
                updateGreeting();
            </script>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Your Biodata</h5>
                            <p>Your class: <?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></p>
                            <p>Your ID SPP: <?php echo htmlspecialchars($student['id_spp'] ?? 'N/A'); ?></p>
                            <a href="profile.php" class="btn btn-primary">Go to Profile</a>
                            <a href="cetak_pdf.php?student_id=<?php echo urlencode($student['id'] ?? ''); ?>" 
                               class="btn btn-secondary">Check Status Pembayaran</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>