<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Member)
auth('authentication/login.php', 'Member');

// Get order details
$order_id  = req('order_id');
$stm = $_db->prepare('
    SELECT o.*, oi.name AS product_name, oi.variation_name, oivo.order_item_variation_option_id, oivo.name AS variation_option_name, oivo.unit_price, oivo.quantity, oivo.image, oivo.weight
    FROM orders o JOIN order_item oi
    ON o.order_id = oi.order_id
    JOIN order_item_variation_option oivo
    ON oi.order_item_id = oivo.order_item_id
    WHERE o.order_id = ?'
);
$stm->execute([$order_id]);
$result = $stm->fetchAll(PDO::FETCH_OBJ);

$orderItems = [];
foreach ($result as $row) {
    $orderItems[] = $row;
}
$order = $orderItems[0];

if (!$result) {
    temp('info', 'Invalid order.');
    redirect('orderHistoryList.php');
} else if (strcmp($_user->user_id, $order->user_id) !== 0) { // Check if valid user
    temp('info', 'Access denied. (Invalid user)');
    redirect('orderHistoryList.php');
}

// Get shipping details
$stm = $_db->prepare('SELECT * FROM shipping_detail WHERE shipping_detail_id = ?');
$stm->execute([$order->shipping_detail_id]);
$shipping_detail = $stm->fetch();

// Get payment details
$stm = $_db->prepare('SELECT * FROM payment WHERE order_id = ?');
$stm->execute([$order->order_id]);
$payment = $stm->fetch();

$statuses = array_keys($_orderStatus);

