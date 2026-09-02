<!-- =====================================================
     ORDER SUCCESS PAGE
====================================================== -->

<section class="success-page">

    <div class="success-mark">✓</div>

    <h1>
        Order placed successfully
    </h1>

    <p class="muted">
        Thank you! We will contact you before delivery.
    </p>

    <a class="btn" href="orders.php">
        View order history
    </a>

</section>


<!-- =====================================================
     ORDER HISTORY
====================================================== -->

<h1>
    Order history
</h1>


<!-- =================================================
     NO ORDERS
================================================== -->

<div class="empty">

    You have not placed an order yet.

    <br><br>

    <a class="btn" href="books.php">
        Browse books
    </a>

</div>


<!-- =================================================
     ORDERS TABLE
================================================== -->

<div class="table-wrap">

    <table>

        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>

            <!-- Sample Order 1 -->
            <tr>

                <td>#25</td>

                <td>Aug 8, 2026</td>

                <td>COD</td>

                <td>
                    <span class="status status-pending">
                        Pending
                    </span>
                </td>

                <td>
                    Rs. 1,250.00
                </td>

            </tr>


            <!-- Sample Order 2 -->
            <tr>

                <td>#24</td>

                <td>Aug 5, 2026</td>

                <td>COD</td>

                <td>
                    <span class="status status-delivered">
                        Delivered
                    </span>
                </td>

                <td>
                    Rs. 850.00
                </td>

            </tr>

        </tbody>

    </table>

</div>