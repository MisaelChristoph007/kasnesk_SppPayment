<?php
session_start();

// Path ke file data siswa
$studentFile = 'data/students.json'; // Pastikan file JSON siswa ada di lokasi ini
$officerFile = 'data/role.json'; // File JSON untuk data petugas

// Fungsi untuk membaca file JSON
function readJsonFile($filename)
{
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Fungsi untuk memverifikasi login siswa
function verifyLogin($username, $password, $dataFile)
{
    $users = readJsonFile($dataFile);
    foreach ($users as $user) {
        if (
            isset($user['username'], $user['password']) && 
            $user['username'] === $username && 
            $user['password'] === $password
        ) {
            return $user;
        }
    }
    return null;
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah login untuk admin
    if ($username == 'admin' && $password == '123') {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit();
    }

    if ($username == 'petugas' && $password == '123') {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'petugas';
        header("Location: petugas_dashboard.php");
        exit();
    }

    // Cek apakah login untuk siswa
    $student = verifyLogin($username, $password, $studentFile);
    if ($student) {
        $_SESSION['student_username'] = $student['username'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['student_class'] = $student['class'];
        $_SESSION['student_id_spp'] = $student['id_spp']; // Menyimpan id_spp jika diperlukan
        $_SESSION['role'] = 'student';
        header("Location: siswa_dashboard.php");
        exit();
    }

    // Jika semua gagal
    $error = "Invalid Username or Password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="w-50">
            <img src="kasnesk.png" id="img" class="mb-3">
            <h1 class="text-center mb-4">Silahkan Login Terlebih Dahulu...</h1>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" name="login">Login</button>
                <audio id="errorSound" src="error-sound.mp3"></audio>
            </form>
        </div>
    </div>
    <script>
        // Mainkan suara jika terjadi kesalahan login
        <?php if ($error): ?>
            document.getElementById('errorSound').play();
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>