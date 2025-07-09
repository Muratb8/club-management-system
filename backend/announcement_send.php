<?php
session_start();
require_once 'database.php';
header('Content-Type: application/json');

// 1. Oturum ve rol kontrolü
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'club_president') {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim. Lütfen giriş yapın.']);
    exit();
}

$user_id = $_SESSION['id'];

// 2. POST verilerini al ve XSS filtrele
$title = htmlspecialchars($_POST['title'] ?? '');
$content = htmlspecialchars($_POST['content'] ?? '');
$category = htmlspecialchars($_POST['category'] ?? '');
$date = $_POST['date'] ?? ''; // Tarih verisini al

// 3. Alan doğrulama
if (!$title || !$content || !$category || !$date) {
    echo json_encode(['success' => false, 'message' => 'Tüm alanları doldurun.']);
    exit();
}

// 4. Tarih formatı doğrulama ve düzeltme
// datetime-local formatını Y-m-d H:i:s formatına çevir
$announcement_date = date('Y-m-d H:i:s', strtotime($date));

// 5. Veritabanı işlemleri
try {
    $db = new Database();
    $db->getConnection();

    // 6. Kulüp başkanı id'sini al
    $president = $db->getRow("SELECT president_id FROM clubpresidents WHERE user_id = ?", [$user_id]);
    if (!$president) {
        echo json_encode(['success' => false, 'message' => 'Kulüp başkanı bulunamadı.']);
        exit();
    }

    // 7. Kulübün id'sini al
    $club = $db->getRow("SELECT id FROM clubs WHERE clubpresident_id = ?", [$president['president_id']]);
    if (!$club) {
        echo json_encode(['success' => false, 'message' => 'Bağlı kulüp bulunamadı.']);
        exit();
    }

    // 8. Duyuruyu ekle
    $announcementData = [
        'user_id' => $user_id,
        'title' => $title,
        'content' => $content,
        'category' => $category,
        'announcement_date' => $announcement_date, // Düzeltilmiş tarih formatı
        'created_at' => date('Y-m-d H:i:s'), // Güncel oluşturulma tarihi
        'club_id' => $club['id']  // Tek bir kulüp id'sini kullanıyoruz
    ];

    // Duyuruyu veritabanına ekle
    $result = $db->insertData('announcements', $announcementData);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Duyurunuz başarıyla oluşturuldu.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Duyuru eklenirken hata oluştu.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
?>
