<?php

// Set the page title
$page_title = 'Categories';

// Include the common admin header
// This likely contains the database connection ($conn),
// authentication checks, HTML header, navigation, etc.
require '../includes/admin-header.php';

// Store validation/error messages
$error = '';

// Stores category information when editing a category
$edit = null;


// =========================================================
// HANDLE FORM SUBMISSION
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // -----------------------------------------------------
    // DELETE CATEGORY
    // -----------------------------------------------------

    // Check if the delete form was submitted
    if (isset($_POST['delete_id'])) {

        // Get the category ID and convert it to an integer
        $id = (int) $_POST['delete_id'];

        try {

            // Prepare DELETE query
            $stmt = $conn->prepare(
                'DELETE FROM categories WHERE id=?'
            );

            // Bind the category ID
            // 'i' = integer
            $stmt->bind_param('i', $id);

            // Execute the DELETE query
            $stmt->execute();

            // Show success message
            set_flash('Category deleted.');

        } catch (mysqli_sql_exception $e) {

            // This usually happens when the category is
            // still being used by one or more books.
            set_flash(
                'Categories with books cannot be deleted.',
                'danger'
            );
        }

        // Return to the categories page
        redirect('categories.php');
    }


    // -----------------------------------------------------
    // GET CATEGORY DATA FROM FORM
    // -----------------------------------------------------

    // Get the category ID.
    //
    // If update_id exists, this is an edit operation.
    // If it doesn't exist, the value becomes 0,
    // meaning this is a new category.
    $id = (int) ($_POST['update_id'] ?? 0);

    // Get category name
    $name = trim($_POST['name'] ?? '');

    // Get category description
    $description = trim($_POST['description'] ?? '');


    // -----------------------------------------------------
    // VALIDATE CATEGORY
    // -----------------------------------------------------

    // Category name is required
    if (!$name) {

        $error = 'Category name is required.';


    // -----------------------------------------------------
    // UPDATE EXISTING CATEGORY
    // -----------------------------------------------------

    } elseif ($id) {

        // Prepare UPDATE query
        $stmt = $conn->prepare(
            'UPDATE categories
             SET name=?, description=?
             WHERE id=?'
        );

        try {

            // Bind:
            // name        = string
            // description = string
            // id          = integer
            $stmt->bind_param(
                'ssi',
                $name,
                $description,
                $id
            );

            // Execute UPDATE
            $stmt->execute();

            // Show success message
            set_flash('Category updated.');

            // Redirect back to categories
            redirect('categories.php');

        } catch (mysqli_sql_exception $e) {

            // Usually caused by a duplicate category name
            $error = 'A category with this name already exists.';
        }


        // Keep the submitted data available
        // so the edit form can be displayed again.
        $edit = [
            'id' => $id,
            'name' => $name,
            'description' => $description
        ];


    // -----------------------------------------------------
    // ADD NEW CATEGORY
    // -----------------------------------------------------

    } else {

        // Prepare INSERT query
        $stmt = $conn->prepare(
            'INSERT INTO categories(name, description)
             VALUES(?, ?)'
        );

        try {

            // Bind category name and description
            // Both are strings
            $stmt->bind_param(
                'ss',
                $name,
                $description
            );

            // Execute INSERT
            $stmt->execute();

            // Show success message
            set_flash('Category added.');

            // Redirect back to categories
            redirect('categories.php');

        } catch (mysqli_sql_exception $e) {

            // Usually caused by duplicate category name
            $error = 'A category with this name already exists.';
        }
    }
}


// =========================================================
// LOAD CATEGORY FOR EDITING
// =========================================================

// If we aren't already editing a category,
// check whether an edit ID was provided in the URL.
if (!$edit && isset($_GET['edit'])) {

    // Get category ID from URL
    //
    // Example:
    // categories.php?edit=5
    //
    // This gets the value 5.
    $id = (int) $_GET['edit'];


    // Prepare SELECT query
    $stmt = $conn->prepare(
        'SELECT * FROM categories WHERE id=?'
    );

    // Bind category ID
    $stmt->bind_param('i', $id);

    // Execute query
    $stmt->execute();

    // Get the category data
    $edit = $stmt->get_result()->fetch_assoc();
}