// Submit
if (is_post()) {
    $currentStatusIndex = array_search($order->status, $statuses);

    if ($currentStatusIndex !== false && $currentStatusIndex < count($statuses) - 1) {
        $nextStatus = $statuses[$currentStatusIndex + 1];
        $stm = $_db->prepare('
        UPDATE orders SET status = ?,
                          received_datetime = CURRENT_TIMESTAMP()
        WHERE order_id = ?');
        $stm->execute([$nextStatus, $order->order_id]);
        
        temp('info', 'Status has been updated to \'' . ucfirst($nextStatus) . '\'.');
        redirect();
    } else {
        temp('info', 'Status has been updated to \'Received\'.');
    }
}

// ----------------------------------------------------------------------------

$_title = 'finecamp | Order Details';
$_css_file = '../../css/member/orderHistoryDetail.css';
include '../../_customerPortalHead.php';
?>
        <div class="orderDetailPage">
            <div class="orderDetailPageContainer">
                <div class="orderDetailContainer">
                    <div class="backBtnContainer">
                        <a class="backBtn" href="orderHistoryList.php">Back</a>
                    </div>
                    <div class="orderDetailHeaderContainer">
                        <div class="orderDetailHeader">
                            Order Details
                        </div>
                        <div class="orderDetailNoOfItems">(<?= count($orderItems) ?>) Item(s)</div>
                    </div>
                    <?php if ($orderItems == null) { ?>
                        <div class="noRecordContainer">
                            Order not found.
                        </div>
                    <?php } else { ?> 
                    <div class="orderDetailSection">
                        <div class="mainOrderDetailHeaderSection">
                            <div><b>Order ID: <?= $order->order_id ?></b></div>
                            <div><b><?= ucfirst($order->status) ?></b></div>
                        </div>
                        <div class="orderDetailHeaderSection">
                            <div class="orderDetailTableHeaderContainer">
                                <div>Product Details</div>
                                <div>Unit Price</div>
                                <div>Quantity</div>
                                <div>Final Price</div>
                                <div></div>
                            </div>
                        </div>
                        <div class="allOrderItemContainer">
                        <?php foreach ($orderItems as $item): 
                                // Final price
                                $finalPrice = $item->unit_price * $item->quantity;
                        ?>
                            <div class="orderDetailCard">
                                <div class="productInfoContainer">
                                    <img src="../../images/member/order/productVariation/<?= $item->order_item_variation_option_id ?>/<?= $item->image ?>" alt="Product Image" />
                                    <div class="productInfo">
                                        <div class="productName"><?= $item->product_name ?></div>
                                        <div><?= $item->variation_name ?>: <?= $item->variation_option_name ?></div>
                                        <div>Weight: <?= $item->weight ?> kg</div>
                                    </div>
                                </div>
                                <div class="unitPriceContainer">
                                    RM <?= sprintf("%.2f", $item->unit_price) ?>
                                </div>
                                <div class="quantityContainer">
                                    <?= $item->quantity ?>
                                </div>
                                <div class="finalPriceContainer">
                                    RM <?= sprintf("%.2f", $finalPrice) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <div class="summaryContainer">
                            <div>
                                <div class="statusContainer">
                                    <div class="statusHeaderContainer">
                                        <div class="statusHeader">Status</div>
                                        <div class="statusName">(<?=ucfirst($order->status) ?>)</div>
                                    </div>
                                    <div class="status">
                                        <div>
                                            <div>Created Time</div>
                                            <div><?= $order->created_datetime ?? '-' ?></div>
                                        </div>
                                        <div>
                                            <div>Packaged Time</div>
                                            <div><?= $order->packaging_datetime ?? '-' ?></div>
                                        </div>
                                        <div>
                                            <div>Shipping Time</div>
                                            <div><?= $order->shipping_datetime ?? '-' ?></div>
                                        </div>
                                        <div>
                                            <div>Delivered Time</div>
                                            <div><?= $order->delivery_datetime ?? '-' ?></div>
                                        </div>
                                        <div>
                                            <div>Received Time</div>
                                            <div><?= $order->received_datetime ?? '-' ?></div>
                                        </div>
                                    </div>
                                    <?php if ($order->status === 'delivered') { ?>
                                        <form id="updateStatusContainer" method="post">
                                            <button>Received</button>
                                        </form>
                                    <?php } else if ($order->status === 'completed') { ?>
                                        <div id="updateStatusContainer">
                                            <div>Received</div>
                                        </div>
                                    <?php } else if (in_array($order->status, $statuses)){ ?>
                                        <div id="updateStatusContainer">
                                            <div>Pending</div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div>
                                <div class="addressMethodContainer">
                                    <div class="deliveryAddressContainer">
                                        <div class="deliveryAddressHeader">Delivery Address</div>
                                        <div class="deliveryAddress">
                                            <?php if ($shipping_detail) { ?>
                                            <div class="recipientName"><?= $shipping_detail->recipient_name ?></div>
                                            <div><?= $shipping_detail->recipient_phone_no ?></div>
                                            <div><?= $shipping_detail->address_line ?></div>
                                            <div><?= $shipping_detail->postcode ?>&nbsp;<?= $shipping_detail->city ?>,&nbsp;<?= $shipping_detail->state ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="paymentMethodContainer">
                                        <div class="paymentMethodHeader">Payment Method</div>
                                        <div class="paymentMethod">
                                            <?php if ($payment) { ?>
                                            <div><?= $payment->card_type ?></div>
                                            <div>(on <?= $payment->created_datetime ?>)</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="paymentDetailsContainer">
                                    <div class="paymentDetailsHeader">Payment Details</div>
                                    <div class="amountContainer">
                                        <div>
                                            <div>Subtotal</div>
                                            <div name="subtotal">RM <?= $order->subtotal_amount ?></div>
                                        </div>
                                        <div>
                                            <div>Shipping Charge</div>
                                            <div name="shippingCharge">RM <?= $order->shipping_charge ?></div>
                                        </div>
                                        <div>
                                            <div>Tax (10%)</div>
                                            <div name="tax">RM <?= $order->tax_charge ?></div>
                                        </div>
                                        <div>
                                            <div>Discount</div>
                                            <div name="discount">- RM <?= $order->discount_amount ?></div>
                                        </div>
                                        <div>
                                            <div><b>Total</b></div>
                                            <b><div name="total">RM <?= $order->total_amount ?></div></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        $('#updateStatusContainer button').on('click', function(e) {
            e.preventDefault();

            if (confirm('Are you sure to update status to \'Completed\'?')) {
                document.getElementById('updateStatusContainer').submit();
            }
        });
    </script>
</body>
</html>

<?php
include '../../_foot.php';
?>