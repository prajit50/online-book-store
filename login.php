<?php
require 'includes/db.php';
require 'includes/auth.php';
if (is_logged_in())
    redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password)
        $error = 'Email and password are required.';
    else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email=?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['full_name'], 'role' => $user['role']];
            set_flash('Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php');
        }
        $error = 'Invalid email or password.';
    }
}
$page_title = 'Login';
require 'includes/header.php'; ?>
<form class="form-card" method="post" data-validate>
    <h1>Welcome back</h1>
    <p class="muted">Log in to manage your orders and cart.</p><?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <div class="form-group"><label>Email</label><input type="email" name="email" required
            value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div><button
        class="btn" type="submit">Login</button>
    <p class="form-note">New here? <a href="register.php"><u>Create an account</u></a></p>
</form>
<?php require 'includes/footer.php'; ?>