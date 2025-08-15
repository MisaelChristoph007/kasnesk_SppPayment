<?php
// File tempat data siswa disimpan
$file = 'data/students.json';

// Memuat data siswa dari file JSON
if (file_exists($file)) {
    $students = json_decode(file_get_contents($file), true) ?? [];
} else {
    $students = [];
}

// Tangani form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cegah output sebelum header
    ob_start();

    $action = $_POST['action'] ?? null;
    $index = $_POST['index'] ?? null;

    if ($action === 'create') {
        $students[] = [
            'name' => $_POST['name'] ?? '',
            'class' => $_POST['class'] ?? '',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'alamat' => $_POST['alamat'] ?? '',
            'id_spp' => $_POST['id_spp'] ?? '',
        ];
    } elseif ($action === 'edit' && is_numeric($index)) {
        $students[$index] = [
            'name' => $_POST['name'] ?? '',
            'class' => $_POST['class'] ?? '',
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'alamat' => $_POST['alamat'] ?? '',
            'id_spp' => $_POST['id_spp'] ?? '',
        ];
    } elseif ($action === 'delete' && is_numeric($index)) {
        unset($students[$index]);
        $students = array_values($students); // Reset indeks array
    }

    // Simpan data siswa ke file JSON
    file_put_contents($file, json_encode($students, JSON_PRETTY_PRINT));

    ob_end_clean(); // Pastikan tidak ada output sebelum header
    header('Location: manage_students.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
        <!-- Tombol untuk membuka dan menutup sidebar -->
        <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-expanded="false" aria-controls="sidebar">
            Toggle Sidebar
        </button>

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

<!-- Main Content -->
<div class="container mt-4">
    <h1 class="mb-3">Manajemen Siswa</h1>

    <!-- Form Tambah/Edit Siswa -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="hidden" name="action" value="create" id="action">
                <input type="hidden" name="index" id="index">
                <label for="name" class="form-label">Nama</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="col-md-4">
                <label for="class" class="form-label">Kelas</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
        <div class="col-md-4">
                <label for="class" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <label for="password" class="form-label">Password</label>
                <input type="text" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-4">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" required>
            </div>
            <div class="col-md-4">
                <label for="id_spp" class="form-label">ID SPP</label>
                <input type="text" class="form-control" id="id_spp" name="id_spp" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Simpan</button>
    </form>

    <!-- Tabel Data Siswa -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Username</th>
            <th>Password</th>
            <th>Alamat</th>
            <th>ID SPP</th>
            <th>Aksi</th>
        </tr>
        </thead>
    <tbody>
    <?php if (!empty($students)) { ?>
        <?php foreach ($students as $index => $student) { ?>
            <tr>
                <td><?php echo htmlspecialchars($student['name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($student['class'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($student['username'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($student['password'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($student['alamat'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($student['id_spp'] ?? ''); ?></td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm" onclick="editStudent(<?php echo $index; ?>)">Edit</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="7" class="text-center">Tidak ada data siswa.</td>
        </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

            <script>
                function editStudent(index) {
                    const student = <?php echo json_encode($students); ?>[index] || {};
                    document.getElementById('action').value = 'edit';
                    document.getElementById('index').value = index;
                    document.getElementById('name').value = student.name || '';
                    document.getElementById('class').value = student.class || '';
                    document.getElementById('username').value = student.username || '';
                    document.getElementById('password').value = student.password || '';
                    document.getElementById('alamat').value = student.alamat || '';
                    document.getElementById('id_spp').value = student.id_spp || '';
                }
            </script>

<script> src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
