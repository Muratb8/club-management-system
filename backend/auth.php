<?php
session_start();
require_once 'database.php';

function login($email, $password) {
    $db = new Database();
    $user = $db->getRow("SELECT * FROM users WHERE email = ?", [$email]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../public/login.php");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}
?>