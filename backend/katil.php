<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo "Yetkisiz erişim.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $user_id = $_SESSION['id']; // <-- Doğru değişken

    try {
        $db = new Database();

        // user_id'den student_id'yi bul
        $sql = "SELECT student_id FROM students WHERE user_id = :user_id";
        $student = $db->getRow($sql, ['user_id' => $user_id]);

        if (!$student) {
            echo "Öğrenci bulunamadı.";
            exit;
        }

        $student_id = $student['student_id'];

        // Daha önce katılmış mı kontrol et
        $checkSql = "SELECT id FROM event_participants WHERE student_id = :student_id AND event_id = :event_id";
        $exists = $db->getRow($checkSql, ['student_id' => $student_id, 'event_id' => $event_id]);

        if ($exists) {
            echo "Zaten katıldınız.";
            exit;
        }

        // Katılım ekle
        $insertSql = "INSERT INTO event_participants (student_id, event_id) VALUES (:student_id, :event_id)";
        $db->insertData('event_participants', [
            'student_id' => $student_id,
            'event_id' => $event_id
        ]);

        echo "Katılım başarılı.";
    } catch (Exception $e) {
        echo "Hata: " . $e->getMessage();
    }
} else {
    echo "Geçersiz istek.";
}
?>
