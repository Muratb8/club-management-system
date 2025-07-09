<?php
// This file contains utility functions that can be used throughout the backend

/**
 * Sanitize input data to prevent XSS and SQL injection
 *
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validate email format
 *
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a random token for session management or CSRF protection
 *
 * @param int $length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format date to a more readable format
 *
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date("d-m-Y", strtotime($date));
}

/**
 * Check if the user is logged in
 *
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['id']);
}
?>