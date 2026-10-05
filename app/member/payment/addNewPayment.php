<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_post() && isset($_SESSION['checkout_order_id'])) {

    // Get Data
    $orderId = $_SESSION['checkout_order_id'];
    $paymentMethod = $_SESSION['paymentMethod'];

    // Get Order Items
    $stm = $_db->prepare('SELECT oi.name AS name, 
    oivo.name AS variation, 
    oivo.unit_price AS price, 
    oivo.quantity AS quantity, 
    (oivo.unit_price*oivo.quantity) AS total, 
    oivo.image, 
    oivo.order_item_variation_option_id 
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id 
    JOIN order_item_variation_option oivo ON oi.order_item_id = oivo.order_item_id
    WHERE o.order_id = ?;
    ');

    $stm->execute([$orderId]);
    $order = $stm->fetchAll();
    $count = count($order);

    // Get payment details
    $stm = $_db->prepare('SELECT discount_amount, shipping_charge, tax_charge, subtotal_amount, total_amount
    FROM orders WHERE order_id = ?');
    $stm->execute([$orderId]);
    $paymentDetails = $stm->fetch();

    // Insert Payment Record
    $stm = $_db->prepare('INSERT INTO payment (total, created_datetime, card_type, order_id) VALUES (?, CURRENT_TIMESTAMP(), ?, ?)');
    $stm->execute([$paymentDetails->total_amount, $paymentMethod, $orderId]);



    // Send Receipt
    $m = get_mail();
    $m->Subject = "FINE Camp Receipt #$orderId";
    $m->addAddress($_user->email, $_user->username);
    $m->addEmbeddedImage("../../images/member/payment/fineCampReceiptLogo.jpg", 'photo');
    $m->isHTML(true);
    $m->Body = "
            <img src='cid:photo'
                     style='width: 200px; height: 200px;
                            border: 1px solid #333'>

<h3>Dear $_user->username,</h3>

<h1 style='color: black; '><b>Thank you for your purchase!</b></h1>

<h2>Here is your receipt:</h2>

<h3>
    Order ID: $orderId
</h3>
";

    $tracker = 0;
    while ($count > $tracker):
        $o = $order[$tracker];
        $tracker++;
        $photoId = rand();
        $m->addEmbeddedImage("../../images/member/order/productVariation/$o->order_item_variation_option_id/$o->image", $photoId);
        $m->Body .=
            "
    <div id='itemContainer' style='width: 800px; border-radius: 10px; margin-bottom: 30px; border: black 3px solid; display: flex; align-content: center; justify-content: flex-start; flex-direction: row;'>
        <div>
            <img src='cid:$photoId' alt='Product Image' style='width: 200px; height: 200px; border-radius:20px;'>
        </div>
        <div style='margin-left: 100px;'>
            <p><b>Item name:</b> $o->name</p>
            <p><b>Variation:</b> $o->variation</p>
            <p><b>Unit price:</b> RM$o->price</p>
            <p><b>Quantity:</b> $o->quantity</p>
            <p><b>Subtotal:</b> RM$o->total</p>
        </div>
    </div>
";
    endwhile;

    $m->Body .= "
<div>
    <h3 style='margin-left: 300px;'>Subtotal: RM$paymentDetails->subtotal_amount</h3>
    <h3 style='margin-left: 300px;'>Shipping: RM$paymentDetails->shipping_charge</h3>
    <h3 style='margin-left: 300px;'>Tax: RM$paymentDetails->tax_charge</h3>
    <h3 style='margin-left: 300px;'>Discount: RM$paymentDetails->discount_amount</h3>
    <h3 style='margin-left: 300px;'>Grand Total: RM$paymentDetails->total_amount</h3>
</div>

<br><br>
<h3>From, Fine Camp</h3>
";


    $m->send();

    unset($_SESSION['checkout_order_id']);
    unset($_SESSION['paymentMethod']);

    //Prompt Message
    temp('info', 'Payment Successful, Order Placed. <br> Check your E- mail for Receipt');
    redirect('/member/home/home.php');
} else {
    temp('info', 'Access denied.');
    redirect('/member/order/cart.php');
}



// ----------------------------------------------------------------------------