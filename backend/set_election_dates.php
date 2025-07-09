<?php
require_once 'database.php';

header('Content-Type: application/json');
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (!$start_date || !$end_date) {
        echo json_encode(['success' => false, 'message' => 'Başlangıç ve bitiş tarihi zorunludur.']);
        exit;
    }

    // Tabloya daha önce kayıt eklenmiş mi kontrol et
    $existing = $db->getRow("SELECT id FROM election_settings LIMIT 1");

    if ($existing) {
        // Güncelle
        $db->updateData(
            'election_settings',
            ['start_date' => $start_date, 'end_date' => $end_date],
            'id = :id',
            ['id' => $existing['id']]
        );
        echo json_encode(['success' => true, 'message' => 'Seçim tarihleri güncellendi.']);
    } else {
        // Ekle
        $db->insertData('election_settings', [
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
        echo json_encode(['success' => true, 'message' => 'Seçim tarihleri kaydedildi.']);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit;
}
?>