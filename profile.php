<?php
session_start();

if (!isset($_SESSION['student_username'])) {
    header("Location: index.php");
    exit();
}

$studentFile = 'data/students.json';
if (!file_exists($studentFile)) {
    die("File data siswa tidak ditemukan."); // Handle jika file tidak ada
}
$students = json_decode(file_get_contents($studentFile), true);

if ($students === null) {
    die("Gagal membaca data siswa dari file."); // Handle jika JSON decode gagal
}

$student = null;
foreach ($students as $s) {
    if ($s['username'] === $_SESSION['student_username']) {
        $student = $s;
        break;
    }
}

if ($student === null) {
    die("Data siswa tidak ditemukan."); // Handle jika data siswa tidak ditemukan
}

$uploadError = '';
$imagePath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) { // Cek error upload
    if ($_FILES['image']['size'] > 1048576) {
        $uploadError = 'Ukuran file terlalu besar (maksimal 1MB).';
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            $uploadError = 'Hanya file JPG, JPEG, dan PNG yang diizinkan.';
        } else {
            $imageName = uniqid() . '.' . $fileExtension;
            $imagePath = 'uploads/' . $imageName;

            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                foreach ($students as &$s) {
                    if ($s['username'] === $_SESSION['student_username']) {
                        $s['image'] = $imagePath;
                        break;
                    }
                }
                if (file_put_contents($studentFile, json_encode($students, JSON_PRETTY_PRINT)) === false) {
                    $uploadError = "Gagal menyimpan data ke file.";
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'imagePath' => $imagePath]);
                    exit;
                }
            } else {
                $uploadError = 'Gagal memindahkan file yang diunggah.';
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    // Handle error upload spesifik
    switch ($_FILES['image']['error']) {
        case UPLOAD_ERR_INI_SIZE:
            $uploadError = "Ukuran file melebihi upload_max_filesize di php.ini.";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $uploadError = "Ukuran file melebihi MAX_FILE_SIZE yang ditentukan dalam form HTML.";
            break;
        case UPLOAD_ERR_PARTIAL:
            $uploadError = "File hanya diunggah sebagian.";
            break;
        case UPLOAD_ERR_NO_FILE:
            $uploadError = "Tidak ada file yang diunggah.";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $uploadError = "Folder sementara tidak ditemukan.";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $uploadError = "Gagal menulis file ke disk.";
            break;
        case UPLOAD_ERR_EXTENSION:
            $uploadError = "Ekstensi file menghentikan pengunggahan.";
            break;
        default:
            $uploadError = "Terjadi kesalahan yang tidak diketahui saat mengunggah.";
            break;
    }
}

if ($uploadError && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $uploadError]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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

    <!-- Navbar -->
    <div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="position-sticky">
                <a class="text-center mt-3" href="siswa_dashboard.php">
                <h4 class="text-center mt-3">Student Dashboard</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="status.php">
                            <i class="bi bi-calendar"></i>
                            Payment Status
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-tag"></i>
                            Profile
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">

        <h1>Profile - <?= $_SESSION['student_name'] ?></h1>
        <div class="row">
        <div class="container mt-5">
    <form id="uploadForm" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="image" class="form-label">Upload Profile Picture</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
    <div id="profilePictureContainer">
        <?php if (!empty($student['image'])): ?>
            <img id="profilePicture" src="<?= htmlspecialchars($student['image']); ?>" alt="Profile Picture" class="img-thumbnail" width="200">
        <?php else: ?>
            <p id="noProfilePicture">Belum ada foto profil.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();

    xhr.open('POST', 'profile.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const profilePicture = document.getElementById('profilePicture');
                    const navProfilePicture = document.getElementById('navProfilePicture');

                    profilePicture.src = response.imagePath;
                    navProfilePicture.src = response.imagePath;
                } else {
                    alert('Gagal mengunggah gambar: ' + response.error);
                }
            } catch (error) {
                console.error("Error parsing JSON:", error, xhr.responseText);
                alert('Terjadi kesalahan pada server saat memproses respon.');
            }
        } else {
            alert('Terjadi kesalahan pada server. Status: ' + xhr.status);
        }
    };
    xhr.onerror = function() {
        alert("Terjadi kesalahan jaringan saat mengunggah gambar.");
    };
    xhr.send(formData);
});
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
            <div class="col-md-8">
                <h4>Student Information</h4>
                <p><strong>Username:</strong> <?= $student['username'] ?></p>
                <p><strong>Password:</strong> <?= $student['password'] ?></p>
                <p><strong>Class:</strong> <?= $student['class'] ?></p>
                <p><strong>Alamat:</strong> <?= $student['alamat'] ?></p>
                <p><strong>ID SPP:</strong> <?= $student['id_spp'] ?></p>
            </div>
        </div>
    </div>
</body>
</html>
