<?php require 'includes/db.php';
session_unset();
session_destroy();
session_start();
$_SESSION['flash'] = 'You have been logged out.';
$_SESSION['flash_type'] = 'success';
header('Location: index.php');
exit; ?>