<?php
require 'includes/db.php';
require 'includes/auth.php';

if (is_logged_in()) {
    redirect($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$phone || !$password) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone number must be exactly 10 digits.';
    } elseif (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
        $error = 'Enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email=?');
        $check->bind_param('s', $email);
        $check->execute();

        if ($check->get_result()->num_rows) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users(full_name, email, phone, password) VALUES(?,?,?,?)');
            $stmt->bind_param('ssss', $name, $email, $phone, $hash);
            $stmt->execute();

            set_flash('Account created. Please log in.');
            redirect('login.php');
        }
    }
}

$page_title = 'Create Account';
require 'includes/header.php';
?>

<form class="form-card auth-form" method="post" data-validate>
    <h1>Create account</h1>
    <p class="muted">Join BookNest to place orders.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label>Full Name</label>
        <input name="name" required value="<?= e($_POST['name'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}" value="<?= e($_POST['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Phone</label>

        <div class="phone-field">
            <span class="phone-prefix">+977</span>
            <input
                type="tel"
                inputmode="numeric"
                name="phone"
                required
                pattern="[0-9]{10}"
                maxlength="10"
                value="<?= e($_POST['phone'] ?? '') ?>"
            >
        </div>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
    </div>

    <button class="btn" type="submit">Register</button>

    <p class="form-note">Already have an account? <a href="login.php"><u>Log in</u></a></p>
</form>

<?php require 'includes/footer.php'; ?> 

