<?php

// Set the title of the admin page
$page_title = 'Add Book';

// Include the admin header.
// This probably also starts the database connection ($conn).
require '../includes/admin-header.php';

// Variable used to store validation/upload error messages
$error = '';

// Get all book categories from the database
// Results are sorted alphabetically by category name
$categories = $conn->query('SELECT * FROM categories ORDER BY name');


// Check whether the form was submitted using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the book title from the form
    // trim() removes unnecessary spaces from the beginning and end
    $title = trim($_POST['title'] ?? '');

    // Get the author's name
    $author = trim($_POST['author'] ?? '');

    // Get the ISBN
    $isbn = trim($_POST['isbn'] ?? '');

    // Get the publisher
    $publisher = trim($_POST['publisher'] ?? '');

    // Get the selected category ID
    // Convert it to an integer
    $category = (int) ($_POST['category_id'] ?? 0);

    // Get the price and convert it to a floating-point number
    $price = (float) ($_POST['price'] ?? 0);

    // Get the quantity and convert it to an integer
    $quantity = (int) ($_POST['quantity'] ?? 0);

    // Get the book description
    $description = trim($_POST['description'] ?? '');

    // Check whether the "featured" checkbox was selected
    // If checked, store 1; otherwise store 0
    $featured = isset($_POST['featured']) ? 1 : 0;


    // Validate the required fields
    //
    // $title       → must not be empty
    // $author      → must not be empty
    // $category    → must be selected
    // $price       → must be greater than 0
    // $quantity    → cannot be negative
    if (!$title || !$author || !$category || $price <= 0 || $quantity < 0) {

        // Display an error message if validation fails
        $error = 'Please complete all required fields with valid values.';

    } else {

        // Upload the book cover image
        // 'image' is the name of the file input in the HTML form
        $image = upload_cover('image');


        // Check whether the image upload failed
        if ($image === false) {

            // Display an error message
            $error = 'Upload a JPG, PNG, or WEBP image under 2 MB.';

        } else {

            // Prepare an SQL INSERT statement
            //
            // The ? characters are placeholders for the values
            // that will be inserted into the database.
            $stmt = $conn->prepare(
                'INSERT INTO books
                (title, author, isbn, publisher, category_id, price,
                 quantity, description, image, featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );


            // Bind the PHP variables to the SQL placeholders
            //
            // s = string
            // i = integer
            // d = double/decimal number
            //
            // The order must match the columns/placeholders above.
            $stmt->bind_param(
                'ssssidissi',
                $title,
                $author,
                $isbn,
                $publisher,
                $category,
                $price,
                $quantity,
                $description,
                $image,
                $featured
            );


            // Execute the INSERT query
            // This adds the new book to the database
            $stmt->execute();


            // Create a success message
            set_flash('Book added successfully.');


            // Redirect the admin back to the books page
            redirect('books.php');
        }
    }
}
?>


<!-- Admin page actions -->
<div class="admin-actions">

    <!-- Page heading -->
    <h1>Add book</h1>

    <!-- Link back to the list of books -->
    <a href="books.php">← Back to books</a>

</div>


<!--
    Book creation form.

    method="post"
        Sends the form data using HTTP POST.

    enctype="multipart/form-data"
        Required because the form allows file uploads.
-->
<form class="admin-form" method="post" enctype="multipart/form-data">

    <?php if ($error): ?>

        <!--
            Display an error message if validation or image upload failed.
            e() is presumably a custom HTML-escaping function.
        -->
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <!-- Title and Author -->
    <div class="form-row">

        <div class="form-group">

            <!-- Required book title -->
            <label>Title *</label>

            <input
                name="title"
                required
                value="<?= e($_POST['title'] ?? '') ?>"
            >

        </div>


        <div class="form-group">

            <!-- Required author name -->
            <label>Author *</label>

            <input
                name="author"
                required
                value="<?= e($_POST['author'] ?? '') ?>"
            >

        </div>

    </div>


    <!-- ISBN and Publisher -->
    <div class="form-row">

        <div class="form-group">

            <!-- Optional ISBN -->
            <label>ISBN</label>

            <input
                name="isbn"
                value="<?= e($_POST['isbn'] ?? '') ?>"
            >

        </div>


        <div class="form-group">

            <!-- Optional publisher -->
            <label>Publisher</label>

            <input
                name="publisher"
                value="<?= e($_POST['publisher'] ?? '') ?>"
            >

        </div>

    </div>


    <!-- Category and Cover Image -->
    <div class="form-row">

        <div class="form-group">

            <!-- Book category -->
            <label>Category *</label>

            <select name="category_id" required>

                <!-- Default option -->
                <option value="">Select category</option>


                <?php
                // Loop through every category retrieved from the database
                while ($c = $categories->fetch_assoc()):
                ?>

                    <!--
                        $c['id'] is the category ID.
                        $c['name'] is the category name.
                    -->
                    <option value="<?= $c['id'] ?>">
                        <?= e($c['name']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

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