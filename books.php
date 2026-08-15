<!-- PAGE HEADER -->
<div class="section-head">
    <div>
        <h1>Book Catalog</h1>
        <p class="muted">
            Find your next great read.
        </p>
    </div>
</div>


<!-- SEARCH AND CATEGORY FILTER -->
<form class="filters" method="get">

    <!-- Search box -->
    <input
        name="search"
        placeholder="Search title, author, or category"
    >

    <!-- Category dropdown -->
    <select name="category">

        <option value="">
            All categories
        </option>

        <option value="1">Fiction</option>
        <option value="2">Non-Fiction</option>
        <option value="3">Science</option>
        <option value="4">Technology</option>
        <option value="5">Biography</option>

    </select>

    <!-- Filter button -->
    <button class="btn">
        Filter
    </button>

</form>


<!-- BOOK GRID -->
<div class="book-grid">

    <!-- BOOK CARD -->
    <article class="book-card">

        <!-- Book cover -->
        <img
            class="book-cover"
            src="images/book-placeholder.svg"
            alt="The Great Gatsby cover"
        >

        <div class="book-info">

            <!-- Book title -->
            <h3>
                The Great Gatsby
            </h3>

            <!-- Author -->
            <p>
                F. Scott Fitzgerald
            </p>

            <!-- Price + Category -->
            <div class="book-meta">

                <span class="price">
                    Rs. 500.00
                </span>

                <span class="tag">
                    Fiction
                </span>

            </div>

            <!-- Stock status -->
            <p class="stock-in">
                In stock
            </p>

            <!-- Card actions -->
            <div class="card-actions">

                <!-- View details -->
                <a
                    class="btn btn-light"
                    href="book-details.php?id=1"
                >
                    Details
                </a>

                <!-- Add to cart -->
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

        </div>

    </article>


    <!-- BOOK CARD -->
    <article class="book-card">

        <img
            class="book-cover"
            src="images/book-placeholder.svg"
            alt="Atomic Habits cover"
        >

        <div class="book-info">

            <h3>
                Atomic Habits
            </h3>

            <p>
                James Clear
            </p>

            <div class="book-meta">

                <span class="price">
                    Rs. 750.00
                </span>

                <span class="tag">
                    Non-Fiction
                </span>

            </div>

            <p class="stock-in">
                In stock
            </p>

            <div class="card-actions">

                <a
                    class="btn btn-light"
                    href="book-details.php?id=2"
                >
                    Details
                </a>

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
                        value="2"
                    >

                    <button class="btn">
                        Add to cart
                    </button>

                </form>

            </div>

        </div>

    </article>

</div>