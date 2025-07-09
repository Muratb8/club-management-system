<?php
session_start();
require_once 'database.php'; // Veritabanı bağlantı dosyanız



if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kullanıcı girişinden alınan veriler
    $user_id = $_POST['user_id'] ?? null;
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $gpa = $_POST['gpa'] ?? '';
    $cgpa = $_POST['cgpa'] ?? '';
    $birth_place = $_POST['birth_place'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $student_number = $_POST['student_number'] ?? '';

    // Öğrenci seçilmediğinde hata mesajı döndür
    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "Öğrenci seçilmedi."]);
        exit;
    }

    // Verileri temizle (XSS koruması)
    $first_name = htmlspecialchars(trim($first_name), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(trim($last_name), ENT_QUOTES, 'UTF-8');
    $birth_place = htmlspecialchars(trim($birth_place), ENT_QUOTES, 'UTF-8');
    $gender = htmlspecialchars(trim($gender), ENT_QUOTES, 'UTF-8');
    $phone_number = htmlspecialchars(trim($phone_number), ENT_QUOTES, 'UTF-8');
    $student_number = htmlspecialchars(trim($student_number), ENT_QUOTES, 'UTF-8');

    // Verilerin türünü ve boyutunu kontrol et
    if (strlen($first_name) > 100 || strlen($last_name) > 100) {
        echo json_encode(["status" => "error", "message" => "Ad ve soyad 100 karakteri aşamaz."]);
        exit;
    }

    // GPA ve CGPA doğrulaması (0-4 arası olmalı)
    if (!is_numeric($gpa) || $gpa < 0 || $gpa > 4) {
        echo json_encode(["status" => "error", "message" => "Geçersiz GPA değeri."]);
        exit;
    }

    if (!is_numeric($cgpa) || $cgpa < 0 || $cgpa > 4) {
        echo json_encode(["status" => "error", "message" => "Geçersiz CGPA değeri."]);
        exit;
    }

    // Telefon numarasının geçerli olduğundan emin ol
    if (!preg_match("/^\d{10}$/", $phone_number)) {
        echo json_encode(["status" => "error", "message" => "Geçersiz telefon numarası formatı."]);
        exit;
    }

    try {
        $db = new Database();
        $updateQuery = "UPDATE students SET 
                        first_name = :first_name, 
                        last_name = :last_name, 
                        gpa = :gpa, 
                        cgpa = :cgpa, 
                        birth_place = :birth_place, 
                        birth_date = :birth_date, 
                        gender = :gender, 
                        phone_number = :phone_number, 
                        student_number = :student_number
                        WHERE user_id = :user_id";

        $params = [
            ":user_id" => $user_id,
            ":first_name" => $first_name,
            ":last_name" => $last_name,
            ":gpa" => $gpa,
            ":cgpa" => $cgpa,
            ":birth_place" => $birth_place,
            ":birth_date" => $birth_date,
            ":gender" => $gender,
            ":phone_number" => $phone_number,
            ":student_number" => $student_number
        ];

        $result = $db->execute($updateQuery, $params);

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Öğrenci bilgileri güncellendi."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Güncelleme başarısız."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Bir hata oluştu."]);
    }
}
