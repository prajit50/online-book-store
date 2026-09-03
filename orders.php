<!-- =====================================================
     ORDER SUCCESS PAGE
====================================================== -->

<section class="success-page">

    <!--
        Success icon.
        The check mark indicates that the order
        was successfully placed.
    -->
    <div class="success-mark">✓</div>


    <!-- Main success message -->
    <h1>
        Order placed successfully
    </h1>


    <!-- Additional information for the customer -->
    <p class="muted">
        Thank you! We will contact you before delivery.
    </p>


    <!--
        Button that takes the user to their
        previous orders / order history.
    -->
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


<!--
    Check whether the user has any orders.

    $orders->num_rows contains the number of
    orders returned from the database.
-->
<?php if (!$orders->num_rows): ?>


    <!-- =================================================
         NO ORDERS
    ================================================== -->

    <div class="empty">

        <!-- Message shown when the user has no orders -->
        You have not placed an order yet.

        <br><br>


        <!--
            Send the user to the book catalog
            so they can purchase a book.
        -->
        <a class="btn" href="books.php">
            Browse books
        </a>

    </div>


<?php else: ?>


    <!-- =================================================
         ORDERS TABLE
    ================================================== -->

    <div class="table-wrap">

        <table>

            <!-- Table headings -->
            <thead>
                <tr>

                    <!-- Unique order ID -->
                    <th>
                        Order ID
                    </th>

                    <!-- Date the order was created -->
                    <th>
                        Date
                    </th>

                    <!-- Payment method -->
                    <th>
                        Payment
                    </th>

                    <!-- Current order status -->
                    <th>
                        Status
                    </th>

                    <!-- Total amount -->
                    <th>
                        Total
                    </th>

                </tr>
            </thead>


            <!-- =================================================
                 DISPLAY EACH ORDER
            ================================================== -->

            <tbody>

                <?php while ($order = $orders->fetch_assoc()): ?>

                    <tr>

                        <!--
                            Display the order ID.

                            Example:
                            #25
                        -->
                        <td>
                            #<?= $order['id'] ?>
                        </td>

                        <td>
                            <?= date('M j, Y', strtotime($order['created_at'])) ?>
                        </td>

                        <td>
                            <?= e($order['payment_method']) ?>
                        </td>

                        <td>
                            <span class="status status-<?= strtolower($order['status']) ?>">
                                <?= e($order['status']) ?>
                            </span>
                        </td>

                        <td>
                            Rs. <?= number_format($order['total_amount'], 2) ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

<?php endif; ?>