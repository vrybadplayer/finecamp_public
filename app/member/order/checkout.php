<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $cart_item_ids = req('cart_item_id');
    $selected_delivery_address_index = req('deliveryAddress');

    // Get all shipping details
    $stm = $_db->prepare('
    SELECT * FROM `shipping_detail`
    WHERE user_id = ?
    ORDER BY shipping_detail_id
    ');
    $stm->execute([$_user->user_id]);
    $shippingDetails = $stm->fetchAll();

    // Check if correct index passed in
    if ($selected_delivery_address_index < 0 || $selected_delivery_address_index >= count($shippingDetails)) {
        temp('info', 'Invalid delivery address.');
        redirect('cart.php');
    }

    // Valid index
    $shipping_detail_id = $shippingDetails[$selected_delivery_address_index]->shipping_detail_id;

    foreach ($cart_item_ids as $id) {
        // Get cart item
        $stm = $_db->prepare('
        SELECT * FROM `cart_item`
        WHERE cart_item_id = ?
        ');
        $stm->execute([$id]);
        $cart_item = $stm->fetch();

        if (!$cart_item) {
            temp('info', 'Invalid cart_item_id (' . $id . ')');
            redirect('cart.php');
        } else {
            // Get product
            $stm = $_db->prepare('
            SELECT * FROM `product`
            WHERE product_id = ?
            ');
            $stm->execute([$cart_item->product_id]);
            $product = $stm->fetch();

            // Get variation
            $stm = $_db->prepare('
            SELECT * FROM `product_variation_option`
            WHERE product_variation_option_id = ?
            ');
            $stm->execute([$cart_item->product_variation_option_id]);
            $variation = $stm->fetch();

            if (!$product) { // No product
                temp('info', 'Invalid product_id (' . $cart_item->product_id . ')');
                redirect('cart.php');
            } else if ($product->status !== 'active') { // Product inactive/terminated
                temp('info', 'Unavailable product (' . $product->product_id . ', ' . $product->name . ')');
                redirect('cart.php');
            } else if (!$variation) { // No variation
                temp('info', 'Invalid product_variation_option_id (' . $cart_item->product_variation_option_id . ')');
                redirect('cart.php');
            } else if ($cart_item->quantity <= 0) { // Quantity less than/equal 0
                temp('info', "Quantity must be at least 1.");
                redirect('cart.php');
            } else if ($cart_item->quantity > $variation->stock_quantity) { // Insufficient stock qty
                temp('info', 'Exceed stock quantity (' . $product->name . ', ' . $variation->name . ', ' . 'Stock: ' . $variation->stock_quantity . ')');
                redirect('cart.php');
            }
        }
    }

    // Valid
    // Pass data as post
    echo '<form id="redirectForm" action="../payment/payment.php" method="post">';
    foreach ($cart_item_ids as $cart_item_id) {
        echo '<input type="hidden" name="checkout_cart_item_ids[]" value="' . htmlspecialchars($cart_item_id) . '">';
    }
    
    echo '<input type="hidden" name="checkout_shipping_detail_id" value="' . htmlspecialchars($shipping_detail_id) . '">';
    echo '<input type="hidden" name="source" value="checkout">';
    echo '</form>';
    echo '<script type="text/javascript">document.getElementById("redirectForm").submit();</script>';
} else {
    temp('info', 'Access denied.');
    redirect('cart.php');
}


// ----------------------------------------------------------------------------