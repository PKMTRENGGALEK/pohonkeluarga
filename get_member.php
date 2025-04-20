<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Pastikan ID dikirim via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
    exit;
}

$id = (int) $_GET['id'];

// Ambil data anggota berdasarkan ID
$stmt = $conn->prepare(
    'SELECT id, name, spouse, photo, spouse_photo, parent_id FROM family_members WHERE id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Anggota tidak ditemukan']);
    exit;
}

$member = $result->fetch_assoc();

echo json_encode(['status' => 'success', 'data' => $member]);

$stmt->close();
$conn->close();
