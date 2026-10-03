<?php
$page_title = 'Add Book';
require '../includes/admin-header.php';

$error = '';
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $category = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!$title || !$author || !$category || $price <= 0 || $quantity < 0) {
        $error = 'Please complete all required fields with valid values.';
    } else {
        $image = upload_cover('image');

        if ($image === false) {
            $error = 'Upload a JPG, PNG, or WEBP image under 5 MB.';
        } else {
            $stmt = $conn->prepare('INSERT INTO books (title, author, isbn, publisher, category_id, price, quantity, description, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssidissi', $title, $author, $isbn, $publisher, $category, $price, $quantity, $description, $image, $featured);
            $stmt->execute();

            set_flash('Book added successfully.');
            redirect('books.php');
        }
    }
}
?>

<div class="admin-actions">
    <h1>Add book</h1>
    <a href="books.php">← Back to books</a>
</div>

<form class="admin-form" method="post" enctype="multipart/form-data">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-row">
        <div class="form-group">
            <label>Title *</label>
            <input name="title" required value="<?= e($_POST['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Author *</label>
            <input name="author" required value="<?= e($_POST['author'] ?? '') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>ISBN</label>
            <input name="isbn" value="<?= e($_POST['isbn'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Publisher</label>
            <input name="publisher" value="<?= e($_POST['publisher'] ?? '') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Category *</label>
            <select name="category_id" required>
                <option value="">Select category</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Cover image</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">

            <label style="margin-top:12px; display:block;">
                Or paste image URL
            </label>

            <input
                type="url"
                name="image_url"
                placeholder="https://example.com/book-cover.jpg"
                value="<?= e($_POST['image_url'] ?? '') ?>"
            >
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Price (Rs.) *</label>
            <input type="number" min="0.01" step="0.01" name="price" required value="<?= e($_POST['price'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Quantity *</label>
            <input type="number" min="0" name="quantity" required value="<?= e($_POST['quantity'] ?? '0') ?>">
        </div>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea name="description"><?= e($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label>
            <input style="width:auto" type="checkbox" name="featured">
            Feature on home page
        </label>
    </div>

    <button class="btn">Save book</button>
</form>

<?php require '../includes/admin-footer.php'; ?>
        </div>


        <div class="form-group">

            <!-- Book cover image -->
            <label>Cover image</label>

            <!--
                accept specifies the image types that the browser
                should allow the user to select.
            -->
            <input
                type="file"
                name="image"
                accept="image/jpeg,image/png,image/webp"
            >

        </div>

    </div>


    <!-- Price and Quantity -->
    <div class="form-row">

        <div class="form-group">

            <!-- Book price -->
            <label>Price (Rs.) *</label>

            <input
                type="number"
                min="0.01"
                step="0.01"
                name="price"
                required
                value="<?= e($_POST['price'] ?? '') ?>"
            >

        </div>


        <div class="form-group">

            <!-- Number of books in stock -->
            <label>Quantity *</label>

            <input
                type="number"
                min="0"
                name="quantity"
                required
                value="<?= e($_POST['quantity'] ?? '0') ?>"
            >

        </div>

    </div>


    <!-- Book Description -->
    <div class="form-group">

        <label>Description</label>

        <textarea name="description"><?= e($_POST['description'] ?? '') ?></textarea>

    </div>


    <!-- Featured Book Option -->
    <div class="form-group">

        <label>

            <!--
                If checked, the book will be marked as featured
                and can be displayed on the home page.
            -->
            <input
                style="width:auto"
                type="checkbox"
                name="featured"
            >

            Feature on home page

        </label>

    </div>


    <!-- Submit button -->
    <button class="btn">
        Save book
    </button>

</form>


<?php

// Include the common admin footer
require '../includes/admin-footer.php';

?>