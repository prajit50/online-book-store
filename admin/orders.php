<?php
$page_title = 'Manage Orders';
require '../includes/admin-header.php';
$valid = ['Pending', 'Processing', 'Delivered', 'Cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, $valid, true)) {
        $stmt = $conn->prepare('UPDATE orders SET status=? WHERE id=?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        set_flash('Order status updated.');
    }
    redirect('orders.php');
}
$status = $_GET['status'] ?? '';
$sql = 'SELECT o.*,u.full_name,u.email FROM orders o JOIN users u ON o.user_id=u.id';
if (in_array($status, $valid, true)) {
    $stmt = $conn->prepare($sql . ' WHERE o.status=? ORDER BY o.created_at DESC');
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $orders = $stmt->get_result();
} else
    $orders = $conn->query($sql . ' ORDER BY o.created_at DESC');
?>
<div class="admin-actions">
<div>