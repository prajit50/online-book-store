<?php require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_admin();
$page_title = $page_title ?? 'Admin'; ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($page_title) ?> | BookNest Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="admin-body">
    <div class="admin-layout">
        <aside class="sidebar"><a class="logo" href="dashboard.php">Book<span>Nest</span></a>
            <p class="admin-caption">ADMIN PANEL</p>
            <nav><a href="dashboard.php">Dashboard</a><a href="books.php">Books</a><a
                    href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="users.php">Users</a><a
                    href="../logout.php">Logout</a></nav>
        </aside>
        <main class="admin-main">
            <header class="admin-top"><button class="sidebar-toggle"
                    aria-label="Toggle sidebar">☰</button><span><?= e($_SESSION['user']['name']) ?></span><a
                    href="../index.php">View Store</a></header>
            <div class="admin-content"><?php show_flash(); ?>