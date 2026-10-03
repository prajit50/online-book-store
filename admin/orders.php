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
        <h1>Orders</h1>
        <p class="muted">Review customer orders and update delivery progress.</p>
    </div>
</div>
<form class="filters" method="get"><select name="status">
        <option value="">All statuses</option><?php foreach ($valid as $s): ?>
            <option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
    </select><button class="btn">Filter</button></form>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer & delivery</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders && $orders->num_rows === 0): ?>
                <tr>
                    <td colspan="5" class="muted">No orders found yet.</td>
                </tr>
            <?php else: ?>
                <?php while ($o = $orders->fetch_assoc()): ?>
                    <?php
                    $address_parts = [
                        $o['province'] ?? '',
                        $o['district'] ?? '',
                        $o['municipality'] ?? '',
                        ($o['ward'] ?? '') !== '' ? 'Ward ' . $o['ward'] : '',
                        $o['street_address'] ?? '',
                    ];
                    $address_parts = array_values(array_filter($address_parts, function ($part) {
                        return $part !== '' && $part !== null;
                    }));
                    $delivery_address = implode(', ', $address_parts);
                    ?>
                    <tr>
                        <td>#<?= $o['id'] ?><br><small><?= e($o['payment_method'] ?? 'COD') ?></small></td>
                        <td>
                            <strong><?= e($o['customer_name'] ?? '') ?></strong><br>
                            <small><?= e($o['phone'] ?? '') ?></small>
                            <?php if ($delivery_address): ?>
                                <br><small><?= e($delivery_address) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                        <td>Rs. <?= number_format((float) ($o['total_amount'] ?? 0), 2) ?></td>
                        <td>
                            <form method="post"><input type="hidden" name="order_id" value="<?= $o['id'] ?>"><select
                                    name="status"><?php foreach ($valid as $s): ?>
                                        <option <?= ($o['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                                </select><button class="btn" style="margin-top:6px">Update</button></form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/admin-footer.php'; ?>