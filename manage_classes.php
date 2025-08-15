<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Path to JSON file
$classesFile = 'data/classes.json';

// Load JSON data
$classes = file_exists($classesFile) ? json_decode(file_get_contents($classesFile), true) : [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = $_POST['id'] ?? null;
        $class_name = $_POST['class_name'] ?? null;

        if ($action == 'add') {
            $classes[] = [
                'id' => uniqid(),
                'class_name' => htmlspecialchars($class_name)
            ];
        } elseif ($action == 'edit') {
            foreach ($classes as &$class) {
                if (isset($class['id']) && $class['id'] === $id) {
                    $class['class_name'] = htmlspecialchars($class_name);
                    break;
                }
            }
        } elseif ($action == 'delete') {
            $classes = array_filter($classes, function($class) use ($id) {
                return isset($class['id']) && $class['id'] !== $id;
            });
        }

        file_put_contents($classesFile, json_encode($classes, JSON_PRETTY_PRINT));
        header("Location: manage_classes.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas</title>
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
                <h2 class="my-4">Manajemen Kelas</h2>

                <!-- Form Add/Edit Class -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="class-id" value="">
                    <div class="mb-3">
                        <label for="class_name" class="form-label">Nama Kelas</label>
                        <input type="text" class="form-control" id="class_name" name="class_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>

                <!-- Class Table -->
                <h3>Daftar Kelas</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['id'] ?? 'Tidak Diketahui'); ?></td>
                                <td><?php echo htmlspecialchars($class['class_name'] ?? 'Tidak Diketahui'); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo htmlspecialchars($class['id'] ?? ''); ?>" data-class_name="<?php echo htmlspecialchars($class['class_name'] ?? ''); ?>">Edit</button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($class['id'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const class_name = button.getAttribute('data-class_name');
                    document.querySelector('input[name="action"]').value = 'edit';
                    document.querySelector('#class-id').value = id;
                    document.querySelector('#class_name').value = class_name;
                });
            });
        });
    </script>
</body>
</html>