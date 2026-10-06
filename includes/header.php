<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? 'BookNest';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> | BookNest</title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="logo" href="index.php">Book<span>Nest</span></a>
            <button class="nav-toggle" aria-label="Toggle navigation">☰</button>
            <nav class="main-nav">
                <a href="index.php">Home</a>
                <a href="books.php">Books</a>

                <?php if (is_logged_in()): ?>
                    <?php if (is_user()): ?>
                        <a href="cart.php">Cart <small>(<?= cart_count($conn) ?>)</small></a>
                        <a href="orders.php">Orders</a>
                    <?php endif; ?>
                    <?php if (is_admin()): ?>
                        <a href="admin/dashboard.php">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a class="nav-register" href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="page-content">
        <div class="container"><?php show_flash(); ?>
