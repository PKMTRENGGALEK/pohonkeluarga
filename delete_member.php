<?php
header('Content-Type: application/json');
include 'config.php';

if (!isset($_POST['id'])) {
    echo json_encode(['status'=>'error','message'=>'ID tidak diberikan']);
    exit;
}
$id = (int)$_POST['id'];

$stmt = $conn->prepare("DELETE FROM family_members WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
