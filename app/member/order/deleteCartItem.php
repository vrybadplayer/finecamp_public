<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_get() || is_post()) {
    $cart_item_id = req('selectId', []);

    if ($cart_item_id) {
        // (1) Handle multiple ids
        if (!is_array($cart_item_id)) $cart_item_id = [$cart_item_id];

        foreach ($cart_item_id as $id) {
            if (!is_exists($id, 'cart_item', 'cart_item_id')) {
                temp('info', "Invalid cart item ID.");
                redirect('cart.php');
            }
        }

        $stm = $_db->prepare('DELETE FROM cart_item WHERE cart_item_id = ?');
        $n = 0;

        // (2) Delete mutiple records
        foreach ($cart_item_id as $v) {
            $n += $stm->execute([$v]);
        } 
        
        temp('info', "$n cart item(s) removed");
    }
}

redirect('cart.php');

// ----------------------------------------------------------------------------