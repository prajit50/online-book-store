<?php

// Include the database connection.
require 'includes/db.php';

// Include authentication-related functions.
require 'includes/auth.php';


// =========================================================
// CHECK IF USER IS ALREADY LOGGED IN
// =========================================================

// If the user is already logged in,
// redirect them to the homepage.
if (is_logged_in())
    redirect('index.php');


// Variable used to store registration errors.
$error = '';


// =========================================================
// HANDLE REGISTRATION FORM SUBMISSION
// =========================================================

// Check whether the registration form was submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the user's name and remove extra spaces.
    $name = trim($_POST['name'] ?? '');

    // Get the user's email.
    $email = trim($_POST['email'] ?? '');

    // Get the user's phone number.
    $phone = trim($_POST['phone'] ?? '');

    // Get the user's address.
    $address = trim($_POST['address'] ?? '');

    // Get the password.
    $password = $_POST['password'] ?? '';

    // Get the password confirmation.
    $confirm = $_POST['confirm_password'] ?? '';


    // =====================================================
    // VALIDATE REQUIRED FIELDS
    // =====================================================

    // Check whether any required field is empty.
    if (
        !$name ||
        !$email ||
        !$phone ||
        !$address ||
        !$password
    )

        // Show an error if a required field is missing.
        $error = 'All fields are required.';


    // =====================================================
    // VALIDATE EMAIL
    // =====================================================

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))

        // Check whether the email has a valid format.
        $error = 'Enter a valid email address.';


    // =====================================================
    // CHECK PASSWORD CONFIRMATION
    // =====================================================

    elseif ($password !== $confirm)

        // Make sure both passwords are identical.
        $error = 'Passwords do not match.';


    // =====================================================
    // CHECK PASSWORD LENGTH
    // =====================================================

    elseif (strlen($password) < 6)

        // Require at least 6 characters.
        $error = 'Password must be at least 6 characters.';


    // =====================================================
    // CREATE USER ACCOUNT
    // =====================================================

    else {

        // -------------------------------------------------
        // CHECK IF EMAIL ALREADY EXISTS
        // -------------------------------------------------

        // Prepare a query to find an existing account
        // with the same email address.
        $check = $conn->prepare(
            'SELECT id FROM users WHERE email=?'
        );


        // Bind the email to the SQL placeholder.
        // 's' means the value is a string.
        $check->bind_param(
            's',
            $email
        );


        // Execute the query.
        $check->execute();


        // Check whether the query returned any users.
        if ($check->get_result()->num_rows)

            // Email is already registered.
            $error = 'This email is already registered.';


        // -------------------------------------------------
        // INSERT NEW USER
        // -------------------------------------------------

        else {

            // Hash the password before storing it.
            //
            // The plain-text password is NEVER stored
            // directly in the database.
            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // Prepare the INSERT query.
            $stmt = $conn->prepare(
                'INSERT INTO users(
                    full_name,
                    email,
                    phone,
                    address,
                    password
                ) VALUES(?,?,?,?,?)'
            );


            // Bind the five values to the query.
            //
            // All five values are strings, therefore:
            // sssss
            $stmt->bind_param(
                'sssss',
                $name,
                $email,
                $phone,
                $address,
                $hash
            );


            // Execute the INSERT query.
            // This creates the user's account.
            $stmt->execute();


            // Store a success message.
            set_flash(
                'Account created. Please log in.'
            );


            // Redirect the user to the login page.
            redirect('login.php');
        }
    }
}


// =========================================================
// DISPLAY REGISTRATION PAGE
// =========================================================

// Set the page title.
$page_title = 'Create Account';


// Include the common website header.
require 'includes/header.php';

?>


<!-- =====================================================
     REGISTRATION FORM
====================================================== -->

<form
    class="form-card"
    method="post"
    data-validate
>

    <!-- Page heading -->
    <h1>
        Create account
    </h1>


    <!-- Description -->
    <p class="muted">
        Join BookNest to place orders.
    </p>


    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <!-- =================================================
         FULL NAME
    ================================================== -->

    <div class="form-group">

        <label>
            Full Name
        </label>

        <input
            name="name"
            required
            value="<?= e(
                $_POST['name'] ?? ''
            ) ?>"
        >

    </div>


    <!-- =================================================
         EMAIL AND PHONE
    ================================================== -->

    <div class="form-row">

        <!-- Email -->
        <div class="form-group">

            <label>
                Email
            </label>

            <input
                type="email"
                name="email"
                required
                value="<?= e(
                    $_POST['email'] ?? ''
                ) ?>"
            >

        </div>


        <!-- Phone -->
        <div class="form-group">

            <label>
                Phone Number
            </label>

            <input
                name="phone"
                required
                value="<?= e(
                    $_POST['phone'] ?? ''
                ) ?>"
            >

        </div>

    </div>


    <!-- =================================================
         ADDRESS
    ================================================== -->

    <div class="form-group">

        <label>
            Address
        </label>

        <textarea
            name="address"
            required
        ><?= e(
            $_POST['address'] ?? ''
        ) ?></textarea>

    </div>


    <!-- =================================================
         PASSWORDS
    ================================================== -->

    <div class="form-row">

        <!-- Password -->
        <div class="form-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                required
                minlength="6"
            >

        </div>


        <!-- Confirm password -->
        <div class="form-group">

            <label>
                Confirm Password
            </label>

            <input
                type="password"
                name="confirm_password"
                required
                minlength="6"
            >

        </div>

    </div>


    <!-- Register button -->
    <button
        class="btn"
        type="submit"
    >
        Register
    </button>


    <!-- Login link -->
    <p class="form-note">

        Already registered?

        <a href="login.php">
            <u>Login</u>
        </a>

    </p>

</form>


<?php

// Include the common website footer.
require 'includes/footer.php';

?>