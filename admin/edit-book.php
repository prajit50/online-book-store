<?php

// Set the page title
$page_title = 'Edit Book';

// Include the common admin header
// This likely also provides the database connection ($conn)
require '../includes/admin-header.php';

// GET BOOK ID

// Get the book ID from the URL.
//
// Example:
// edit-book.php?id=10
//
// $_GET['id'] would contain 10.
//
// Convert it to an integer for safety.
$id = (int) ($_GET['id'] ?? 0);


// GET BOOK FROM DATABASE


// Prepare a query to find the book by its ID.
$get = $conn->prepare(
    'SELECT * FROM books WHERE id=?'
);

// Bind the ID to the ? placeholder
// 'i' = integer
$get->bind_param('i', $id);

// Execute the query
$get->execute();

// Get the book as an associative array
$book = $get->get_result()->fetch_assoc();



// CHECK IF BOOK EXISTS


if (!$book) {

    // Show an error message if the book doesn't exist
    set_flash('Book not found.', 'danger');

    // Return to the books management page
    redirect('books.php');
}


// GET CATEGORIES


// Get all categories so the admin can select
// a category for the book.
$categories = $conn->query(
    'SELECT * FROM categories ORDER BY name'
);


// Store validation/upload errors
$error = '';


// PROCESS FORM SUBMISSION


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // GET FORM VALUES

    // Get book title
    $title = trim($_POST['title'] ?? '');

    // Get author
    $author = trim($_POST['author'] ?? '');

    // Get ISBN
    $isbn = trim($_POST['isbn'] ?? '');

    // Get publisher
    $publisher = trim($_POST['publisher'] ?? '');

    // Get category ID
    $category = (int) ($_POST['category_id'] ?? 0);

    // Get price
    $price = (float) ($_POST['price'] ?? 0);

    // Get quantity
    $quantity = (int) ($_POST['quantity'] ?? 0);

    // Get description
    $description = trim($_POST['description'] ?? '');

    // Check whether the featured checkbox was selected
    //
    // Checked  → 1
    // Unchecked → 0
    $featured = isset($_POST['featured']) ? 1 : 0;


    // -----------------------------------------------------
    // VALIDATE FORM
    // -----------------------------------------------------

    // Check required fields and valid numeric values
    if (
        !$title ||
        !$author ||
        !$category ||
        $price <= 0 ||
        $quantity < 0
    ) {

        // Display an error message
        $error = 'Please complete required fields correctly.';

    } else {


        // Upload a new image.
        //
        // The second argument is the current image.
        // This allows upload_cover() to keep/replace
        // the existing image appropriately.
        $image = upload_cover(
            'image',
            $book['image']
        );


        // Check whether image upload failed
        if ($image === false) {

            $error =
                'Upload a JPG, PNG, or WEBP image under 5 MB.';

        } else {

            // UPDATE BOOK IN DATABASE
            
            // Prepare UPDATE query.
            //
            // The ? symbols are placeholders.
            $stmt = $conn->prepare(
                'UPDATE books SET
                    title=?,
                    author=?,
                    isbn=?,
                    publisher=?,
                    category_id=?,
                    price=?,
                    quantity=?,
                    description=?,
                    image=?,
                    featured=?
                 WHERE id=?'
            );


            // Bind the values to the placeholders.
            //
            // s = string
            // i = integer
            // d = decimal/double
            //
            // The order must match the SQL query.
            $stmt->bind_param(
                'ssssidissii',
                $title,
                $author,
                $isbn,
                $publisher,
                $category,
                $price,
                $quantity,
                $description,
                $image,
                $featured,
                $id
            );


            // Execute the UPDATE query
            $stmt->execute();


            // Show success message
            set_flash('Book updated.');


            // Return to the books management page
            redirect('books.php');
        }
    }
}

?>


<!-- PAGE HEADER -->

<div class="admin-actions">

    <!-- Page title -->
    <h1>Edit book</h1>

    <!-- Back link -->
    <a href="books.php">
        ← Back to books
    </a>

</div>


<!--  EDIT BOOK FORM -->

<!--
    multipart/form-data is required because
    the form allows image uploads.
-->
<form
    class="admin-form"
    method="post"
    enctype="multipart/form-data"
>


    <!-- Display an error if one exists -->
    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <!--TITLE AND AUTHOR -->

    <div class="form-row">

        <div class="form-group">

            <label>Title *</label>

            <input
                name="title"
                required

                value="<?= e(
                    $_POST['title'] ?? $book['title']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label>Author *</label>

            <input
                name="author"
                required
                value="<?= e(
                    $_POST['author'] ?? $book['author']
                ) ?>"
            >

        </div>

    </div>


    <!-- ISBN AND PUBLISHER -->

    <div class="form-row">

        <div class="form-group">

            <label>ISBN</label>

            <input
                name="isbn"
                value="<?= e(
                    $_POST['isbn'] ?? $book['isbn']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label>Publisher</label>

            <input
                name="publisher"
                value="<?= e(
                    $_POST['publisher'] ?? $book['publisher']
                ) ?>"
            >

        </div>

    </div>


    <!--  CATEGORY AND COVER IMAGE -->

    <div class="form-row">

        <div class="form-group">

            <label>Category *</label>

            <select
                name="category_id"
                required
            >

                <?php
                $selected_category = $_POST['category_id'] ?? $book['category_id'];

                // Loop through every category
                while ($c = $categories->fetch_assoc()):
                    $selected = (string) $c['id'] === (string) $selected_category;
                ?>

                    <option
                        value="<?= (int) $c['id'] ?>"
                        <?= $selected ? 'selected' : '' ?>
                    >
                        <?= e($c['name']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </div>


        <div class="form-group">

            <!--  The admin can upload a replacement cover image  -->

            <label>
                Replace cover image
            </label>

            <input
                type="file"
                name="image"
                accept="image/jpeg,image/png,image/webp"
            >

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


    <!--  PRICE AND QUANTITY -->

    <div class="form-row">

        <div class="form-group">

            <label>
                Price (Rs.) *
            </label>

            <input
                type="number"
                min="0.01"
                step="0.01"
                name="price"
                required
                value="<?= e(
                    $_POST['price'] ?? $book['price']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label>
                Quantity *
            </label>

            <input
                type="number"
                min="0"
                name="quantity"
                required
                value="<?= e(
                    $_POST['quantity'] ?? $book['quantity']
                ) ?>"
            >

        </div>

    </div>


    <!-- DESCRIPTION -->

    <div class="form-group">

        <label>
            Description
        </label>

        <textarea name="description"><?= e(
            $_POST['description']
            ?? $book['description']
        ) ?></textarea>

    </div>


    <!-- FEATURED CHECKBOX -->

    <div class="form-group">

        <label>

            <input
                style="width:auto"
                type="checkbox"
                name="featured"

                
                <?= isset($_POST['featured'])
                    || (!$_POST && $book['featured'])
                    ? 'checked'
                    : ''
                ?>
            >

            Feature on home page

        </label>

    </div>


    <!-- Submit button -->
    <button class="btn">
        Update book
    </button>

</form>


<?php

// Include the common admin footer
require '../includes/admin-footer.php';

?>