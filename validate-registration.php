<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['valid' => false, 'message' => 'Method not allowed.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
    echo json_encode(['valid' => false, 'message' => 'Enter a valid email address.']);
    exit;
}

require __DIR__ . '/includes/db.php';

try {
    $check = $conn->prepare('SELECT id FROM users WHERE email=?');
    $check->bind_param('s', $email);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    echo json_encode([
        'valid' => !$exists,
        'message' => $exists ? 'This email is already registered.' : '',
    ]);
} catch (mysqli_sql_exception $exception) {
    error_log('Registration email validation failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Email validation is temporarily unavailable. Please try again.']);
}
