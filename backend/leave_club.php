<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız.']);
    exit;
}

$userId = $_SESSION['id'];

// Gelen JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['club_id'])) {
    echo json_encode(['success' => false, 'message' => 'Kulüp bilgisi eksik.']);
    exit;
}
$clubId = (int)$data['club_id'];

require_once '../backend/database.php'; // Doğru yolu ver

$db = new Database();

// Önce kullanıcının bu kulüpte approved üyelik durumunu kontrol et
$membership = $db->getRow("SELECT * FROM clubmembers WHERE user_id = :user_id AND club_id = :club_id AND status = 'approved'", [
    'user_id' => $userId,
    'club_id' => $clubId
]);

if (!$membership) {
    echo json_encode(['success' => false, 'message' => 'Bu kulüpte üyeliğiniz yok veya ayrılmaya yetkiniz yok.']);
    exit;
}

// Üyelik kaydını sil
$deleted = $db->execute("DELETE FROM clubmembers WHERE user_id = :user_id AND club_id = :club_id", [
    'user_id' => $userId,
    'club_id' => $clubId
]);

if ($deleted) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası oluştu.']);
}
?>
