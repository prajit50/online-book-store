<h1>Checkout</h1>

<div class="checkout-layout">

    <!-- Delivery Information -->
    <form class="order-box">

        <h2>Delivery information</h2>

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" required>
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" required></textarea>
        </div>

        <h3>Payment method</h3>

        <p>
            <strong>Cash on Delivery (COD)</strong><br>
            <span class="muted">
                Pay when your order is delivered.
            </span>
        </p>

        <button type="submit" class="btn">
            Place order
        </button>

    </form>


    <!-- Order Summary -->
    <aside class="summary">

        <h3>Your order</h3>

        <div class="summary-line">
            <span>Book Title × 1</span>
            <span>Rs. 500.00</span>
        </div>

        <div class="summary-line">
            <span>Another Book × 2</span>
            <span>Rs. 800.00</span>
        </div>

        <div class="summary-line total">
            <span>Total</span>
            <span>Rs. 1,300.00</span>
        </div>

    </aside>

</div>