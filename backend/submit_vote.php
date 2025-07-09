<?php
session_start();
require_once '../backend/database.php';

$db = new Database();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo "Yetkisiz erişim.";
    exit();
}

$user_id = $_SESSION['id'];
$student = $db->getRow("SELECT student_id FROM students WHERE user_id = :user_id", [
    'user_id' => $user_id
]);

if (!$student || !isset($student['student_id'])) {
    http_response_code(404);
    echo "Öğrenci kaydı bulunamadı.";
    exit();
}

$student_id = $student['student_id'];

// club_id kontrolü
if (!isset($_POST['club_id']) || !is_numeric($_POST['club_id'])) {
    http_response_code(400);
    echo "Kulüp bilgisi eksik.";
    exit();
}
$club_id = (int)$_POST['club_id'];

if (!isset($_POST['candidates']) || !is_array($_POST['candidates'])) {
    http_response_code(400);
    echo "Aday seçilmedi.";
    exit();
}

$candidate_ids = $_POST['candidates'];

$hasVoted = $db->getRow("
    SELECT 1 
    FROM votes v 
    JOIN candidates c ON v.candidate_id = c.candidate_id 
    WHERE v.student_id = ? AND c.club_id = ?
", [$student_id, $club_id]);

if ($hasVoted) {
    echo "Bu kulüpte zaten oy kullandınız. İkinci kez oy kullanamazsınız.";
    exit;
}
if (count($candidate_ids) > 5) {
    http_response_code(400);
    echo "En fazla 5 aday seçebilirsiniz.";
    exit();
}

try {
    foreach ($candidate_ids as $candidate_id) {
        $exists = $db->getRow(
            "SELECT * FROM votes WHERE student_id = :student_id AND candidate_id = :candidate_id",
            [
                'student_id' => $student_id,
                'candidate_id' => $candidate_id
            ]
        );

        if (!$exists) {
            $db->insertData('votes', [
                'student_id' => $student_id,
                'candidate_id' => $candidate_id,
                'club_id' => $club_id
            ]);
        }
    }

    echo "Oylar başarıyla kaydedildi.";
} catch (Exception $e) {
    http_response_code(500);
    echo "Bir hata oluştu: " . $e->getMessage();
}
