<?php
require 'includes/db.php';
require 'includes/auth.php';
require_user();

$user_id = (int) $_SESSION['user']['id'];

$cart_sql = $conn->prepare('
    SELECT
        b.id,
        b.title,
        b.price,
        ci.quantity AS cart_quantity
    FROM cart c
    JOIN cart_items ci ON c.id = ci.cart_id
    JOIN books b ON b.id = ci.book_id
    WHERE c.user_id = ?
');
$cart_sql->bind_param('i', $user_id);
$cart_sql->execute();
$cart_items = $cart_sql->get_result();

if (!$cart_items->num_rows) {
    set_flash('Your cart is empty.', 'danger');
    redirect('cart.php');
}

$cart_items->data_seek(0);

$user_stmt = $conn->prepare('
    SELECT full_name, phone
    FROM users
    WHERE id=?
');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$error = '';

function resolve_address_name($file, $value)
{
    $path = __DIR__ . '/includes/address/' . $file;
    if (!is_file($path)) {
        return trim((string) $value);
    }

    $items = json_decode(file_get_contents($path), true);
    if (!is_array($items)) {
        return trim((string) $value);
    }

    foreach ($items as $item) {
        if ((string) ($item['id'] ?? '') === (string) $value || (string) ($item['name'] ?? '') === (string) $value) {
            return trim((string) ($item['name'] ?? $value));
        }
    }

    return trim((string) $value);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $province = resolve_address_name('provinces.json', trim($_POST['province'] ?? ''));
    $district = resolve_address_name('districts.json', trim($_POST['district'] ?? ''));
    $municipality = resolve_address_name('municipalities.json', trim($_POST['municipality'] ?? ''));
    $ward = trim((string) ($_POST['ward'] ?? ''));
    $ward = preg_replace('/^ward\s*/i', '', $ward);
    $street_address = trim($_POST['address'] ?? '');

    if (!$name || !$phone || !$province || !$district || !$municipality || !$ward || !$street_address) {
        $error = 'Please complete your delivery information.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Please enter a valid 10-digit phone number.';
    } else {
        try {
            $conn->begin_transaction();
            $fresh = [];
            $order_total = 0;
            $lock = $conn->prepare('SELECT id, title, price, quantity FROM books WHERE id=? FOR UPDATE');

            $cart_items->data_seek(0);
            while ($item = $cart_items->fetch_assoc()) {
                $lock->bind_param('i', $item['id']);
                $lock->execute();
                $book = $lock->get_result()->fetch_assoc();
                $requested_qty = (int) $item['cart_quantity'];

                if (!$book || $requested_qty <= 0 || $book['quantity'] < $requested_qty) {
                    throw new Exception($item['title'] . ' has an invalid quantity or not enough stock.');
                }

                $fresh[] = ['book' => $book, 'qty' => $requested_qty];
                $order_total += $book['price'] * $requested_qty;
            }

            $insert = $conn->prepare('INSERT INTO orders(user_id, customer_name, phone, province, district, municipality, ward, street_address, total_amount) VALUES(?,?,?,?,?,?,?,?,?)');
            $insert->bind_param('isssssssd', $user_id, $name, $phone, $province, $district, $municipality, $ward, $street_address, $order_total);
            $insert->execute();
            $order_id = $conn->insert_id;

            $order_item_stmt = $conn->prepare('INSERT INTO order_items(order_id, book_id, quantity, price) VALUES(?,?,?,?)');
            $reduce_stmt = $conn->prepare('UPDATE books SET quantity=quantity-? WHERE id=?');

            foreach ($fresh as $line) {
                $id = (int) $line['book']['id'];
                $qty = (int) $line['qty'];
                $price = (float) $line['book']['price'];

                $order_item_stmt->bind_param('iiid', $order_id, $id, $qty, $price);
                $order_item_stmt->execute();

                $reduce_stmt->bind_param('ii', $qty, $id);
                $reduce_stmt->execute();
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
foreach ($cart_items as $line) {
    $total += (float) $line['price'] * (int) $line['cart_quantity'];
}
$cart_items->data_seek(0);

$page_title = 'Checkout';
require 'includes/header.php';
?>

<h1>Checkout</h1>

<div class="checkout-layout">
    <form class="order-box" method="post">
        <h2>Delivery information</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-row checkout-top-row">
            <div class="form-group checkout-name-field">
                <label>Name</label>
                <input name="name" required value="<?= e($_POST['name'] ?? $user['full_name']) ?>">
            </div>

            <div class="form-group checkout-phone-field">
                <label>Phone</label>
                <div class="phone-field">
                    <span class="phone-prefix">+977</span>
                    <input type="tel" inputmode="numeric" name="phone" required pattern="[0-9]{10}" maxlength="10" value="<?= e($_POST['phone'] ?? $user['phone']) ?>">
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Province</label>
                <select id="province" name="province" required>
                    <option value="">Select province</option>
                </select>
            </div>

            <div class="form-group">
                <label>District</label>
                <select id="district" name="district" required disabled>
                    <option value="">Select district</option>
                </select>
            </div>
        </div>

        <div class="form-row checkout-location-row">
            <div class="form-group checkout-municipality-field">
                <label>Municipality</label>
                <select id="municipality" name="municipality" required disabled>
                    <option value="">Select municipality</option>
                </select>
            </div>

            <div class="form-group checkout-ward-field">
                <label>Ward</label>
                <select id="ward" name="ward" required disabled>
                    <option value="">Select ward</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Street address</label>
            <input name="address" placeholder="House / street" required>
        </div>

        <h3>Payment method</h3>
        <p>
            <strong>Cash on Delivery (COD)</strong><br>
            <span class="muted">Pay when your order is delivered.</span>
        </p>

        <button class="btn" type="submit">Place order</button>
    </form>

    <aside class="summary">
        <h3>Your order</h3>

        <?php while ($line = $cart_items->fetch_assoc()): ?>
            <div class="summary-line">
                <span><?= e($line['title']) ?> × <?= (int) $line['cart_quantity'] ?></span>
                <span>Rs. <?= number_format((float) $line['price'] * (int) $line['cart_quantity'], 2) ?></span>
            </div>
        <?php endwhile; ?>

        <div class="summary-line total">
            <span>Total</span>
            <span>Rs. <?= number_format((float) $total, 2) ?></span>
        </div>
    </aside>
</div>

<?php require 'includes/footer.php'; ?>