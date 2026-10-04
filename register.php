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
    } elseif (!preg_match('/^[\p{L}][\p{L}\p{M}\s.\'-]*$/u', $name) || preg_match('/\d/', $name)) {
        $error = 'Name can only contain letters, spaces, and basic punctuation. Numbers are not allowed.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone number must be exactly 10 digits.';
    } elseif (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
        $error = 'Enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email=?');
        $check->bind_param('s', $email);
        $check->execute();

        if ($check->get_result()->num_rows) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $stmt = $conn->prepare('INSERT INTO users(full_name, email, phone, password, role) VALUES(?,?,?,?,?)');
            $stmt->bind_param('sssss', $name, $email, $phone, $hash, $role);
            $stmt->execute();

            $user_id = (int) $conn->insert_id;

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user_id,
                'name' => $name,
                'role' => 'user'
            ];

            merge_guest_cart_to_user($conn, $user_id);

            set_flash('Welcome to BookNest!');
            redirect('index.php');
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

