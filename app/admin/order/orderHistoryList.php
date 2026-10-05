<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Admin, Root)
auth('authentication/loginAdmin.php', 'Admin', 'Root');

// (2) Get all order details
$stm = $_db->prepare('
    SELECT COUNT(*) FROM `orders`
');
$stm->execute([]);
$totalNumOfOrders = $stm->fetchColumn();

$history_query = "
    SELECT o.order_id, o.total_amount, o.status, o.user_id, oi.name AS product_name, oi.variation_name, oivo.order_item_variation_option_id, oivo.name AS variation_option_name, oivo.unit_price, oivo.quantity, oivo.image, oivo.weight
    FROM orders o JOIN order_item oi
    ON o.order_id = oi.order_id
    JOIN order_item_variation_option oivo
    ON oi.order_item_id = oivo.order_item_id
";

$search_input = req('search_input') ?? '';
$order_status = req('order_status') ?? 'all';

$params = [];
// Have search input
if ($search_input) {
    $history_query .= " AND (o.order_id LIKE :search_query
    OR o.user_id LIKE :search_query
    OR o.order_id IN (
                    SELECT DISTINCT o2.order_id
                    FROM orders o2
                    JOIN order_item oi2 ON o2.order_id = oi2.order_id
                    WHERE oi2.name LIKE :search_query)
    OR o.total_amount LIKE :search_query)
                ";
    
    $params[':search_query'] = '%' . $search_input . '%';
}

// Have specific order status
if ($order_status !== '' && $order_status !== 'all') {
    $history_query .= " AND o.status = :order_status";
    $params[':order_status'] = $order_status;
}

$fields = [
    'order_id' => ['label' => 'Order ID', 'sortable' => true],
    'user_id' => ['label' => 'User ID', 'sortable' => true],
    'item' => ['label' => 'Item(s)', 'sortable' => false],
    'total_amount' => ['label' => 'Total Amount', 'sortable' => true],
    'status' => ['label' => 'Status', 'sortable' => true],
    'action' => ['label' => 'Action', 'sortable' => false]
];

$sort = req('sort');
key_exists($sort, $fields) || $sort = 'order_id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

// Default ORDER BY
$history_query .= " ORDER BY $sort $dir";

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

$_title = 'finecamp | Order List [Admin]';
$_css_file = '../../css/admin/orderHistoryList.css';
include '../../_head.php';
?>
        <div class="orderHistoryPage">
            <div class="orderHistoryPageContainer">
                <button data-get="/admin/adminDashboard.php" class="back">Back to Dashboard</button>
                <div class="orderHistoryContainer"> 
                    <div class="orderHistoryHeaderContainer">
                        <div class="orderHistoryHeader">
                            Order List
                        </div>
                        <div class="orderHistoryNoOfItems">(<?= $totalNumOfOrders ?>) Record(s)</div>
                    </div>
                    <div class="searchSection">
                        <form method="get" action="orderHistoryList.php">
                            <?= html_search('search_input', 'placeholder="Search by order ID, user ID, product name or total amount"') ?>
                            <?= html_select('order_status', $status_option, null) ?>
                            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
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
                        <table class="table allOrderContainer">
                            <tr>
                                <?= table_headers_2($fields, $sort, $dir) ?>
                            </tr>

                            <?php foreach ($orders as $order_id => $orderItems): 
                                $order = $orderItems[0]; ?>
                                <tr>
                                    <td><?= $order->order_id ?></td>
                                    <td><?= $order->user_id ?></td>
                                    <td class="itemContainer">
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="itemRow">
                                            <div><?= $item->product_name ?></div>
                                            <div>(<?= $item->variation_name ?>: <?= $item->variation_option_name ?>)</div>
                                            <div>x<?= $item->quantity ?><br></div>
                                        </div>
                                    <?php endforeach; ?>
                                    </td>
                                    <td><?= $order->total_amount ?></td>
                                    <td><?= ucfirst($order->status) ?></td>
                                    <td class="actionContainer">
                                        <div>
                                            <a href="orderHistoryDetail.php?order_id=<?= $order->order_id ?>">
                                                <img src="../../images/admin/order/view_icon.png" alt="View Icon" />
                                                <div>View</div>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php } ?> 
                </div>
            </div>
        </div>
    </main>
    <script>
        $('button, th a').on('click', function(e) {
            e.preventDefault();

            // Get search_input & order_status
            let search_input = $('#search_input').val();
            let order_status = $('#order_status').val();

            // Get sort & dir on URL parameter
            let sort = '<?= $sort ?>';
            let dir = '<?= $dir ?>';

            // If click table header
            if ($(this).is('a')) {
                let href = new URLSearchParams($(this).attr('href').split('?')[1]);
                sort = href.get('sort') || sort;
                dir = href.get('dir') || dir;
            }

            // Create URL
            let url = '?search_input=' + search_input + 
                      '&order_status=' + order_status +
                      '&sort=' + sort + 
                      '&dir=' + dir;
                
            // Redirect to the URL
            window.location.href = url;
        });
    </script>
</body>
</html>

<?php
include '../../_foot.php';
?>