// =========================================================
// GET ALL CATEGORIES
// =========================================================

// Get every category and count how many books
// belong to each category.
$categories = $conn->query(
    'SELECT
        c.*,
        COUNT(b.id) book_count

     FROM categories c

     LEFT JOIN books b
        ON b.category_id = c.id

     GROUP BY c.id

     ORDER BY c.name'
);

?>


<!-- =====================================================
     PAGE HEADER
====================================================== -->

<div class="admin-actions">

    <div>

        <!-- Page title -->
        <h1>Categories</h1>

        <!-- Description -->
        <p class="muted">
            Organize books into categories.
        </p>

    </div>

</div>


<!-- =====================================================
     CATEGORY FORM + CATEGORY TABLE
====================================================== -->

<div class="form-row">


    <!-- =================================================
         ADD / EDIT CATEGORY FORM
    ================================================== -->

    <form class="admin-form" method="post">

        <!--
            Change the heading depending on whether
            we are adding or editing.
        -->
        <h2>
            <?= $edit ? 'Edit category' : 'Add category' ?>
        </h2>


        <!-- Display validation errors -->
        <?php if ($error): ?>

            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <!--
            Only include update_id when editing.

            This hidden field tells PHP:
            "We want to update this category."
        -->
        <?php if ($edit): ?>

            <input
                type="hidden"
                name="update_id"
                value="<?= $edit['id'] ?>"
            >

        <?php endif; ?>


        <!-- Category name -->
        <div class="form-group">

            <label>Name</label>

            <input
                name="name"
                required
                value="<?= e($edit['name'] ?? '') ?>"
            >

        </div>


        <!-- Category description -->
        <div class="form-group">

            <label>Description</label>

            <textarea name="description"><?= e(
                $edit['description'] ?? ''
            ) ?></textarea>

        </div>


        <!--
            Button text changes depending on whether
            we are adding or editing.
        -->
        <button class="btn">
            <?= $edit ? 'Update category' : 'Add category' ?>
        </button>


        <!-- Show Cancel link only while editing -->
        <?php if ($edit): ?>

            <a href="categories.php">
                Cancel
            </a>

        <?php endif; ?>

    </form>


    <!-- =================================================
         CATEGORY TABLE
    ================================================== -->

    <div class="table-wrap">

        <table>

            <!-- Table headings -->
            <thead>

                <tr>

                    <th>Name</th>

                    <th>Books</th>

                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>

                <?php
                // Loop through every category
                while ($c = $categories->fetch_assoc()):
                ?>

                    <tr>


                        <!-- =================================
                             CATEGORY NAME
                        ================================== -->

                        <td>

                            <!-- Category name -->
                            <strong>
                                <?= e($c['name']) ?>
                            </strong>

                            <br>

                            <!-- Category description -->
                            <small>
                                <?= e($c['description']) ?>
                            </small>

                        </td>


                        <!-- =================================
                             NUMBER OF BOOKS
                        ================================== -->

                        <td>
                            <?= $c['book_count'] ?>
                        </td>


                        <!-- =================================
                             ACTIONS
                        ================================== -->

                        <td>

                            <!-- Edit category -->

                            <a
                                class="btn btn-light"
                                href="categories.php?edit=<?= $c['id'] ?>"
                            >
                                Edit
                            </a>


                            <!-- Delete category -->

                            <form
                                method="post"
                                style="display:inline"
                                onsubmit="return confirm('Delete this category?')"
                            >

                                <!--
                                    Hidden category ID
                                    sent to PHP when deleting.
                                -->
                                <input
                                    type="hidden"
                                    name="delete_id"
                                    value="<?= $c['id'] ?>"
                                >


                                <!-- Delete button -->
                                <button class="btn btn-danger">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>


<?php

// Include the common admin footer
require '../includes/admin-footer.php';

?>