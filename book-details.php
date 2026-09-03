<?php
$page_title = 'Book Details';
require 'includes/header.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT b.*,c.name category_name FROM books b JOIN categories c ON b.category_id=c.id WHERE b.id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
if (!$book) {
    echo '<div class="empty">Book not found.</div>';
    require 'includes/footer.php';
    exit;
}
$image = $book['image'] ? 'uploads/' . e($book['image']) : 'images/book-placeholder.svg';
?>
<section class="details"><img class="details-image" src="<?= $image ?>" alt="<?= e($book['title']) ?> cover">
    <div>
        <p class="tag"><?= e($book['category_name']) ?></p>
        <h1><?= e($book['title']) ?></h1>
        <p class="muted">by <?= e($book['author']) ?></p>
        <p class="price">Rs. <?= number_format($book['price'], 2) ?></p>
        <p class="description"><?= nl2br(e($book['description'])) ?></p>
        <div class="detail-list">
            <p><strong>Publisher:</strong> <?= e($book['publisher']) ?></p>
            <p><strong>ISBN:</strong> <?= e($book['isbn']) ?></p>
            <p><strong>Availability:</strong> <span
                    class="<?= $book['quantity'] ? 'stock-in' : 'stock-out' ?>"><?= $book['quantity'] ? $book['quantity'] . ' available' : 'Out of stock' ?></span>
            </p>
        </div><?php if ($book['quantity']): ?>
            <form action="cart.php" method="post"><input type="hidden" name="action" value="add"><input type="hidden"
                    name="book_id" value="<?= $book['id'] ?>"><button class="btn">Add to cart</button></form><?php endif; ?>
    </div>
</section>
<?php require 'includes/footer.php'; ?>