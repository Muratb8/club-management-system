<?php
require_once '../backend/database.php';

session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$db = new Database();
$user_id = $_SESSION['id'];

// Fetch data for charts
$studentCount = $db->getColumn("SELECT COUNT(*) FROM students");
$clubCount = $db->getColumn("SELECT COUNT(*) FROM clubs");
$presidentCount = $db->getColumn("SELECT COUNT(*) FROM clubpresidents");
$membershipCount = $db->getColumn("SELECT COUNT(*) FROM clubmembers WHERE status = 'approved'");

// Prepare data for charts
$data = [
    'studentCount' => $studentCount,
    'clubCount' => $clubCount,
    'presidentCount' => $presidentCount,
    'membershipCount' => $membershipCount,
];

echo json_encode($data);
?>