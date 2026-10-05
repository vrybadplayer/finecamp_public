<?php
$_css_file = '../../css/member/payment.css';
$_title = 'finecamp | Payment';
include '../../_base.php';
include '../../_authenticationHead.php';

auth('member/home/home.php', 'Member');

//-----------------------------------

if (
    isset($_POST['source']) && $_POST['source'] === 'checkout' &&
    isset($_POST['checkout_cart_item_ids']) && isset($_POST['checkout_shipping_detail_id'])
) { // From checkout.php, and have cart_item_ids and shipping_detail_id
    $_SESSION['checkout_cart_item_ids'] = $_POST['checkout_cart_item_ids'];
    $_SESSION['checkout_shipping_detail_id'] = $_POST['checkout_shipping_detail_id'];
    echo '<input type="hidden" name="source" value="payment" form="paymentForm">';
}

if ((!isset($_POST['source']) || $_POST['source'] !== 'checkout') && (!isset($_POST['source']) || $_POST['source'] !== 'payment')) {
    temp('info', 'Access denied.');
    redirect('../order/cart.php');
} else {


    if (is_post() && ((isset($_POST['source']) && ($_POST['source'] === 'payment' || $_POST['source'] === 'checkout')))) {
        echo '<input type="hidden" name="source" value="payment" form="paymentForm">';

        $paymentMethod = req('paymentMethod');
        $cardName = req('cardName');
        $cardNumber = req('cardNumber');
        $cvv = req('cvv');
        $date = req('date');
        $cardRegex = "/^\d{16}$/";
        $dateRegex = "/^(0[1-9]|1[0-2])\/[0-9]{2}$/";
        $cvvRegex = "/^\d{3}$/";

        $_SESSION['paymentMethod'] = req('paymentMethod');

        // Validate: payment method
        if ($paymentMethod == '- Select One -') {
            $_err['paymentMethod'] = 'Required';
        } else if (!in_array($paymentMethod, $_paymentMethod)) {
            $_err['paymentMethod'] = 'Required';
        }

        // Validate card holder name
        if ($cardName == '') {
            $_err['cardName'] = 'Required';
        }

        // Validate card number
        if ($cardNumber == '') {
            $_err['cardNumber'] = 'Required';
        } else if (!preg_match($cardRegex, $cardNumber)) {
            $_err['cardNumber'] = 'Invalid';
        }

        //Validate date
        if ($date == '') {
            $_err['date'] = 'Required';
        } else if (!preg_match($dateRegex, $date)) {
            $_err['date'] = 'Invalid';
        }

        // Validate CVV
        if ($cvv == '') {
            $_err['cvv'] = 'Required';
        } else if (!preg_match($cvvRegex, $cvv)) {
            $_err['cvv'] = 'Invalid';
        } else if (strlen($cvv) != 3) {
            $_err['cvv'] = 'Invalid';
        }


        // Validate with BANK CARD
        if (!$_err) {
            $stm = $_db->prepare(('SELECT * FROM bank_card WHERE number = ?'));
            $stm->execute([$cardNumber]);
            $bankCard = $stm->fetch();

            if ($bankCard) {

                if ($bankCard->number != $cardNumber) {
                    $_err['cardNumber'] = 'Invalid Card';
                }

                if ($bankCard->name != $cardName) {
                    $_err['cardNumber'] = 'Invalid Card';
                }

                if ($bankCard->type != $paymentMethod) {
                    $_err['cardNumber'] = 'Invalid Card';
                }

                if ($bankCard->expiry_date != $date) {
                    $_err['cardNumber'] = 'Invalid Card';
                }

                if ($bankCard->cvv != sha1($cvv)) {
                    $_err['cardNumber'] = 'Invalid Card';
                }
            } else {
                $_err['cardNumber'] = 'Invalid Card';
                temp('info', 'Invalid Card');
            }
        }



        // Create Payment (if no error)
        if (!$_err) {
            // Temporarily Store Payment Details
            $_SESSION['tempPaymentMethod'] = $paymentMethod;
            $_SESSION['tempCardName'] = $cardName;
            $_SESSION['tempCardNumber'] = $cardNumber;
            $_SESSION['tempDate'] = $date;
            $_SESSION['tempId'] = $_user->user_id;

            // Go addNewOrder.php
            unset($_SESSION['source']);
            echo '<form id="redirectForm" action="../order/addNewOrder.php" method="post">';
            echo '<input type="hidden" name="source" value="payment">';
            echo '</form>';
            echo '<script type="text/javascript">document.getElementById("redirectForm").submit();</script>';
        }
    }
}

// ----------------------------------------------------------------------------



?>


<span id="back">
    <a href="/member/order/cart.php">Back</a>
</span>

<span>

    <form method="post" id="paymentForm">

        <h1>Payment</h1>

        <!-- Payment Section -->
        <label for="paymentMethod" id="label1">Payment Method:</label><br>
        <?= html_select('paymentMethod', $_paymentMethod) ?>
        <?= err('paymentMethod') ?>

        <br>

        <label for="name" id="label2">Card Holder Name: </label><br>
        <?= html_text('cardName', 'maxlength="100" placeholder="John Doe"') ?>
        <?= err('cardName') ?>

        <br>

        <label for="cardNumber" id="label3">Card Number: </label><br>
        <?= html_text('cardNumber', 'maxlength="16" placeholder="xxxxxxxxxxxxxxxx"') ?>
        <?= err('cardNumber') ?>

        <br>

        <label for="cvv" id="label4">CVV: </label><br>
        <?= html_password('cvv', 'maxlength="3" placeholder="***"') ?>
        <?= err('cvv') ?>

        <br>
        <label for="date" id="label5">Valid Until: </label><br>
        <?= html_text('date', 'maxlength="5" placeholder="MM/YY"') ?>
        <?= err('date') ?>

        <br>



        <!-- Form Button -->
        <br>
        <span id="button">
            <button data-confirm>Proceed</button>
        </span>

    </form>

</span>

<script>
    $('select[name="userCard"]').on('change', function(e) {
        e.target.form.submit();
    });
</script>

<?php
include '../../_foot.php';
