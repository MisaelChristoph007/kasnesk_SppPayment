<?php
// Simpan ini di login_process.php

// Hard-coded admin credentials (contoh sederhana, seharusnya di database)
$admin_username = "admin";
$admin_password = "123";

// Ambil data dari form
$username = $_POST['username'];
$password = $_POST['password'];

// Validasi login
if ($username == $admin_username && $password == $admin_password) {
    // Jika login sukses, redirect ke halaman dashboard admin
    session_start();
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin_dashboard.php");
    exit();
} else {
    // Jika login gagal, kembalikan ke halaman login
    header("Location: login.php");
    exit();
}
?>