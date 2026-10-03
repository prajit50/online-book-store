<?php
$page_title = 'Book Catalog';
require 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$category_id = (int) ($_GET['category'] ?? 0);
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');

$sql = 'SELECT b.*, c.name category_name FROM books b JOIN categories c ON b.category_id=c.id WHERE 1=1';
$types = '';
$params = [];

if ($search !== '') {
    $sql .= ' AND (b.title LIKE ? OR b.author LIKE ? OR c.name LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'sss';
    $params = [$like, $like, $like];
}

if ($category_id) {
    $sql .= ' AND b.category_id=?';
    $types .= 'i';
    $params[] = $category_id;
}

$sql .= ' ORDER BY b.created_at DESC';
$stmt = $conn->prepare($sql);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$books = $stmt->get_result();
?>

<div class="section-head">
    <div>
        <h1>Book catalog</h1>
        <p class="muted">Find your next great read.</p>
    </div>
</div>

<form class="filters" method="get">
    <input name="search" value="<?= e($search) ?>" placeholder="Search title, author, or category">

    <select name="category">
        <option value="">All categories</option>
        <?php while ($c = $categories->fetch_assoc()): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $category_id === (int) $c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button class="btn" type="submit">Filter</button>
</form>

<?php if (!$books->num_rows): ?>
    <div class="empty">No books matched your search.</div>
<?php else: ?>
    <div class="book-grid">
        <?php while ($book = $books->fetch_assoc()): ?>
            <?php $image = book_image_url($book['image'] ?? ''); ?>
            <article class="book-card">
                <img class="book-cover" src="<?= e($image) ?>" alt="<?= e($book['title']) ?> cover">

                <div class="book-info">
                    <h3><?= e($book['title']) ?></h3>
                    <p><?= e($book['author']) ?></p>

                    <div class="book-meta">
                        <span class="price">Rs. <?= number_format((float) $book['price'], 2) ?></span>
                        <span class="tag"><?= e($book['category_name']) ?></span>
                    </div>

                    <p class="<?= $book['quantity'] ? 'stock-in' : 'stock-out' ?>">
                        <?= $book['quantity'] ? 'In stock' : 'Out of stock' ?>
                    </p>

                    <div class="card-actions">
                        <a class="btn btn-light" href="book-details.php?id=<?= (int) $book['id'] ?>">Details</a>

                        <?php if ($book['quantity']): ?>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>">
                                <button class="btn" type="submit">Add to cart</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>