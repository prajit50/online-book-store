<!-- Book card -->
<article class="book-card">

    <!-- Book cover image -->
    <img
        class="book-cover"
        src="images/TheAlchemist.jpg"
        alt="The Alchemist cover"
    >

    <div class="book-info">

        <!-- Book title -->
        <h3>The Alchemist</h3>

        <!-- Author -->
        <p>Paulo Coelho</p>

        <!-- Price and category -->
        <div class="book-meta">

            <span class="price">
                Rs. 850.00
            </span>

            <span class="tag">
                Fiction
            </span>

        </div>

        <!-- Stock status -->
        <p class="stock-in">
            In stock
        </p>

        <!-- Buttons -->
        <div class="card-actions">

            <a
                class="btn btn-light"
                href="book-details.html"
            >
                Details
            </a>

            <form method="post" action="cart.php">

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

                <button class="btn" type="submit">
                    Add to cart
                </button>

            </form>

        </div>

    </div>

</article>