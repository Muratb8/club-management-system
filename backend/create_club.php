<?php
session_start();
include_once 'database.php';

// Oturum kontrolü
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

// CSRF token kontrolü (formdan gelen token kontrol edilecek)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = ['text' => 'Geçersiz istek (CSRF hatası)!', 'type' => 'danger'];
        header("Location: index.php");
        exit;
    }

    $db = new Database();
    $db->getConnection(); // Veritabanı bağlantısını sağla

    // Girdileri al
    $clubName = $_POST['clubName'];
    $clubDescription = $_POST['clubDescription'];
    $clubPresident = $_POST['clubPresident']; // Seçilen kulüp başkanı
    $clubLogo = '';

    // Kulüp başkanı doğrulaması
    $validPresident = $db->getRow("SELECT id FROM clubpresidents WHERE id = ?", [$clubPresident]);
    if (!$validPresident) {
        $_SESSION['message'] = ['text' => "Geçersiz kulüp başkanı seçildi!", 'type' => "danger"];
        header("Location: index.php");
        exit;
    }

    // Logo yükleme işlemi
    if (isset($_FILES['clubLogo']) && $_FILES['clubLogo']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $mimeType = mime_content_type($_FILES['clubLogo']['tmp_name']);
        $fileExtension = pathinfo($_FILES['clubLogo']['name'], PATHINFO_EXTENSION);

        if (!in_array($mimeType, $allowedTypes)) {
            $_SESSION['message'] = ['text' => "Yalnızca JPG, PNG veya GIF yükleyebilirsiniz!", 'type' => "danger"];
            header("Location: index.php");
            exit();
        }

        // Dosya adını benzersiz hale getir
        $clubLogo = uniqid('logo_', true) . '.' . $fileExtension;
        $targetDir = "uploads/";
        $targetFile = $targetDir . $clubLogo;

        // Dosyayı yükle
        if (!move_uploaded_file($_FILES["clubLogo"]["tmp_name"], $targetFile)) {
            $_SESSION['message'] = ['text' => "Dosya yüklenemedi!", 'type' => "danger"];
            header("Location: index.php");
            exit();
        }
    }

    // Veritabanına kayıt
    $data = [
        'name' => $clubName,
        'description' => $clubDescription,
        'logo' => $clubLogo,
        'clubpresident_id' => $clubPresident,
    ];

    if ($db->insertData('clubs', $data)) {
        $_SESSION['message'] = ['text' => 'Kulüp başarıyla kaydedildi!', 'type' => 'success'];
    } else {
        $_SESSION['message'] = ['text' => "Kulüp eklenirken bir hata oluştu!", 'type' => "danger"];
    }

    header("Location: index.php");
}
?>
