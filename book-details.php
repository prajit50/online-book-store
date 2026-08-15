// Book details section

<section class="details">

// Book image
    <img
        class="details-image"
        src="images/book-placeholder.svg"
        alt="The Great Gatsby cover"
    >

   //book details
    <div>

        <!-- Book category -->
        <p class="tag">
            Fiction
        </p>

        <!-- Book title -->
        <h1>
            The Great Gatsby
        </h1>

        <!-- Author -->
        <p class="muted">
            by F. Scott Fitzgerald
        </p>

        <!-- Book price -->
        <p class="price">
            Rs. 500.00
        </p>

        <!-- Book description -->
        <p class="description">
            The Great Gatsby is a classic novel about wealth,
            ambition, love, and the American Dream.
        </p>

        <!-- ADDITIONAL BOOK INFORMATION -->
        <div class="detail-list">

            <!-- Publisher -->
            <p>
                <strong>Publisher:</strong>
                Scribner
            </p>

            <!-- ISBN -->
            <p>
                <strong>ISBN:</strong>
                9780743273565
            </p>

            <!-- Availability -->
            <p>
                <strong>Availability:</strong>

                <span class="stock-in">
                    5 available
                </span>
            </p>

        </div>

        <!-- ADD TO CART -->
        <form
            action="cart.php"
            method="post"
        >

            <input
                type="hidden"
                name="action"
                value="add"
            >

            <input
                type="hidden"
                name="book_id"
                value="1"
            >

            <button class="btn">
                Add to cart
            </button>

        </form>

    </div>

</section>