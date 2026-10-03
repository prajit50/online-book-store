<?php
$page_title = 'Dashboard';
require '../includes/admin-header.php';
function stat_value($conn, $sql)
{
    $r = $conn->query($sql);
    return (int) $r->fetch_row()[0];
}
$total_books = stat_value($conn, 'SELECT COUNT(*) FROM books');
$total_users = stat_value($conn, "SELECT COUNT(*) FROM users WHERE role='user'");
$total_orders = stat_value($conn, 'SELECT COUNT(*) FROM orders');
$pending = stat_value($conn, "SELECT COUNT(*) FROM orders WHERE status='Pending'");
$recent = $conn->query('SELECT o.*,u.full_name FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 5');
?>
<h1>Dashboard</h1>
<p class="muted">Overview of your bookstore.</p>
<div class="stats">
    <div class="stat-card">
        <p>Total books</p><strong><?= $total_books ?></strong>
    </div>
    <div class="stat-card">
        <p>Total users</p><strong><?= $total_users ?></strong>
    </div>
    <div class="stat-card">
        <p>Total orders</p><strong><?= $total_orders ?></strong>
    </div>
    <div class="stat-card">
        <p>Pending orders</p><strong><?= $pending ?></strong>
    </div>
</div>
<div class="admin-actions">
    <h2>Recent orders</h2><a class="btn" href="orders.php">Manage orders</a>
</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody><?php while ($o = $recent->fetch_assoc()): ?>
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
                    <td>#<?= $o['id'] ?></td>
                    <td>
                        <strong><?= e($o['full_name']) ?></strong><br>
                        <small><?= e($o['phone'] ?? '') ?></small>
                        <?php if ($delivery_address): ?>
                            <br><small><?= e($delivery_address) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                    <td><span class="status status-<?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
                    <td>Rs. <?= number_format($o['total_amount'], 2) ?></td>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/admin-footer.php';?>