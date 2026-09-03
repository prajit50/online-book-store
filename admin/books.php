<?php
$page_title = 'Manage Books';
require '../includes/admin-header.php';
if (isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    try {
        $stmt = $conn->prepare('DELETE FROM books WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        set_flash('Book deleted.');
    } catch (mysqli_sql_exception $e) {
        set_flash('This book cannot be deleted because it appears in an order.', 'danger');
    }
    redirect('books.php');
}
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare('SELECT b.*,c.name category_name FROM books b JOIN categories c ON b.category_id=c.id WHERE b.title LIKE ? OR b.author LIKE ? ORDER BY b.created_at DESC');
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $books = $stmt->get_result();
} else
    $books = $conn->query('SELECT b.*,c.name category_name FROM books b JOIN categories c ON b.category_id=c.id ORDER BY b.created_at DESC');
?>
<div class="admin-actions">
    <div>
        <h1>Books</h1>
        <p class="muted">Add, edit, and organize your catalog.</p>
    </div><a class="btn" href="add-book.php">+ Add book</a>
</div>
<form class="filters" method="get"><input name="search" value="<?= e($search) ?>"
        placeholder="Search title or author"><button class="btn">Search</button></form>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Cover</th>
                <th>Book</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody><?php while ($b = $books->fetch_assoc()): ?>
                <tr>
                    <td><img class="thumbnail"
                            src="<?= $b['image'] ? '../uploads/' . e($b['image']) : '../images/book-placeholder.svg' ?>" alt="">
                    </td>
                    <td><strong><?= e($b['title']) ?></strong><br><small><?= e($b['author']) ?></small></td>
                    <td><?= e($b['category_name']) ?></td>
                    <td>Rs. <?= number_format($b['price'], 2) ?></td>
                    <td><?= $b['quantity'] ?></td>
                    <td><a class="btn btn-light" href="edit-book.php?id=<?= $b['id'] ?>">Edit</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Delete this book?')"><input
                                type="hidden" name="delete_id" value="<?= $b['id'] ?>"><button
                                class="btn btn-danger">Delete</button></form>
                    </td>
                </tr><?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/admin-footer.php'; ?>