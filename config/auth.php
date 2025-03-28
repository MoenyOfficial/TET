<?php
// Generate a random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Generate token expiry date (24 hours from now)
function generateTokenExpiry() {
    return date('Y-m-d H:i:s', strtotime('+24 hours'));
}

// Validate token expiry
function isTokenValid($expiry) {
    $now = new DateTime();
    $expiry_date = new DateTime($expiry);
    return $now < $expiry_date;
}
?>