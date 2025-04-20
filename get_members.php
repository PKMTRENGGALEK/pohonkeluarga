<?php
header('Content-Type: application/json');

// Aktifkan error reporting untuk debug (sebaiknya dimatikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Query ambil semua data anggota keluarga
$sql = "SELECT * FROM family_members";
$result = $conn->query($sql);

if (!$result) {
    // Kirim error kalau query gagal
    echo json_encode([
        'status' => 'error',
        'message' => 'Query failed: ' . $conn->error
    ]);
    exit;
}

// Ambil data dan simpan ke array
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'spouse' => $row['spouse'],
        'photo' => $row['photo'],
        'spouse_photo' => $row['spouse_photo'],
        'parent_id' => $row['parent_id'],
        'created_at' => $row['created_at'],
        'birthdate' => $row['birthdate'],      // Tambahan biodata
        'address' => $row['address'],
        'phone' => $row['phone']
    ];
}

// Kirim data dalam format JSON
echo json_encode([
    'status' => 'success',
    'data' => $members
]);
?>
