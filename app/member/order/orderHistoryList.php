<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Member)
auth('authentication/login.php', 'Member');

// (2) Get all order details
$stm = $_db->prepare('
    SELECT COUNT(*) FROM `orders`
    WHERE user_id = ?
');
$stm->execute([$_user->user_id]);
$totalNumOfOrders = $stm->fetchColumn();

$history_query = "
    SELECT o.order_id, o.total_amount, o.status, oi.name AS product_name, oi.variation_name, oivo.order_item_variation_option_id, oivo.name AS variation_option_name, oivo.unit_price, oivo.quantity, oivo.image, oivo.weight
    FROM orders o JOIN order_item oi
    ON o.order_id = oi.order_id
    JOIN order_item_variation_option oivo
    ON oi.order_item_id = oivo.order_item_id
    WHERE o.user_id = :user_id
";
$params[':user_id'] = $_user->user_id;

$search_input = req('search_input') ?? '';
$order_status = req('order_status') ?? 'all';

// Have search input
if ($search_input) {
    $history_query .= " AND (o.order_id LIKE :search_query
    OR o.order_id IN (
                    SELECT DISTINCT o2.order_id
                    FROM orders o2
                    JOIN order_item oi2 ON o2.order_id = oi2.order_id
                    WHERE oi2.name LIKE :search_query
                    AND o2.user_id = :user_id)
    OR o.total_amount LIKE :search_query)
                ";
    
    $params[':search_query'] = '%' . $search_input . '%';
}

// Have specific order status
if ($order_status !== '' && $order_status !== 'all') {
    $history_query .= " AND o.status = :order_status";
    $params[':order_status'] = $order_status;
}

// Default ORDER BY
$history_query .= " ORDER BY o.order_id DESC";

// Get orders
$stm = $_db->prepare($history_query);
$stm->execute($params);
$result = $stm->fetchAll(PDO::FETCH_OBJ);

$orders = [];
foreach ($result as $row) {
    $orders[$row->order_id][] = $row;
}

$status_option = array(
    'all' => 'All',
    'created' => 'Created',
    'packaged' => 'Packaged',
    'shipping' => 'Shipping',
    'delivered' => 'Delivered',
    'completed' => 'Completed'
);

// ----------------------------------------------------------------------------

$_title = 'finecamp | Order History';
$_css_file = '../../css/member/orderHistoryList.css';
include '../../_customerPortalHead.php';
?>
        <div class="orderHistoryPage">
            <div class="orderHistoryPageContainer">
                <div class="orderHistoryContainer">
                    <div class="orderHistoryHeaderContainer">
                        <div class="orderHistoryHeader">
                            Order History
                        </div>
                        <div class="orderHistoryNoOfItems">(<?= $totalNumOfOrders ?>) Record(s)</div>
                    </div>
                    <div class="searchSection">
                        <form>
                            <?= html_search('search_input', 'placeholder="Search by order ID, product name or total amount"') ?>
                            <?= html_select('order_status', $status_option, null) ?>
                            <button>Search</button>
                        </form>
                    </div>
                    <?php if ($orders == null) { ?>
                        <div class="noRecordContainer">
                            <b>No orders found.</b>
                        </div>
                    <?php } else { ?>
                        <div class="noRecordContainer">
                            <b><?= count($orders) ?> order(s) found.</b>
                        </div>
                    <div class="orderHistorySection">
                        <div class="allOrderContainer">
                            <?php foreach ($orders as $order_id => $orderItems): 
                                $order = $orderItems[0]; ?>
                            <div class="orderHistoryCard">
                                <div class="orderHistoryCardHeaderContainer">
                                    <div><b>Order ID: <?= $order->order_id ?></b></div>
                                    <div><b><?= ucfirst($order->status) ?></b></div>
                                </div>
                                <div class="allProductInfoContainer">
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="productInfoContainer">
                                            <img src="../../images/member/order/productVariation/<?= $item->order_item_variation_option_id ?>/<?= $item->image ?>" alt="Product Image" />
                                            <div class="productInfo">
                                                <div class="productName"><?= $item->product_name ?></div>
                                                <div><?= $item->variation_name ?>: <?= $item->variation_option_name ?></div>
                                                <div>Weight: <?= $item->weight ?> kg</div>
                                            </div>
                                            <div class="qtyPriceInfo">
                                                <div>x<?= $item->quantity ?></div>
                                                <div>RM <?= sprintf("%.2f", $item->quantity * $item->unit_price) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="summaryContainer">
                                    <div class="orderTotalContainer">
                                        <div><b>Order Total: </b></div>
                                        <div><b>RM <?= $order->total_amount ?></b></div>
                                    </div>
                                    <div class="actionContainer">
                                        <div>Total Items (<?= count($orderItems) ?>)</div>
                                        <a href="orderHistoryDetail.php?order_id=<?= $order->order_id ?>">
                                            <img src="../../images/member/order/view_icon.png" alt="View Icon" />
                                            <div>View</div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php } ?> 
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php
include '../../_foot.php';
?>