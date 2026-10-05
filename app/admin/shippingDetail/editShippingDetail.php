<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Admin, Root)
auth('authentication/loginAdmin.php', 'Admin', 'Root');

// (2) Get user
$selected_user_id = req('user_id');
$stm = $_db->prepare('
    SELECT * FROM `user`
    WHERE user_id = ?
');
$stm->execute([$selected_user_id]);
$selected_user = $stm->fetch();

if (!$selected_user) {
    temp('info', 'Invalid user.');
    redirect('../user/index.php');
} else if ($selected_user->role !== 'Member') {
    temp('info', 'Access denied. (Invalid user)');
    redirect('../user/index.php');
}

// Get initial shipping details
$shipping_detail_id  = req('shipping_detail_id');
$stm = $_db->prepare('SELECT * FROM shipping_detail WHERE shipping_detail_id = ?');
$stm->execute([$shipping_detail_id]);
$shipping_detail = $stm->fetch();

if (!$shipping_detail || $shipping_detail->user_id != $selected_user_id) {
    temp('info', 'Access denied. (Invalid user)');
    redirect('shippingDetail.php');
}

$index = array_search($shipping_detail->state, $_state);
$deformatted_phone_no = substr($shipping_detail->recipient_phone_no, 1);

$GLOBALS['recipientName'] = $shipping_detail->recipient_name;
$GLOBALS['recipientPhoneNo'] = $deformatted_phone_no;
$GLOBALS['addressLine'] = $shipping_detail->address_line;
$GLOBALS['postcode'] = $shipping_detail->postcode;
$GLOBALS['city'] = $shipping_detail->city;
$GLOBALS['state'] = $index !== false ? $index : 0;

// Submit form
if (is_post()) {
    if (isset($_POST['saveBtn'])) {
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

        // Edit shipping details
        if (!$_err) {
            $stm = $_db->prepare('
                UPDATE shipping_detail 
                SET recipient_name = ?, recipient_phone_no = ?, address_line = ?, postcode = ?, city = ?, state = ?
                WHERE shipping_detail_id = ?
            ');

            $formatted_phone_no = '0' . $recipient_phone_no;
            $stm->execute([$recipient_name, $formatted_phone_no, $address_line, $postcode, $city, $_state[$state], $shipping_detail_id]);

            temp('info', 'Shipping details updated successfully.');
            redirect('shippingDetail.php?user_id=' . $selected_user_id);
        }
    } else if (isset($_POST['deleteBtn'])) {
        $stm = $_db->prepare('DELETE FROM shipping_detail WHERE shipping_detail_id = ?');
        $stm->execute([$shipping_detail_id]);
        temp('info', 'Shipping details deleted successfully.');
        redirect('shippingDetail.php?user_id=' . $selected_user_id);
    }
}

// ----------------------------------------------------------------------------

$_title = 'finecamp | Edit Shipping Details [Admin]';
$_css_file = '../../css/admin/editShippingDetail.css';
include '../../_head.php';
?>

        <div class="editShippingDetailPage">
            <div class="editShippingDetailPageContainer">
                <div class="editShippingDetailContainer">
                    <div class="backBtnContainer">
                        <a class="backBtn" href="shippingDetail.php?user_id=<?= $selected_user_id ?>">Back</a>
                    </div>
                    <div class="editShippingDetailHeaderContainer">
                        <div class="editShippingDetailHeader">EDIT SHIPPING DETAILS</div>
                        <div class="selectedUserId">Id: <?= $selected_user_id ?></div>
                    </div>
                    <form id="editShippingDetailForm" method="post" enctype="multipart/form-data">
                        <?php include 'shippingDetailContainer.php' ?>
                        <div class="btnContainer">
                            <?php html_button('saveBtn', 'Save', 'value="save"') ?>
                            <?php html_button('deleteBtn', 'Delete', 'value="delete"') ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script>
        // Confirm to delete
        $('#deleteBtn').on('click', function(e) {
            e.preventDefault();

            if (confirm('Are you sure to delete the shipping details?')) {
                let form = document.getElementById('editShippingDetailForm');
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleteBtn';
                form.appendChild(input);

                form.submit();
            }
        });
    </script>
</body>
</html>

<?php
include '../../_foot.php';
?>