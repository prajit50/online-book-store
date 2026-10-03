<?php
$page_title = 'Discover Your Next Book';
require 'includes/header.php';

$featured = $conn->query('SELECT b.*, c.name category_name FROM books b JOIN categories c ON b.category_id=c.id WHERE b.featured=1 ORDER BY b.created_at DESC LIMIT 4');
$latest = $conn->query('SELECT b.*, c.name category_name FROM books b JOIN categories c ON b.category_id=c.id ORDER BY b.created_at DESC LIMIT 4');
$categories = $conn->query('SELECT c.*, COUNT(b.id) book_count FROM categories c LEFT JOIN books b ON b.category_id=c.id GROUP BY c.id ORDER BY c.name LIMIT 6');

function home_card($book)
{
    $image = book_image_url($book['image'] ?? '');
    ?>
    <article class="book-card">
        <img class="book-cover" src="<?= e($image) ?>" alt="<?= e($book['title']) ?> cover">
        <div class="book-info">
            <h3><?= e($book['title']) ?></h3>
            <p><?= e($book['author']) ?></p>

            <div class="book-meta">
                <span class="price">Rs. <?= number_format((float) $book['price'], 2) ?></span>
                <span class="tag"><?= e($book['category_name']) ?></span>
            </div>

            <p class="<?= $book['quantity'] > 0 ? 'stock-in' : 'stock-out' ?>">
                <?= $book['quantity'] > 0 ? 'In stock' : 'Out of stock' ?>
            </p>

            <div class="card-actions">
                <a class="btn btn-light" href="book-details.php?id=<?= (int) $book['id'] ?>">Details</a>

                <?php if ($book['quantity'] > 0): ?>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>">
                        <button class="btn" type="submit">Add to cart</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}
?>

<section class="hero">
    <h1>Find the books that move your thinking.</h1>
    <p>Browse a curated selection of academic, technical, and fiction books, delivered right to your door.</p>

    <form class="search-form" action="books.php" method="get">
        <input name="search" placeholder="Search by title, author, or category">
        <button class="btn" type="submit">Search books</button>
    </form>
</section>

<section>
    <div class="section-head">
        <h2>Featured books</h2>
        <a href="books.php">View all →</a>
    </div>

    <div class="book-grid">
        <?php while ($book = $featured->fetch_assoc()): ?>
            <?php home_card($book); ?>
        <?php endwhile; ?>
    </div>
</section>

<section>
    <div class="section-head">
        <h2>Browse by category</h2>
    </div>

    <div class="category-grid">
        <?php while ($category = $categories->fetch_assoc()): ?>
            <a class="category-card" href="books.php?category=<?= (int) $category['id'] ?>">
                <?= e($category['name']) ?>
                <small class="muted"><br><?= (int) $category['book_count'] ?> books</small>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<section>
    <div class="section-head">
        <h2>Latest arrivals</h2>
        <a href="books.php">View all →</a>
    </div>

    <div class="book-grid">
        <?php while ($book = $latest->fetch_assoc()): ?>
            <?php home_card($book); ?>
        <?php endwhile; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>