<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_post() && $_POST['source'] === 'payment') {
    $cart_item_ids = $_SESSION['checkout_cart_item_ids'];
    $shipping_detail_id = $_SESSION['checkout_shipping_detail_id'];

    $total_weight = 0;
    $subtotal_price = 0;

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
            } else { // Valid
                $total_weight += $variation->weight * $cart_item->quantity;
                $subtotal_price += $variation->unit_price * $cart_item->quantity;
            }
        }
    }

    // Shipping charge
    $shipping_charge = calculateShippingCharge($total_weight);

    // Tax (10%)
    $tax = $subtotal_price * 0.1;

    // Discount (Every RM200, discount RM20)
    $discount = calculateDiscount($subtotal_price);

    // Total
    $total = $subtotal_price + $shipping_charge + $tax - $discount;

    // Get shipping detail
    $stm = $_db->prepare('
    SELECT * FROM `shipping_detail`
    WHERE shipping_detail_id = ?
    ');
    $stm->execute([$shipping_detail_id]);
    $shipping_detail = $stm->fetch();

    // Duplicate shipping detail
    $stm = $_db->prepare('
        INSERT INTO shipping_detail (recipient_name, recipient_phone_no, address_line, postcode, city, state)
        VALUES (?, ?, ?, ?, ?, ?)
    ');

    $stm->execute([$shipping_detail->recipient_name, $shipping_detail->recipient_phone_no, $shipping_detail->address_line, $shipping_detail->postcode, $shipping_detail->city, $shipping_detail->state]);
    // Get inserted shipping detail id
    $duplicated_shipping_detail_id = $_db->lastInsertId();

    // Create new order
    $stm = $_db->prepare('
        INSERT INTO orders (created_datetime, discount_amount, shipping_charge, tax_charge, subtotal_amount, total_amount, status, shipping_detail_id, user_id)
        VALUES (CURRENT_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stm->execute([sprintf("%.2f", $discount), sprintf("%.2f", $shipping_charge), sprintf("%.2f", $tax), sprintf("%.2f", $subtotal_price), sprintf("%.2f", $total), 'created', $duplicated_shipping_detail_id, $_user->user_id]);
    // Get inserted order id
    $new_order_id = $_db->lastInsertId();

    $groupedItems = [];
    foreach ($cart_item_ids as $id) { 
        // Get cart item
        $stm = $_db->prepare('
        SELECT * FROM `cart_item`
        WHERE cart_item_id = ?
        ');
        $stm->execute([$id]);
        $cart_item = $stm->fetch();

        $current_product_id = $cart_item->product_id;

        // Group cart items by product_id
        if (!isset($groupedItems[$current_product_id])) {
            $groupedItems[$current_product_id] = [];
        }
        $groupedItems[$current_product_id][] = $cart_item;

    }

    foreach ($groupedItems as $product_id => $cart_items) {
        // Get product
        $stm = $_db->prepare('
            SELECT * FROM `product`
            WHERE product_id = ?
        ');
        $stm->execute([$product_id]);
        $product = $stm->fetch();

        // Create new order_item
        $stm = $_db->prepare('
            INSERT INTO order_item (name, variation_name, product_id, order_id)
            VALUES (?, ?, ?, ?)
        ');
        $stm->execute([$product->name, $product->variation_name, $product->product_id, $new_order_id]);
        // Get inserted order_item_id
        $new_order_item_id = $_db->lastInsertId();

        foreach ($cart_items as $cart_item) {
            // Get variation
            $stm = $_db->prepare('
            SELECT * FROM `product_variation_option`
            WHERE product_variation_option_id = ?
            ');
            $stm->execute([$cart_item->product_variation_option_id]);
            $variation = $stm->fetch();

            // Create new order_item_variation_option
            $stm = $_db->prepare('
                INSERT INTO order_item_variation_option (name, unit_price, quantity, image, weight, order_item_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stm->execute([$variation->name, $variation->unit_price, $cart_item->quantity, $variation->image, $variation->weight, $new_order_item_id]);
            $new_order_item_variation_option_id = $_db->lastInsertId();

            // Copy product picture to new order folder
            $source_image_path = '../../images/admin/product/product_variations_image/' . $variation->image;
            $destination_folder = '../../images/member/order/productVariation/' . $new_order_item_variation_option_id;

            if (!file_exists($destination_folder)) {
                mkdir($destination_folder, 0777, true);
            }

            $destination_image_path = $destination_folder . '/' . $variation->image;
            @copy($source_image_path, $destination_image_path);

            // Minus stock qty
            $deducted_stock_qty = $variation->stock_quantity - $cart_item->quantity;

            $stm = $_db->prepare('
                UPDATE product_variation_option 
                SET stock_quantity = ?
                WHERE product_variation_option_id = ?
            ');

            $stm->execute([$deducted_stock_qty, $variation->product_variation_option_id]);

            // Delete cart items from cart
            $stm = $_db->prepare('DELETE FROM cart_item WHERE cart_item_id = ?');
            $stm->execute([$cart_item->cart_item_id]);
        }
    }

    // Go to addNewPayment.php
    unset($_SESSION['checkout_cart_item_ids']);
    unset($_SESSION['checkout_shipping_detail_id']);
    $_SESSION['checkout_order_id'] = $new_order_id;
    
    echo '<form id="redirectForm" action="../payment/addNewPayment.php" method="post">';
    echo '<input type="hidden" name="source" value="addNewOrder">';
    echo '</form>';
    echo '<script type="text/javascript">document.getElementById("redirectForm").submit();</script>';

} else {
    temp('info', 'Access denied.');
    redirect('cart.php');
}

// Calculate shipping charge
function calculateShippingCharge($total_weight) {
    if ($total_weight > 0 && $total_weight <= 1) { // RM5 per kg if 0 < x <= 1
        return ceil($total_weight) * 5; 
    } else if ($total_weight > 1 && $total_weight <= 5) { // RM4.5 per kg if 1 < x <= 5
        return ceil($total_weight) * 4.5; 
    } else if ($total_weight > 5) { // RM4 per kg if x > 5
        return ceil($total_weight) * 4;
    } else { // Invalid
        return 0;
    }
}

// Calculate discount
function calculateDiscount($subtotal_price) {
    return floor($subtotal_price / 200) * 20;
}

// ----------------------------------------------------------------------------