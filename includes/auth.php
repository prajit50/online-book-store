<?php
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header('Location: ' . $url); exit; }
function is_logged_in() { return isset($_SESSION['user']); }
function is_user() { return is_logged_in() && $_SESSION['user']['role'] === 'user'; }
function is_admin() { return is_logged_in() && $_SESSION['user']['role'] === 'admin'; }
function require_login() {
    if (!is_logged_in()) { $_SESSION['flash'] = 'Please log in to continue.'; redirect('login.php'); }
}
function require_user() {
    if (!is_user()) { $_SESSION['flash'] = 'Customer access required.'; redirect('login.php'); }
}
function require_admin() {
    if (!is_admin()) { $_SESSION['flash'] = 'Administrator access required.'; redirect('../login.php'); }
}
function add_guest_cart_item($book_id, $quantity = 1) {
    $book_id = (int) $book_id;
    $quantity = max(1, (int) $quantity);

    if ($book_id <= 0) {
        return;
    }

    if (!isset($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }

    $_SESSION['guest_cart'][$book_id] = ($_SESSION['guest_cart'][$book_id] ?? 0) + $quantity;
}
function merge_guest_cart_to_user($conn, $user_id) {
    if (empty($_SESSION['guest_cart'])) {
        return;
    }

    $cart_stmt = $conn->prepare('SELECT id FROM cart WHERE user_id=?');
    $cart_stmt->bind_param('i', $user_id);
    $cart_stmt->execute();
    $cart = $cart_stmt->get_result()->fetch_assoc();

    if ($cart) {
        $cart_id = (int) $cart['id'];
    } else {
        $insert_cart = $conn->prepare('INSERT INTO cart(user_id) VALUES(?)');
        $insert_cart->bind_param('i', $user_id);
        $insert_cart->execute();
        $cart_id = (int) $conn->insert_id;
    }

    foreach ($_SESSION['guest_cart'] as $book_id => $quantity) {
        $book_id = (int) $book_id;
        $quantity = max(1, (int) $quantity);

        $stock = $conn->prepare('SELECT quantity FROM books WHERE id=?');
        $stock->bind_param('i', $book_id);
        $stock->execute();
        $book = $stock->get_result()->fetch_assoc();

        if (!$book || $book['quantity'] <= 0) {
            continue;
        }

        $existing = $conn->prepare('SELECT id, quantity FROM cart_items WHERE cart_id=? AND book_id=?');
        $existing->bind_param('ii', $cart_id, $book_id);
        $existing->execute();
        $item = $existing->get_result()->fetch_assoc();

        $new_qty = $quantity;
        if ($item) {
            $new_qty = (int) $item['quantity'] + $quantity;
            $new_qty = min($new_qty, (int) $book['quantity']);
            $update = $conn->prepare('UPDATE cart_items SET quantity=? WHERE id=?');
            $update->bind_param('ii', $new_qty, $item['id']);
            $update->execute();
        } else {
            $new_qty = min($quantity, (int) $book['quantity']);
            $insert = $conn->prepare('INSERT INTO cart_items(cart_id, book_id, quantity) VALUES(?,?,?)');
            $insert->bind_param('iii', $cart_id, $book_id, $new_qty);
            $insert->execute();
        }
    }

    unset($_SESSION['guest_cart']);
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
function book_image_url($image) {
    $image = trim((string) $image);
    if ($image === '') return 'images/book-placeholder.svg';
    if (filter_var($image, FILTER_VALIDATE_URL)) return $image;

    $normalized = ltrim($image, '/');
    $normalized = preg_replace('#^uploads/?#i', '', $normalized);

    if ($normalized === '') return 'images/book-placeholder.svg';
    if (strpos($normalized, 'images/') === 0) return $normalized;

    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $base_dir = rtrim(dirname($script), '/');

    if (preg_match('#/admin$#i', $base_dir)) {
        $base_dir = dirname($base_dir);
    }

    $base_url = rtrim($base_dir, '/');
    if ($base_url === '' || $base_url === '.') {
        return '/uploads/' . $normalized;
    }

    return $base_url . '/uploads/' . $normalized;
}

function upload_cover($field, $existing = '') {
    $image_url = trim($_POST['image_url'] ?? '');
    if ($image_url !== '') {
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) return false;
        if (!preg_match('/\.(jpe?g|png|webp)(\?.*)?$/i', $image_url)) return false;
        return $image_url;
    }

    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return $existing;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    $max_upload_size = 5 * 1024 * 1024;
    if (!isset($allowed[$mime]) || $_FILES[$field]['size'] > $max_upload_size) return false;
    $upload_dir = __DIR__ . '/../uploads';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true) && !is_dir($upload_dir)) {
        return false;
    }
    $name = uniqid('book_', true) . '.' . $allowed[$mime];
    $path = $upload_dir . '/' . $name;
    return move_uploaded_file($_FILES[$field]['tmp_name'], $path) ? $name : false;
}
?>
