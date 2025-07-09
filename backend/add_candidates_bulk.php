<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;
    $candidate_ids = isset($_POST['candidate_ids']) ? $_POST['candidate_ids'] : [];

    if (!$club_id || empty($candidate_ids)) {
        echo json_encode(['success' => false, 'message' => 'Lütfen en az bir aday seçin.']);
        exit;
    }

    $added = 0;
    foreach ($candidate_ids as $student_id) {
        // Aynı öğrenci aynı kulüpte aday mı kontrol et
        $exists = $db->getRow(
            "SELECT candidate_id FROM candidates WHERE student_id = :student_id AND club_id = :club_id",
            ['student_id' => $student_id, 'club_id' => $club_id]
        );
        if (!$exists) {
            $db->insertData('candidates', [
                'student_id' => $student_id,
                'club_id' => $club_id
            ]);
            $added++;
        }
    }

    if ($added > 0) {
        echo json_encode(['success' => true, 'message' => 'Adaylar başarıyla eklendi.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Seçilen üyeler zaten aday.']);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}
?>