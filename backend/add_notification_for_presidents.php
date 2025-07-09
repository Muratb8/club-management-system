<?php
require_once 'database.php';

header('Content-Type: application/json');
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $title = "Seçim Bildirimi"; // İsterseniz formdan da alabilirsiniz

    if ($message === '') {
        echo json_encode(['success' => false, 'message' => 'Mesaj boş olamaz.']);
        exit;
    }

    // Kulüp başkanlarının user_id'lerini çek
 $presidents = $db->getAllRecords(
    "SELECT cp.user_id 
     FROM clubs c
     INNER JOIN clubpresidents cp ON c.clubpresident_id = cp.president_id
     WHERE c.clubpresident_id IS NOT NULL"
);
    if (empty($presidents)) {
        echo json_encode(['success' => false, 'message' => 'Hiç kulüp başkanı bulunamadı.']);
        exit;
    }

    foreach ($presidents as $president) {
        $db->insertData('notifications', [
            'user_id' => $president['user_id'],
            'title' => $title,
            'content' => $message,
            'status' => 'unread',
            'type' => 'information'
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Bildirimler başarıyla gönderildi.']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}
?>