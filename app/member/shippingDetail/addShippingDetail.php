<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Member)
auth('authentication/login.php', 'Member');

// (2) Get total number of shipping details
$stm = $_db->prepare('
    SELECT COUNT(*) FROM `shipping_detail`
    WHERE user_id = ?
');
$stm->execute([$_user->user_id]);
$numOfShippingDetails = $stm->fetchAll();

// Check if already 3 shippping details
if ($numOfShippingDetails[0]->{'COUNT(*)'} == 3) {
    temp('info', 'You can only add up to 3 shipping details.');
    redirect('shippingDetail.php');
}

// Submit form
if (is_post()) {
    $recipient_name = req('recipientName');
    $recipient_phone_no = req('recipientPhoneNo');
    $address_line = req('addressLine');
    $postcode = req('postcode');
    $city = req('city');
    $state = req('state');

    $GLOBALS['recipientName'] = $recipient_name;
    $GLOBALS['recipientPhoneNo'] = $recipient_phone_no;
    $GLOBALS['addressLine'] = $address_line;
    $GLOBALS['postcode'] = $postcode;
    $GLOBALS['city'] = $city;
    $GLOBALS['state'] = $state;

    // Validate shipping details
    include 'validateShippingDetail.php';

    // Add new shipping details
    if (!$_err) {
        $stm = $_db->prepare('
            INSERT INTO shipping_detail (user_id, recipient_name, recipient_phone_no, address_line, postcode, city, state)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        $formatted_phone_no = '0' . $recipient_phone_no;
        $stm->execute([$_user->user_id, $recipient_name, $formatted_phone_no, $address_line, $postcode, $city, $_state[$state]]);

        temp('info', 'New shipping details added successfully.');
        redirect('shippingDetail.php');
    }
}

// ----------------------------------------------------------------------------

$_title = 'finecamp | Add Shipping Details';
$_css_file = '../../css/member/addShippingDetail.css';
include '../../_customerPortalHead.php';
?>

        <div class="newShippingDetailPage">
            <div class="newShippingDetailPageContainer">
                <div class="newShippingDetailContainer">
                    <div class="backBtnContainer">
                        <a class="backBtn" href="shippingDetail.php">Back</a>
                    </div>
                    <div class="newShippingDetailHeaderContainer">
                        <div class="newShippingDetailHeader">NEW SHIPPING DETAILS</div>
                    </div>
                    <form id="addShippingDetailForm" method="post" enctype="multipart/form-data">
                        <?php include 'shippingDetailContainer.php' ?>
                        <div class="btnContainer">
                            <?php html_button('addBtn', 'Add') ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php
include '../../_foot.php';
?>