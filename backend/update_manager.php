<?php

require_once 'database.php'; // Veritabanı bağlantı dosyanız
session_start();

// CSRF token kontrolü
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit();
}

// Oturum kontrolü
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST verilerini al
    $user_id = $_POST['user_id'] ?? null;
    $ad = $_POST['ad'] ?? '';
    $soyad = $_POST['soyad'] ?? '';

    // Veri doğrulama
    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "Aktivite Merkezi Sorumlusu seçilmedi."]);
        exit;
    }

    // XSS saldırılarına karşı, ad ve soyad alanlarını temizle
    $ad = htmlspecialchars(trim($ad), ENT_QUOTES, 'UTF-8');
    $soyad = htmlspecialchars(trim($soyad), ENT_QUOTES, 'UTF-8');

    // Veri boyutunu kontrol et (ad ve soyad için örnek sınır)
    if (strlen($ad) > 100 || strlen($soyad) > 100) {
        echo json_encode(["status" => "error", "message" => "Ad ve soyad 100 karakteri aşamaz."]);
        exit;
    }

    try {
        $db = new Database();
        
        // SQL sorgusunu prepared statement ile güvenli hale getir
        $updateQuery = "UPDATE managers SET 
                        ad = :ad, 
                        soyad = :soyad 
                        WHERE user_id = :user_id";

        $params = [
            ":user_id" => $user_id,
            ":ad" => $ad,
            ":soyad" => $soyad
        ];

        $result = $db->execute($updateQuery, $params);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Aktivite Merkezi Sorumlusu bilgileri güncellendi."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Güncelleme başarısız."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Hata oluştu: " . $e->getMessage()]);
    }
}
?>
