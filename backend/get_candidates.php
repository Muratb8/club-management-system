<?php
session_start();
require_once 'database.php';

$db = new Database();

//kulüp bilgisinin ve kullanıcının id kontrolü
if (!isset($_POST['club_id']) || !isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}

//kulüp ve öğrencinin id'si aktarılıyor
$club_id = $_POST['club_id'];
$user_id = $_SESSION['id'];


//kullanıcının id'si tekrar kontrol ediliyor
if (!$user_id) {
    echo json_encode(['error' => 'Kullanıcı oturumu bulunamadı.']);
    exit;
}

//kullanıcının id'si üzerinden öğrencinin id'sini alıyoruz

// 1. user_id'den student_id'yi al
$student = $db->getRow("SELECT student_id FROM students WHERE user_id = ?", [$user_id]);

//eğer eşleşen bir öğrenci yoksa, hata mesajı gözükecek
if (!$student) {
    echo json_encode(['error' => 'Öğrenci bilgisi bulunamadı.']);
    exit;
}

//öğrencinin sadece id değerini alabilmek için student_id isminde değişken oluşturduk.
$student_id = $student['student_id'];

// Öğrencinin bu kulüpte oy verip vermediğini kontrol et
$hasVoted = $db->getRow("
    SELECT 1 
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.candidate_id
    WHERE v.student_id = ? AND c.club_id = ?
    LIMIT 1
", [$student_id, $club_id]);

// Adayları getir
$candidates = $db->getAllRecords("
    SELECT 
        c.candidate_id AS id, 
        c.student_id, 
        c.manifesto,
        c.photo AS image, 
        s.first_name AS name
    FROM candidates c
    JOIN students s ON c.student_id = s.student_id 
    WHERE c.club_id = ?
", [$club_id]);

// Öğrencinin daha önce oy verdiği adayları getir
$voted = $db->getAllRecords("
    SELECT candidate_id 
    FROM votes 
    WHERE student_id = ? ", 
    [$student_id]
);

$voted_ids = array_column($voted, 'candidate_id');
// 5. JSON cevabı
echo json_encode([
    'candidates' => $candidates,
    'voted_ids' => $voted_ids,
    'has_voted' => $hasVoted ? true : false
]);