<?php
// Oturum açılmadıysa yönlendir
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Kullanıcı oturumu açmamış.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST verilerinden gelen bilgiler
    $id = $_POST['id'];
    $action = $_POST['action'];
    $manager_id = $_POST['manager_id'];  // Kullanıcı ID'si

    // Veritabanı işlemleri
    require_once 'database.php';
    $db = new Database();

    // Durum ve güncelleme işlemleri
    try {
        if ($action === 'approve') {
            // Etkinliği onayla
            $sql = "UPDATE event_application SET application_status = 'approved', manager_id = :manager_id WHERE id = :id";
        } elseif ($action === 'reject') {
            // Etkinliği reddet
            $sql = "UPDATE event_application SET application_status = 'rejected', manager_id = :manager_id WHERE id = :id";
        } else {
            echo json_encode(['success' => false, 'error' => 'Geçersiz işlem']);
            exit;
        }

        $params = [
            ':manager_id' => $manager_id,  // Manager ID
            ':id' => $id
        ];

        $db->update($sql, $params);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>