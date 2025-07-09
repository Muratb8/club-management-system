<?php
include_once 'database.php';
session_start();
header('Content-Type: application/json'); // JSON olarak yanıtla

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $db = new Database();
    $db->getConnection();

    $userEmail = $_POST['userEmail'] ?? '';
    $userRole = $_POST['userRole'] ?? '';

    if (empty($userEmail) || empty($userRole)) {
        echo json_encode(['success' => false, 'message' => 'Eksik veri gönderildi.']);
        exit();
    }

    // Email zaten kayıtlı mı kontrol et
    $existingUser = $db->getRow("SELECT * FROM users WHERE email = ?", [$userEmail]);

    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı!']);
        exit();
    }

    // Yeni kullanıcı ekle
    $data = [
        'email' => $userEmail,
        'PASSWORD' => '1',
        'role' => $userRole,
    ];

    $userId = $db->insertData('users', $data);

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Kullanıcı eklenirken bir hata oluştu.']);
        exit();
    }

    // Ekstra tablolar
    if ($userRole == 'student') {
        $db->insertData('students', ['user_id' => $userId]);
    } elseif ($userRole == 'club_president') {
        $db->insertData('clubpresidents', ['user_id' => $userId]);
    }

    // Başarılı sonuç döndür
    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla kaydedildi!']);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
}
?>
