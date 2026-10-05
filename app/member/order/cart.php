<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Member)
auth('authentication/login.php', 'Member');

// (2) Get all cart items
$stm = $_db->prepare('
    SELECT * FROM `cart_item`
    WHERE user_id = ?
');
$stm->execute([$_user->user_id]);
$cartItems = $stm->fetchAll();

// (3) Get all shipping details
$stm = $_db->prepare('
    SELECT * FROM `shipping_detail`
    WHERE user_id = ?
    ORDER BY shipping_detail_id
');
$stm->execute([$_user->user_id]);
$shippingDetails = $stm->fetchAll();

$shippingDetailsOptions = [];
foreach ($shippingDetails as $sd) {
    $shippingDetailsOptions[] = ' ( ' . $sd->recipient_name . ', ' . $sd->recipient_phone_no . ' ) ' .
                                $sd->address_line . ', ' . 
                                $sd->postcode . ', ' . $sd->city . ', ' . $sd->state;
}

if (!$shippingDetailsOptions) {
    $shippingDetailsOptions[0] = 'No shipping details';
}

// Submit
if (is_post()) {
    $btn = req('btn');
    $qty = req('qty');
    $cart_item_id = req('cart_item_id');

    if ($btn == 'edit' && $qty) {
        if (!ctype_digit($qty)) { // Quantity is not digit
            temp('info', "Invalid quantity.");
        } else if ($qty <= 0) { // Quantity less than/equal 0
            temp('info', "Quantity must be at least 1.");
        } else { // Change qty
            updateQty($_db, $qty, $cart_item_id);
        }
    } else if ($btn == 'select' && $cart_item_id) { // Change variation options
        $product_variation_option = req('variation_options_' . $cart_item_id);

        $stm = $_db->prepare('
            SELECT * FROM `cart_item`
            WHERE cart_item_id = ?
        ');
        $stm->execute([$cart_item_id]);
        $selectedCartItem = $stm->fetch();
        
        $test_all_pvo_stm = $_db->prepare('SELECT * FROM product_variation_option WHERE product_id = ?');
        $test_all_pvo_stm->execute([$selectedCartItem->product_id]);
        $selectedVariationOptions = $test_all_pvo_stm->fetchAll();
        $variation_id = $selectedVariationOptions[$product_variation_option]->product_variation_option_id;

        // Get variation
        $stm = $_db->prepare('
            SELECT * FROM `product_variation_option`
            WHERE product_variation_option_id = ?
        ');
        $stm->execute([$variation_id]);
        $variation = $stm->fetch();

        // Check if item already exists in cart
        $stm = $_db->prepare('
            SELECT * FROM `cart_item`
            WHERE product_variation_option_id = ? AND user_id = ?
        ');
        $stm->execute([$variation_id, $_user->user_id]);
        $exist_cart_item = $stm->fetch();

        // Selected cart item
        $stm = $_db->prepare('
            SELECT * FROM `cart_item`
            WHERE cart_item_id = ?
        ');
        $stm->execute([$cart_item_id]);
        $selected_cart_item = $stm->fetch();

        if ($exist_cart_item) { // Product already inside cart
            $added_cart_item_num = intval($selected_cart_item->quantity) + intval($exist_cart_item->quantity);
            if ($added_cart_item_num > 0) { // Quantity larger than 0
                if ($added_cart_item_num <= $variation->stock_quantity) { // Sufficient quantity
                    // Update exist cart item's quantity
                    $stm = $_db->prepare('
                        UPDATE cart_item 
                        SET quantity = ?
                        WHERE cart_item_id = ?
                    ');
    
                    $stm->execute([$added_cart_item_num, $exist_cart_item->cart_item_id]);
    
                    // Remove selected cart item
                    $stm = $_db->prepare('
                        DELETE FROM cart_item 
                        WHERE cart_item_id = ?
                    ');
    
                    $stm->execute([$selected_cart_item->cart_item_id]);
    
                    temp('info', "Item variation changed successfully.");
                } else { // Insufficient quantity
                    // Get product
                    $stm = $_db->prepare('
                        SELECT * FROM `product`
                        WHERE product_id = ?
                    ');
                    $stm->execute([$exist_cart_item->product_id]);
                    $product = $stm->fetch();
    
                    temp('info', 'Exceed stock quantity (' . $product->name . ', ' . $variation->name . ', ' . 'Stock: ' . $variation->stock_quantity . ')');
                }
            } else { // Quantity less than/equal 0
                temp('info', "Quantity must be at least 1.");
            }
        } else { // Product not inside cart
            // Update cart item's variation
            update_cart($cart_item_id, $variation_id);
            temp('info', "Item variation changed successfully.");
        }
    }

    redirect();
}

// Functions
function updateQty($_db, $qty, $cart_item_id) {
    $stm = $_db->prepare('
        UPDATE cart_item 
        SET quantity = ?
        WHERE cart_item_id = ?
    ');

    $stm->execute([$qty, $cart_item_id]);
}

// ----------------------------------------------------------------------------

$_title = 'finecamp | Shopping Cart';
$_css_file = '../../css/member/cart.css';
include '../../_customerPortalHead.php';
?>
        <div class="shoppingCartPage">
            <div class="shoppingCartPageContainer">
                <div class="shoppingCartContainer">
                    <div class="shoppingCartHeaderContainer">
                        <div class="shoppingCartHeader">
                            <img src="../../images/base/cart_icon.png" alt="Cart Icon" />
                            Shopping Cart
                        </div>
                        <div class="shoppingCartNoOfItems">(<?= count($cartItems) ?>) Item(s)</div>
                    </div>
                    <?php if ($cartItems == null) { ?>
                        <div class="noRecordContainer">
                            No cart items found.
                        </div>
                    <?php } else { ?> 
                    <div class="shoppingCartSection">
                        <div class="shoppingCartHeaderSection">
                            <div class="shoppingCartTableHeaderContainer">
                                <div>Product Details</div>
                                <div>Unit Price</div>
                                <div>Quantity</div>
                                <div>Final Price</div>
                                <div></div>
                            </div>
                        </div>
                        <div class="allCartItemContainer">
                            <?php
                                // Statements
                                $p_stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
                                $pvo_stm = $_db->prepare('SELECT * FROM product_variation_option WHERE product_variation_option_id = ?');
                                $all_pvo_stm = $_db->prepare('SELECT * FROM product_variation_option WHERE product_id = ?');

                                foreach ($cartItems as $cartItem): 
                                    // Get product
                                    $p_stm->execute([$cartItem->product_id]);
                                    $p = $p_stm->fetch();
                                    
                                    // Get product variation option
                                    $pvo_stm->execute([$cartItem->product_variation_option_id]);
                                    $pvo = $pvo_stm->fetch();

                                    // Get all product variation options
                                    $all_pvo_stm->execute([$cartItem->product_id]);
                                    $all_pvo = $all_pvo_stm->fetchAll();
                                    $pvo_name = [];
                                    foreach ($all_pvo as $pvo_item) {
                                        $pvo_name[] = $pvo_item->name;
                                    }

                                    if (!$p) { // No product
                                    ?>
                                <div class="shoppingCartCard">
                                    <div class="selectContainer"></div>
                                    <div class="productInfoContainer">
                                        <img src="../../images/member/order/image_not_found.png" alt="Product Image" />
                                        <div class="productInfo">
                                            <div>Unavailable</div>
                                            <div>Weight: - kg</div>
                                        </div>
                                    </div>
                                    <div class="unitPriceContainer">
                                        RM -
                                    </div>
                                    <div class="quantityContainer">
                                        <div id="qty"><?= $cartItem->quantity ?></div>
                                    </div>
                                    <div class="finalPriceContainer">
                                        RM -
                                    </div>
                                    <div class="deleteContainer">
                                        <img src="../../images/member/order/delete_icon.png" alt="Delete Icon" data-url="deleteCartItem.php?selectId=<?= $cartItem->cart_item_id ?>" onclick="confirmDelete(this)" />
                                    </div>
                                </div>
                                    <?php } else if ($p->status !== 'active') { // Product inactive/terminated 
                                        // Final price
                                        $finalPrice = $pvo->unit_price * $cartItem->quantity;
                                    ?> 
                                <div class="shoppingCartCard">
                                    <div class="selectContainer"></div>
                                    <div class="productInfoContainer">
                                        <img src="../../images/admin/product/product_variations_image/<?= $pvo->image ?>" alt="Product Image" />
                                        <div class="productInfo">
                                            <div><?= $p->name ?> (Unavailable)</div>
                                            <div class="variation_options_container">
                                                <div><?= $p->variation_name ?>: <?= $pvo->name ?></div>
                                            </div>
                                            <div>Weight: <?= $pvo->weight ?> kg</div>
                                        </div>
                                    </div>
                                    <div class="unitPriceContainer">
                                        RM <?= $pvo->unit_price ?>
                                    </div>
                                    <div class="quantityContainer">
                                        <div id="qty"><?= $cartItem->quantity ?></div>
                                    </div>
                                    <div class="finalPriceContainer">
                                        RM <?= sprintf("%.2f", $finalPrice) ?>
                                    </div>
                                    <div class="deleteContainer">
                                        <img src="../../images/member/order/delete_icon.png" alt="Delete Icon" data-url="deleteCartItem.php?selectId=<?= $cartItem->cart_item_id ?>" onclick="confirmDelete(this)" />
                                    </div>
                                </div>
                                    <?php } else if (!$pvo) { // No product variation option ?>
                                <div class="shoppingCartCard">
                                    <div class="selectContainer"></div>
                                    <div class="productInfoContainer">
                                        <img src="../../images/member/order/image_not_found.png" alt="Product Image" />
                                        <div class="productInfo">
                                            <div><?= $p->name ?></div>
                                            <div class="variation_options_container">
                                                <div><?= $p->variation_name ?>: - (Unavailable)</div>
                                            </div>
                                            <div>Weight: - kg</div>
                                        </div>
                                    </div>
                                    <div class="unitPriceContainer">
                                        RM -
                                    </div>
                                    <div class="quantityContainer">
                                        <div id="qty"><?= $cartItem->quantity ?></div>
                                    </div>
                                    <div class="finalPriceContainer">
                                        RM -
                                    </div>
                                    <div class="deleteContainer">
                                        <img src="../../images/member/order/delete_icon.png" alt="Delete Icon" data-url="deleteCartItem.php?selectId=<?= $cartItem->cart_item_id ?>" onclick="confirmDelete(this)" />
                                    </div>
                                </div>
                                    <?php } else { // Valid
                                        $index = array_search($pvo->name, $pvo_name);
                                        $GLOBALS['cart_item_id'] = $cartItem->cart_item_id;
                                        $GLOBALS['variation_options_' . $cartItem->cart_item_id] = $index !== false ? $index : 0;
                                        $GLOBALS['qty'] = $cartItem->quantity;
    
                                        // Final price
                                        $finalPrice = $pvo->unit_price * $cartItem->quantity;
                                    ?>
                                <div class="shoppingCartCard">
                                    <div class="selectContainer">
                                        <input type="checkbox"
                                            name="selectId[]"
                                            value="<?= $cartItem->cart_item_id ?>"
                                            form="cartAction">

                                        <!-- Hidden input for calculation -->
                                        <input type="hidden" value="<?= $pvo->weight ?>" class="weight">
                                        <input type="hidden" value="<?= $pvo->unit_price ?>" class="unit_price">
                                        <input type="hidden" value="<?= $cartItem->quantity ?>" class="quantity">
                                    </div>
                                    <div class="productInfoContainer">
                                        <img src="../../images/admin/product/product_variations_image/<?= $pvo->image ?>" alt="Product Image" />
                                        <form class="productInfo" method="post">
                                            <div><?= $p->name ?></div>
                                            <?= html_hidden('cart_item_id') ?>
                                            <div class="variation_options_container">
                                                <div><?= $p->variation_name ?>:</div>
                                                <?php $variation_option_name = 'variation_options_' . $cartItem->cart_item_id ?>
                                                <?php html_select($variation_option_name, $pvo_name, null, 'class="variation_options"') ?>
                                            </div>
                                            <div>
                                                Weight: <?= $pvo->weight ?> kg
                                            </div>
                                        </form>
                                    </div>
                                    <div class="unitPriceContainer">
                                        RM <?= sprintf("%.2f", $pvo->unit_price) ?>
                                    </div>
                                    <form class="quantityContainer" method="post">
                                        <?php html_hidden('cart_item_id'); ?>
                                        <?php html_number('qty', '1', $pvo->stock_quantity, '1') ?>
                                    </form>
                                    <div class="finalPriceContainer">
                                        RM <?= sprintf("%.2f", $finalPrice) ?>
                                    </div>
                                    <div class="deleteContainer">
                                        <img src="../../images/member/order/delete_icon.png" alt="Delete Icon" data-url="deleteCartItem.php?selectId=<?= $cartItem->cart_item_id ?>" onclick="confirmDelete(this)" />
                                    </div>
                                </div>
                            <?php   unset($GLOBALS['variation_options']);
                                    } endforeach; ?>
                            <div class="actionContainer">
                                <?php html_button('selectAll', 'Select All') ?>
                                <form method="post" id="cartAction">
                                    <?php html_button('deleteSelected', 'Delete (<span id="checkedCount">0</span>)') ?>
                                </form>
                            </div>
                            <div class="addressPaymentContainer">
                                <div>
                                    <div class="deliveryAddressContainer">
                                        <div class="deliveryAddressHeader">Delivery Address</div>
                                        <?php html_select('deliveryAddress', $shippingDetailsOptions, null, 'form="checkoutForm"') ?>
                                        <div class="addEdit">
                                            <a href="../shippingDetail/shippingDetail.php">Add/Edit</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="paymentDetailsContainer">
                                    <div class="paymentDetailsHeader">Payment Details</div>
                                    <div class="amountContainer">
                                        <div>
                                            <div>Subtotal</div>
                                            <div name="subtotal">RM 0.00</div>
                                        </div>
                                        <div>
                                            <div>Shipping Charge</div>
                                            <div name="shippingCharge">RM 0.00</div>
                                        </div>
                                        <div>
                                            <div>Tax (10%)</div>
                                            <div name="tax">RM 0.00</div>
                                        </div>
                                        <div>
                                            <div>Discount</div>
                                            <div name="discount">- RM 0.00</div>
                                        </div>
                                        <div>
                                            <div><b>Total</b></div>
                                            <b><div name="total">RM 0.00</div></b>
                                        </div>
                                    </div>
                                    <form class="checkoutContainer" id="checkoutForm" method="post">
                                        <?php html_button('checkout', 'Checkout (0)') ?>
                                    </form>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="../../js/member/cart.js"></script>
</body>
</html>

<?php
include '../../_foot.php';
?>