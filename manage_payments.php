                <?php
                // Path ke folder data JSON
                $baseDir = __DIR__ . '/data/';
                $studentFile = $baseDir . 'students.json';
                $classFile = $baseDir . 'classes.json';
                $yearFile = $baseDir . 'years.json';
                $priceFile = $baseDir . 'prices.json';
                $paymentFile = $baseDir . 'payments.json';

                function readJsonFile($filename) {
                    if (file_exists($filename)) {
                        $content = file_get_contents($filename);
                        $data = json_decode($content, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return $data ?: [];
                        }
                        // Log error jika JSON rusak
                        error_log("JSON Error in $filename: " . json_last_error_msg());
                    }
                    return [];
                }                

                // Fungsi menulis ke file JSON
                function writeJsonFile($filename, $data)
                {
                    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
                }

                // Fungsi untuk mencari data berdasarkan ID
                function findById($array, $id)
                {
                    foreach ($array as $item) {
                        if (isset($item['id']) && $item['id'] === $id) {
                            return $item;
                        }
                    }
                    return null; // Jika tidak ditemukan, kembalikan null
                }

                // Load data
                $payments = readJsonFile($paymentFile);
                $prices = readJsonFile($priceFile);
                $classes = readJsonFile($classFile);
                $years = readJsonFile($yearFile);
                $students = readJsonFile($studentFile);

                // Fungsi untuk mengambil nilai aman dari array
                function getArrayValue($array, $key, $default = '')
                {
                    return isset($array[$key]) ? $array[$key] : $default;
                }

                // Tambahkan pembayaran baru
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $studentId = htmlspecialchars($_POST['student_id'] ?? '');
                    $classId = htmlspecialchars($_POST['class_id'] ?? '');
                    $yearId = htmlspecialchars($_POST['year_id'] ?? '');
                    $priceId = htmlspecialchars($_POST['price_id'] ?? '');
                    $paymentDate = htmlspecialchars($_POST['payment_date'] ?? '');

                    // Validasi input
                    if (empty($studentId) || empty($classId) || empty($yearId) || empty($priceId) || empty($paymentDate)) {
                        $error = 'Semua field wajib diisi!';
                    } else {
                        $newPayment = [
                            'id' => uniqid(),
                            'student_id' => $studentId,
                            'class_id' => $classId,
                            'year_id' => $yearId,
                            'price_id' => $priceId,
                            'payment_date' => $paymentDate
                        ];
                        $payments[] = $newPayment;
                        writeJsonFile($paymentFile, $payments);
                        $success = 'Pembayaran berhasil ditambahkan!';
                    }
                }

                // Hapus pembayaran
                if (isset($_GET['delete_id'])) {
                    $deleteId = htmlspecialchars($_GET['delete_id']);

                    // Pastikan ID pembayaran valid
                    $paymentIndex = array_search($deleteId, array_column($payments, 'id'));
                    if ($paymentIndex !== false) {
                        unset($payments[$paymentIndex]);
                        $payments = array_values($payments); // Re-index array
                        writeJsonFile($paymentFile, $payments);
                        $success = 'Pembayaran berhasil dihapus!';
                    } else {
                        $error = 'Pembayaran tidak ditemukan!';
                    }
                }

                // Edit pembayaran (Jika ada)
                if (isset($_GET['edit_id'])) {
                    $editId = htmlspecialchars($_GET['edit_id']);
                    $paymentToEdit = findById($payments, $editId);
                    if ($paymentToEdit) {
                        // Form untuk mengedit pembayaran
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $studentId = htmlspecialchars($_POST['student_id'] ?? '');
                            $classId = htmlspecialchars($_POST['class_id'] ?? '');
                            $yearId = htmlspecialchars($_POST['year_id'] ?? '');
                            $priceId = htmlspecialchars($_POST['price_id'] ?? '');
                            $paymentDate = htmlspecialchars($_POST['payment_date'] ?? '');

                            // Validasi input
                            if (empty($studentId) || empty($classId) || empty($yearId) || empty($priceId) || empty($paymentDate)) {
                                $error = 'Semua field wajib diisi!';
                            } else {
                                $paymentToEdit['student_id'] = $studentId;
                                $paymentToEdit['class_id'] = $classId;
                                $paymentToEdit['year_id'] = $yearId;
                                $paymentToEdit['price_id'] = $priceId;
                                $paymentToEdit['payment_date'] = $paymentDate;

                                // Update pembayaran dalam array
                                foreach ($payments as $key => $payment) {
                                    if ($payment['id'] === $editId) {
                                        $payments[$key] = $paymentToEdit;
                                        break;
                                    }
                                }

                                writeJsonFile($paymentFile, $payments);
                                $success = 'Pembayaran berhasil diperbarui!';
                            }
                        }
                    } else {
                        $error = 'Pembayaran tidak ditemukan untuk diedit!';
                    }
                }
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Manajemen Pembayaran</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
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

                    <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
                        <h1 class="mb-4">Manajemen Pembayaran</h1>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <!-- Form Tambah Pembayaran -->
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Nama Siswa</label>
                                <select class="form-select" id="student_id" name="student_id" required>
                                    <option value="">Pilih Siswa</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['name']; ?>" <?php echo (isset($paymentToEdit) && $paymentToEdit['student_id'] == $student['name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="class_id" class="form-label">Kelas</label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo (isset($paymentToEdit) && $paymentToEdit['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="year_id" class="form-label">Tahun Ajaran</label>
                                <select class="form-select" id="year_id" name="year_id" required>
                                    <option value="">Pilih Tahun</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year['id']; ?>" <?php echo (isset($paymentToEdit) && $paymentToEdit['year_id'] == $year['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year['year']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price_id" class="form-label">Jenis Pembayaran</label>
                                <select class="form-select" id="price_id" name="price_id" required>
                                    <option value="">Pilih Pembayaran</option>
                                    <?php foreach ($prices as $price): ?>
                                        <option value="<?php echo $price['id']; ?>" <?php echo (isset($paymentToEdit) && $paymentToEdit['price_id'] == $price['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($price['description']) . ' - Rp' . number_format($price['amount'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Tanggal Pembayaran</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo isset($paymentToEdit) ? htmlspecialchars($paymentToEdit['payment_date']) : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo isset($paymentToEdit) ? 'Perbarui Pembayaran' : 'Tambah Pembayaran'; ?></button>
                        </form>

                        <!-- Tabel Pembayaran -->
                        <table class="table table-bordered">
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
                                <?php foreach ($payments as $index => $payment): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?= htmlspecialchars($payment['student_id']); ?></td>

                                        <?php
                                        $class = findById($classes, $payment['class_id']);
                                        $className = $class ? $class['class_name'] : 'Tidak ditemukan';
                                        ?>
                                        <td><?php echo htmlspecialchars($className); ?></td>

                                        <?php
                                        $year = findById($years, $payment['year_id']);
                                        $yearName = $year ? $year['year'] : 'Tidak ditemukan';
                                        ?>
                                        <td><?php echo htmlspecialchars($yearName); ?></td>

                                        <?php
                                        $price = findById($prices, $payment['price_id']);
                                        $priceDescription = $price ? $price['amount'] : 'Tidak ditemukan';
                                        ?>
                                        <td><?php echo htmlspecialchars($priceDescription); ?></td>

                                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>

                                        <td>
    <a href="?edit_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="?delete_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?');">Hapus</a>
</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </main>
                </div>
                </div>
                </body>
                </html>