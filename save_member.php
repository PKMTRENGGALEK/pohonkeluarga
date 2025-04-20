<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Fungsi bantu untuk bind_param dinamis (pengganti ...$params)
function refValues($arr) {
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    return $arr;
}

// Buat folder upload jika belum ada
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
    @chmod($uploadDir, 0777);
}

// Ambil input
$name      = isset($_POST['name']) ? $_POST['name'] : '';
$spouse    = isset($_POST['spouse']) ? $_POST['spouse'] : '';
$parent_id = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') ? (int) $_POST['parent_id'] : null;
$id        = (isset($_POST['id']) && $_POST['id'] !== '') ? (int) $_POST['id'] : null;
// Jika update dan parent_id sama dengan id, ambil parent lama
if ($id && $parent_id === $id) {
    $stmtP = $conn->prepare("SELECT parent_id FROM family_members WHERE id = ?");
    $stmtP->bind_param('i', $id);
    $stmtP->execute();
    $stmtP->bind_result($fetched_parent_id);
    if ($stmtP->fetch()) {
        $parent_id = $fetched_parent_id;
    }
    $stmtP->close();
}

// Upload foto utama
$photoPath = '';
if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === 0) {
    $ext       = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photoPath = $uploadDir . 'photo_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
}

// Upload foto pasangan
$spousePhotoPath = '';
if (!empty($_FILES['spousePhoto']['tmp_name']) && $_FILES['spousePhoto']['error'] === 0) {
    $ext             = pathinfo($_FILES['spousePhoto']['name'], PATHINFO_EXTENSION);
    $spousePhotoPath = $uploadDir . 'spouse_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['spousePhoto']['tmp_name'], $spousePhotoPath);
}

if ($id) {
    // UPDATE existing member
    $sql    = "UPDATE family_members SET name = ?, spouse = ?";
    $types  = "ss";
    $params = [$name, $spouse];

    if ($parent_id !== null) {
        $sql      .= ", parent_id = ?";
        $types    .= "i";
        $params[]  = $parent_id;
    } else {
        $sql .= ", parent_id = NULL";
    }

    if ($photoPath) {
        $sql      .= ", photo = ?";
        $types    .= "s";
        $params[]  = $photoPath;
    }

    if ($spousePhotoPath) {
        $sql      .= ", spouse_photo = ?";
        $types    .= "s";
        $params[]  = $spousePhotoPath;
    }

    $sql      .= " WHERE id = ?";
    $types    .= "i";
    $params[]  = $id;

    $stmt = $conn->prepare($sql);
    $bind_params = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], refValues($bind_params));

} else {
    // INSERT new member
    if ($parent_id !== null) {
        $stmt = $conn->prepare(
            "INSERT INTO family_members (name, spouse, photo, spouse_photo, parent_id, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("ssssi", $name, $spouse, $photoPath, $spousePhotoPath, $parent_id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO family_members (name, spouse, photo, spouse_photo, parent_id, created_at)
             VALUES (?, ?, ?, ?, NULL, NOW())"
        );
        $stmt->bind_param("ssss", $name, $spouse, $photoPath, $spousePhotoPath);
    }
}

// Eksekusi dan respon
if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
