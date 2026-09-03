<?php
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }
function is_logged_in() { return isset($_SESSION['user']); }
function is_admin() { return is_logged_in() && $_SESSION['user']['role'] === 'admin'; }
function require_login() {
    if (!is_logged_in()) { $_SESSION['flash'] = 'Please log in to continue.'; redirect('login.php'); }
}
function require_admin() {
    if (!is_admin()) { $_SESSION['flash'] = 'Administrator access required.'; redirect('../login.php'); }
}
function set_flash($message, $type = 'success') { $_SESSION['flash'] = $message; $_SESSION['flash_type'] = $type; }
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        echo '<div class="alert alert-' . e($type) . '">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash'], $_SESSION['flash_type']);
    }
}
function cart_count($conn) {
    if (!is_logged_in()) return 0;
    $stmt = $conn->prepare('SELECT COALESCE(SUM(ci.quantity), 0) total FROM cart c JOIN cart_items ci ON c.id=ci.cart_id WHERE c.user_id=?');
    $stmt->bind_param('i', $_SESSION['user']['id']); $stmt->execute();
    return (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
}
function upload_cover($field, $existing = '') {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return $existing;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime]) || $_FILES[$field]['size'] > 2 * 1024 * 1024) return false;
    $name = uniqid('book_', true) . '.' . $allowed[$mime];
    $path = __DIR__ . '/../uploads/' . $name;
    return move_uploaded_file($_FILES[$field]['tmp_name'], $path) ? $name : false;
}
?>
