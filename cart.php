<?php
require 'includes/db.php';
require 'includes/auth.php';

if (!is_logged_in()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $book_id = (int) ($_POST['book_id'] ?? 0);

        if ($action === 'add' && $book_id > 0) {
            add_guest_cart_item($book_id, 1);
            set_flash('Please register to complete your purchase.', 'warning');
            redirect('register.php');
        }
    }

    $_SESSION['flash'] = 'Please register to continue.';
    $_SESSION['flash_type'] = 'warning';
    redirect('register.php');
}

function get_cart_id($conn, $user_id)
{
    $stmt = $conn->prepare('SELECT id FROM cart WHERE user_id=?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cart = $stmt->get_result()->fetch_assoc();

    if ($cart) {
        return (int) $cart['id'];
    }

    $insert = $conn->prepare('INSERT INTO cart(user_id) VALUES(?)');
    $insert->bind_param('i', $user_id);
    $insert->execute();

    return (int) $conn->insert_id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $book_id = (int) ($_POST['book_id'] ?? 0);
    $user_id = (int) $_SESSION['user']['id'];
    $cart_id = get_cart_id($conn, $user_id);

    if ($action === 'add' && $book_id) {
        $stock = $conn->prepare('SELECT quantity, title FROM books WHERE id=?');
        $stock->bind_param('i', $book_id);
        $stock->execute();
        $book = $stock->get_result()->fetch_assoc();

        if (!$book || $book['quantity'] <= 0) {
            set_flash('This book is currently out of stock.', 'danger');
        } else {
            $item = $conn->prepare('SELECT id, quantity FROM cart_items WHERE cart_id=? AND book_id=?');
            $item->bind_param('ii', $cart_id, $book_id);
            $item->execute();
            $existing = $item->get_result()->fetch_assoc();

            if ($existing) {
                if ($existing['quantity'] < $book['quantity']) {
                    $update = $conn->prepare('UPDATE cart_items SET quantity=quantity+1 WHERE id=?');
                    $update->bind_param('i', $existing['id']);
                    $update->execute();
                    set_flash('Quantity updated.');
                } else {
                    set_flash('Only ' . $book['quantity'] . ' copies are available.', 'danger');
                }
            } else {
                $insert = $conn->prepare('INSERT INTO cart_items(cart_id, book_id, quantity) VALUES(?,?,1)');
                $insert->bind_param('ii', $cart_id, $book_id);
                $insert->execute();
                set_flash('Book added to your cart.');
            }
        }
    }

    if ($action === 'update' && $book_id) {
        $quantity = max(0, (int) ($_POST['quantity'] ?? 0));

        if ($quantity === 0) {
            $delete = $conn->prepare('DELETE FROM cart_items WHERE cart_id=? AND book_id=?');
            $delete->bind_param('ii', $cart_id, $book_id);
            $delete->execute();
        } else {
            $update = $conn->prepare('UPDATE cart_items ci JOIN books b ON ci.book_id=b.id SET ci.quantity=LEAST(?, b.quantity) WHERE ci.cart_id=? AND ci.book_id=?');
            $update->bind_param('iii', $quantity, $cart_id, $book_id);
            $update->execute();
        }

        set_flash('Cart updated.');
    }

    if ($action === 'remove') {
        $delete = $conn->prepare('DELETE FROM cart_items WHERE cart_id=? AND book_id=?');
        $delete->bind_param('ii', $cart_id, $book_id);
        $delete->execute();
        set_flash('Item removed from cart.');
    }

    redirect('cart.php');
}

$page_title = 'Shopping Cart';
require 'includes/header.php';

$stmt = $conn->prepare('
    SELECT
        b.id,
        b.title,
        b.author,
        b.price,
        b.quantity,
        ci.quantity AS cart_quantity,
        (b.price * ci.quantity) AS line_total
    FROM cart c
    JOIN cart_items ci ON c.id = ci.cart_id
    JOIN books b ON b.id = ci.book_id
    WHERE c.user_id = ?
');
$stmt->bind_param('i', $_SESSION['user']['id']);
$stmt->execute();
$items = $stmt->get_result();

$total = 0;
foreach ($items as $row) {
    $total += (float) $row['line_total'];
}
$items->data_seek(0);
?>

<h1>Shopping cart</h1>

<?php if (!$items->num_rows): ?>
    <div class="empty">
        Your cart is empty.<br><br>
        <a class="btn" href="books.php">Browse books</a>
    </div>
<?php else: ?>
    <div class="cart-layout">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['title']) ?></strong><br>
                                <small class="muted"><?= e($item['author']) ?></small>
                            </td>
                            <td>Rs. <?= number_format((float) $item['price'], 2) ?></td>
                            <td>
                                <form class="quantity-form" method="post">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="book_id" value="<?= (int) $item['id'] ?>">
                                    <input type="number" name="quantity" min="0" max="<?= (int) $item['quantity'] ?>" value="<?= (int) $item['cart_quantity'] ?>">
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                            <td>Rs. <?= number_format((float) $item['line_total'], 2) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="book_id" value="<?= (int) $item['id'] ?>">
                                    <button class="btn btn-danger" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <aside class="summary">
            <h3>Order summary</h3>
            <div class="summary-line">
                <span>Subtotal</span>
                <span>Rs. <?= number_format((float) $total, 2) ?></span>
            </div>
            <div class="summary-line total">
                <span>Total</span>
                <span>Rs. <?= number_format((float) $total, 2) ?></span>
            </div>
            <a class="btn" href="checkout.php">Proceed to checkout</a>
        </aside>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>