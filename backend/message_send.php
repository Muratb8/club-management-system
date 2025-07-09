<?php
// Hata mesajlarını gizle
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../backend/database.php';

// JSON başlığını ayarla
header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// POST verilerini al
$club_id = $_POST['club_id'] ?? null;
$message = $_POST['message'] ?? null;
$clubpresident_id = $_POST['clubpresident_id'] ?? null;
$user_id = $_SESSION['id'];

// Veri kontrolü
if (!$club_id || !$message || !$clubpresident_id) {
    echo json_encode(['success' => false, 'message' => 'Tüm alanları doldurun']);
    exit();
}

try {
    $db = new Database();
    $db->getConnection();

    
    if (!$clubpresident_id) {
        echo json_encode(['success' => false, 'message' => 'Kulüp başkanı bulunamadı']);
        exit();
    }

    $student = $db->getRow("SELECT student_id  FROM students WHERE user_id = ?", [$user_id]);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Öğrenci bulunamadı']);
        exit();
    }

    $student_id = $student['student_id'];

    // Mesajı veritabanına ekle
    $data = [
        'student_id' => $student_id,
        'president_id' => $clubpresident_id,
        'message' => $message,
        'date' => date('Y-m-d H:i:s')
    ];

    $result = $db->insertData('messages', $data);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Mesajınız başarıyla gönderildi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mesaj gönderilirken bir hata oluştu']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
?>
