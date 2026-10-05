<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

auth('authentication/login.php', 'Member');

if (is_get()) {
    $product_id = req('product_id');
    $variation_id = req('variation_id');
    $quantity = req('quantity');

    // Get product
    $stm = $_db->prepare('
    SELECT * FROM `product`
    WHERE product_id = ?
    ');
    $stm->execute([$product_id]);
    $product = $stm->fetch();

    // Get variation
    $stm = $_db->prepare('
    SELECT * FROM `product_variation_option`
    WHERE product_variation_option_id = ?
    ');
    $stm->execute([$variation_id]);
    $variation = $stm->fetch();

    if ($product && $product->status == 'active') { // Got product & product available
        if ($variation) { // Got variation
            if ($quantity > 0) { // Quantity larger than 0
                if ($quantity <= $variation->stock_quantity) { // Enough stock qty
                    // Check if item already exists in cart
                    $stm = $_db->prepare('
                    SELECT * FROM `cart_item`
                    WHERE product_variation_option_id = ? AND user_id = ?
                    ');
                    $stm->execute([$variation_id, $_user->user_id]);
                    $selected_cart_item = $stm->fetch();
    
                    if ($selected_cart_item) { // Product already inside cart
                        $added_cart_item_num = intval($quantity) + intval($selected_cart_item->quantity);
                        if ($added_cart_item_num <= $variation->stock_quantity) { // Sufficient quantity
                            // Update cart item
                            $stm = $_db->prepare('
                                UPDATE cart_item 
                                SET quantity = ?
                                WHERE cart_item_id = ?
                            ');
    
                            $stm->execute([$added_cart_item_num, $selected_cart_item->cart_item_id]);
                            temp('info', "Item has been successfully added to cart.");
                        } else { // Insufficient quantity
                            temp('info', "Exceed stock quantity.");
                        }
                    } else { // Product not inside cart
                        // Add cart item
                        $stm = $_db->prepare('
                        INSERT INTO cart_item (quantity, user_id, product_id, product_variation_option_id)
                        VALUES (?, ?, ?, ?)
                        ');
                        $stm->execute([$quantity, $_user->user_id, $product_id, $variation_id]);
                        temp('info', "Item has been successfully added to cart.");
                    }
                } else {
                    temp('info', "Exceed stock quantity.");
                }
            } else {
                temp('info', "Quantity must be at least 1.");
            }
        } else {
            temp('info', "Variation unavailable.");
        }
    } else {
        temp('info', "Product unavailable.");
    }

    
}

redirect('../product/productDetail.php?productId=' . $product_id);

// ----------------------------------------------------------------------------