<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../public/giris.php");
    exit();
}

require_once '../backend/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelen verileri al
    $activityTopic = htmlspecialchars($_POST['activityTopic'] ?? '', ENT_QUOTES, 'UTF-8');
    $activityDescription = htmlspecialchars($_POST['activityDescription'] ?? '', ENT_QUOTES, 'UTF-8');
    $activityDate = htmlspecialchars($_POST['activityDate'] ?? '', ENT_QUOTES, 'UTF-8');
    $participants = htmlspecialchars($_POST['participants'] ?? '', ENT_QUOTES, 'UTF-8');
    $eventLocation = htmlspecialchars($_POST['eventLocation'] ?? '', ENT_QUOTES, 'UTF-8'); // Event Location eklendi
    $clubPresidentId = $_SESSION['id'];

    try {
        $db = new Database(); // Database sınıfı üzerinden bağlantıyı al
        $data = [
            'club_president_id' => $clubPresidentId,
            'event_topic' => $activityTopic,
            'description'=>$activityDescription,
            'event_date' => $activityDate,
            'participants' => $participants,
            'location' => $eventLocation, // Veritabanına event_location verisi ekleniyor
            'application_status' => 'pending',
            'application_date' => date('Y-m-d H:i:s'),
        ];

        $db->insertData('event_application', $data);

        echo json_encode(['success' => true, 'message' => 'Etkinlik başarıyla oluşturuldu!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}
?>
