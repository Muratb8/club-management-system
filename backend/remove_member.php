<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// POST verileri
$user_id = $_POST['user_id'] ?? null;
$club_id = $_POST['club_id'] ?? null;
$reason = $_POST['reason'] ?? 'Belirtilmedi';  // Neden çıkarıldığı bilgisi

if (!$user_id || !$club_id) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
    exit();
}

try {
    $db = new Database();

    // Kulüp adını al (bildirim içeriği için)
    $club = $db->getRow("SELECT name FROM clubs WHERE id = ?", [$club_id]);
    if (!$club) {
        echo json_encode(['success' => false, 'message' => 'Kulüp bulunamadı']);
        exit();
    }

    // Üyeyi kulüpten çıkar
    $deleteSql = "DELETE FROM clubmembers WHERE user_id = ? AND club_id = ?";
    $deleteStmt = $db->prepare($deleteSql);
    $removed = $deleteStmt->execute([$user_id, $club_id]);

    if ($removed) {
        // Bildirim ekle
        $title = "Kulüpten Çıkarıldınız";
        $content = "{$club['name']} adlı kulüpten çıkarıldınız. Sebep: $reason";

        $notificationData = [
            'user_id' => $user_id,
            'title' => $title,
            'content' => $content,
            'type' => 'information',
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'unread'
        ];

        $db->insertData('notifications', $notificationData);

        echo json_encode(['success' => true, 'message' => 'Üye başarıyla çıkarıldı ve bildirim gönderildi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Üye çıkarılırken bir hata oluştu']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
?>
