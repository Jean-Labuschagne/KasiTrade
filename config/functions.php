<?php
// KasiTrade Helper Functions

require_once 'database.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Check permission
function hasPermission($perm) {
    return isset($_SESSION['permissions']) && in_array($perm, $_SESSION['permissions']);
}

// Redirect with message
function redirect($url, $msg = '', $type = 'info') {
    if ($msg) {
        $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    }
    header("Location: $url");
    exit;
}

// Display flash message
function flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class='alert alert-{$f['type']} alert-dismissible fade show' role='alert'>
                {$f['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

// Sanitize input
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Compress image to WebP
function compressImage($source, $maxWidth = 800, $quality = 80) {
    $info = getimagesize($source);
    if (!$info) return false;

    $type = $info[2];
    switch ($type) {
        case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($source); break;
        case IMAGETYPE_PNG:  $image = imagecreatefrompng($source); break;
        case IMAGETYPE_GIF:  $image = imagecreatefromgif($source); break;
        default: return false;
    }

    $width = imagesx($image);
    $height = imagesy($image);

    if ($width > $maxWidth) {
        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = $height * $ratio;
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $newImage;
    }

    ob_start();
    imagewebp($image, null, $quality);
    $output = ob_get_clean();
    imagedestroy($image);
    return $output;
}

// Format price
function formatPrice($price) {
    return 'R' . number_format($price, 2);
}

// Time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', $time);
}
?>