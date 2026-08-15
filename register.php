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


    <!-- Error message area -->
    <div class="alert alert-danger" style="display: none;">
        Please check your information and try again.
    </div>


    <!-- Full Name -->
    <div class="form-group">

        <label for="name">
            Full Name
        </label>

        <input
            id="name"
            name="name"
            type="text"
            required
            placeholder="Enter your full name"
        >

    </div>


    <!-- Email and Phone -->
    <div class="form-row">

        <!-- Email -->
        <div class="form-group">

            <label for="email">
                Email
            </label>

            <input
                id="email"
                type="email"
                name="email"
                required
                placeholder="Enter your email"
            >

        </div>


        <!-- Phone -->
        <div class="form-group">

            <label for="phone">
                Phone Number
            </label>

            <input
                id="phone"
                type="tel"
                name="phone"
                required
                placeholder="Enter your phone number"
            >

        </div>

    </div>


    <!-- Address -->
    <div class="form-group">

        <label for="address">
            Address
        </label>

        <textarea
            id="address"
            name="address"
            required
            placeholder="Enter your address"
        ></textarea>

    </div>


    <!-- Passwords -->
    <div class="form-row">

        <!-- Password -->
        <div class="form-group">

            <label for="password">
                Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                required
                minlength="6"
                placeholder="Enter your password"
            >

        </div>


        <!-- Confirm password -->
        <div class="form-group">

            <label for="confirm_password">
                Confirm Password
            </label>

            <input
                id="confirm_password"
                type="password"
                name="confirm_password"
                required
                minlength="6"
                placeholder="Confirm your password"
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