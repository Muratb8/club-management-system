<?php
session_start();
require_once "database.php"; // Veritabanı bağlantısı

header("Content-Type: application/json");

// Hata raporlamayı aç (Geliştirme için)
error_reporting(E_ALL);
ini_set("display_errors", 1);

$response = ["success" => false, "message" => "Bilinmeyen bir hata oluştu."];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Gelen verileri al
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Boş alan kontrolü
    if (empty($email) || empty($password)) {
        $response["message"] = "E-posta veya şifre boş olamaz.";
        echo json_encode($response);
        exit;
    }

    try {
        // Kullanıcıyı veritabanında ara
        $db = new Database();
        $user = $db->getRow("SELECT * FROM users WHERE email = ?", [$email]);

        if ($user) {
            // Şifre hash'li mi kontrol et
            $dbPassword = $user["PASSWORD"];
            $isHashed = (strlen($dbPassword) === 60 && strpos($dbPassword, '$2y$') === 0);

            if (
                ($isHashed && password_verify($password, $dbPassword)) || // hash'li ise kontrol et
                (!$isHashed && $password === $dbPassword) // düz metin ise doğrudan karşılaştır
            ) {
                $_SESSION["id"] = $user["id"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];
                $response["success"] = true;
                $response["message"] = "Giriş başarılı!";
                echo json_encode($response);
                exit;
            } else {
                $response["message"] = "Şifre hatalı.";
            }
        } else {
            $response["message"] = "Böyle bir kullanıcı bulunamadı.";
        }
    } catch (Exception $e) {
        $response["message"] = "Hata: " . $e->getMessage();
    }
}

// JSON formatında hata mesajını döndür
echo json_encode($response);
exit;
?>
