<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Path to JSON file
$yearsFile = 'data/years.json';

// Load JSON data
$years = file_exists($yearsFile) ? json_decode(file_get_contents($yearsFile), true) : [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = $_POST['id'] ?? null;
        $year = $_POST['year'] ?? null;

        if ($action == 'add') {
            $years[] = [
                'id' => uniqid(),
                'year' => htmlspecialchars($year)
            ];
        } elseif ($action == 'edit') {
            foreach ($years as &$item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    $item['year'] = htmlspecialchars($year);
                    break;
                }
            }
        } elseif ($action == 'delete') {
            $years = array_filter($years, function($item) use ($id) {
                return isset($item['id']) && $item['id'] !== $id;
            });
        }

        file_put_contents($yearsFile, json_encode($years, JSON_PRETTY_PRINT));
        header("Location: manage_years.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tahun Ajaran</title>
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
                <h2 class="my-4">Manajemen Tahun Ajaran</h2>

                <!-- Form Add/Edit Year -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" id="year-id" value="">
                    <div class="mb-3">
                        <label for="year" class="form-label">Tahun Ajaran</label>
                        <input type="text" class="form-control" id="year" name="year" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>

                <!-- Year Table -->
                <h3>Daftar Tahun Ajaran</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tahun Ajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $year) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($year['id'] ?? 'Tidak Diketahui'); ?></td>
                                <td><?php echo htmlspecialchars($year['year'] ?? 'Tidak Diketahui'); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo htmlspecialchars($year['id'] ?? ''); ?>" data-year="<?php echo htmlspecialchars($year['year'] ?? ''); ?>">Edit</button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($year['id'] ?? ''); ?>">
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
                    const year = button.getAttribute('data-year');
                    document.querySelector('input[name="action"]').value = 'edit';
                    document.querySelector('#year-id').value = id;
                    document.querySelector('#year').value = year;
                });
            });
        });
    </script>
</body>
</html>