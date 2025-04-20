<?php
$host = 'sql12.freesqldatabase.com';
$db   = 'sql12773995';
$user = 'sql12773995';
$pass = 'ADjmDyNZzP';

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset ke UTF-8
$conn->set_charset("utf8");
?>
