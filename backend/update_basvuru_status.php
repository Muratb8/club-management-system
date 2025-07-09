<?php
session_start();
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'club_president' && $_SESSION['role'] !== 'developer')) {
    echo json_encode(['status' => 'error', 'message' => 'Yetkisiz']);
    exit();
}

$id = $_POST['id'];
$status = $_POST['status'];

$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']);
    exit();
}

$stmt = $conn->prepare("UPDATE clubmembers SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Durum güncellendi']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Güncellenemedi']);
}

$conn->close();
?>
