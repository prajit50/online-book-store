<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();
$user_id = $_SESSION['user']['id'];
$cart_sql = $conn->prepare('SELECT b.*,ci.quantity cart_quantity FROM cart c JOIN cart_items ci ON c.id=ci.cart_id JOIN books b ON b.id=ci.book_id WHERE c.user_id=?');
$cart_sql->bind_param('i', $user_id);
$cart_sql->execute();
$cart_items = $cart_sql->get_result();
if (!$cart_items->num_rows) {
    set_flash('Your cart is empty.', 'danger');
    redirect('cart.php');
}
$cart_items->data_seek(0);
$user_stmt = $conn->prepare('SELECT * FROM users WHERE id=?');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if (!$name || !$phone || !$address)
        $error = 'Please complete your delivery details.';
    else {
        try {
            $conn->begin_transaction();
            $fresh = [];
            $total = 0;
            $lock = $conn->prepare('SELECT id,title,price,quantity FROM books WHERE id=? FOR UPDATE');
            $cart_items->data_seek(0);
            while ($item = $cart_items->fetch_assoc()) {
                $lock->bind_param('i', $item['id']);
                $lock->execute();
                $book = $lock->get_result()->fetch_assoc();
                if (!$book || $book['quantity'] < $item['cart_quantity'])
                    throw new Exception($item['title'] . ' no longer has enough stock.');
                $fresh[] = ['book' => $book, 'qty' => $item['cart_quantity']];
                $total += $book['price'] * $item['cart_quantity'];
            }
            $insert = $conn->prepare("INSERT INTO orders(user_id,customer_name,phone,address,total_amount,payment_method,status) VALUES(?,?,?,?,?,'COD','Pending')");
            $insert->bind_param('isssd', $user_id, $name, $phone, $address, $total);
            $insert->execute();
            $order_id = $conn->insert_id;
            $oi = $conn->prepare('INSERT INTO order_items(order_id,book_id,quantity,price) VALUES(?,?,?,?)');
            $reduce = $conn->prepare('UPDATE books SET quantity=quantity-? WHERE id=?');
            foreach ($fresh as $line) {
                $id = $line['book']['id'];
                $price = $line['book']['price'];
                $qty = $line['qty'];
                $oi->bind_param('iiid', $order_id, $id, $qty, $price);
                $oi->execute();
                $reduce->bind_param('ii', $qty, $id);
                $reduce->execute();
            }
            $clear = $conn->prepare('DELETE ci FROM cart_items ci JOIN cart c ON ci.cart_id=c.id WHERE c.user_id=?');
            $clear->bind_param('i', $user_id);
            $clear->execute();
            $conn->commit();
            redirect('orders.php?success=1');
        } catch (Exception $ex) {
            $conn->rollback();
            $error = $ex->getMessage();
        }
    }
}
$total = 0;
$cart_items->data_seek(0);
foreach ($cart_items as $line)
    $total += $line['price'] * $line['cart_quantity'];
$cart_items->data_seek(0);
$page_title = 'Checkout';
require 'includes/header.php'; ?>
<h1>Checkout</h1>
<div class="checkout-layout">
    <form class="order-box" method="post">
        <h2>Delivery information</h2><?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <div class="form-group"><label>Name</label><input name="name" required
                value="<?= e($_POST['name'] ?? $user['full_name']) ?>"></div>
        <div class="form-group"><label>Phone</label><input name="phone" required
                value="<?= e($_POST['phone'] ?? $user['phone']) ?>"></div>
        <div class="form-group"><label>Address</label><textarea name="address"
                required><?= e($_POST['address'] ?? $user['address']) ?></textarea></div>
        <h3>Payment method</h3>
        <p><strong>Cash on Delivery (COD)</strong><br><span class="muted">Pay when your order is delivered.</span></p>
        <button class="btn">Place order</button>
    </form>
    <aside class="summary">
        <h3>Your order</h3><?php while ($line = $cart_items->fetch_assoc()): ?>
            <div class="summary-line"><span><?= e($line['title']) ?> × <?= $line['cart_quantity'] ?></span><span>Rs.
                    <?= number_format($line['price'] * $line['cart_quantity'], 2) ?></span></div><?php endwhile; ?>
        <div class="summary-line total"><span>Total</span><span>Rs. <?= number_format($total, 2) ?></span></div>
    </aside>
</div>
<?php require 'includes/footer.php'; ?>