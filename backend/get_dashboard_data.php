<?php
require_once 'database.php';

session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$db = new Database();
$user_id = $_SESSION['id'];

// Retrieve necessary data for the dashboard
$totalStudents = $db->getColumn("SELECT COUNT(*) FROM students");
$totalClubs = $db->getColumn("SELECT COUNT(*) FROM clubs");
$totalPresidents = $db->getColumn("SELECT COUNT(*) FROM clubpresidents");
$studentsWithClubs = $db->getColumn("SELECT COUNT(DISTINCT user_id) FROM clubmembers WHERE status = 'approved'");
$totalMemberships = $db->getColumn("SELECT COUNT(*) FROM clubmembers WHERE status = 'approved'");

// Prepare the response data
$response = [
    'totalStudents' => $totalStudents,
    'totalClubs' => $totalClubs,
    'totalPresidents' => $totalPresidents,
    'studentsWithClubs' => $studentsWithClubs,
    'totalMemberships' => $totalMemberships,
];

echo json_encode($response);
?>