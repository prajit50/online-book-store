<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();
$page_title = 'My Orders';
require 'includes/header.php';
if (isset($_GET['success'])): ?>
    <section class="success-page">
        <div class="success-mark">✓</div>
        <h1>Order placed successfully</h1>
        <p class="muted">Thank you! We will contact you before delivery.</p><a class="btn" href="orders.php">View order
            history</a>
    </section>
<?php else:
    $stmt = $conn->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
    $stmt->bind_param('i', $_SESSION['user']['id']);
    $stmt->execute();
    $orders = $stmt->get_result(); ?>
    <h1>Order history</h1><?php if (!$orders->num_rows): ?>
        <div class="empty">You have not placed an order yet.<br><br><a class="btn" href="books.php">Browse books</a></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody><?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td><?= e($order['payment_method']) ?></td>
                            <td><span class="status status-<?= strtolower($order['status']) ?>"><?= e($order['status']) ?></span>
                            </td>
                            <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                        </tr><?php endwhile; ?>
                </tbody>
            </table>
        </div><?php endif; endif;
require 'includes/footer.php'; ?>