<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapılmamış']);
    exit();
}

require_once 'database.php';
$db = new Database();

$student_id = $_SESSION['id'];
$club_id = $_POST['club_id'] ?? null;

if (!$club_id) {
    echo json_encode(['status' => 'error', 'message' => 'Kulüp seçilmedi']);
    exit();
}

// Daha önce başvuru yapmış mı kontrol et
$check = $db->getRow(
    "SELECT * FROM clubmembers WHERE user_id = :user_id AND club_id = :club_id",
    ['user_id' => $student_id, 'club_id' => $club_id]
);

if ($check) {
    echo json_encode(['status' => 'error', 'message' => 'Zaten başvuru yapmışsınız'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Başvuru ekle
try {
    $db->insertData('clubmembers', [
        'club_id' => $club_id,
        'user_id' => $student_id,
        'status' => 'pending'
    ]);
    echo json_encode(['status' => 'success', 'message' => 'Başvuru yapıldı'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Başvuru yapılamadı'], JSON_UNESCAPED_UNICODE);
}
?>