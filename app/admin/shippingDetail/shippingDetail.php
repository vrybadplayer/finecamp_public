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

// (3) Get all shipping details
$stm = $_db->prepare('
    SELECT * FROM `shipping_detail`
    WHERE user_id = ?
');
$stm->execute([$selected_user_id]);
$shippingDetails = $stm->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'finecamp | Shipping Details [Admin]';
$_css_file = '../../css/admin/shippingDetail.css';
include '../../_head.php';
?>

        <div class="shippingDetailPage">
            <div class="shippingDetailPageContainer">
                <div class="shippingDetailContainer">
                    <div class="backBtnContainer">
                        <a class="backBtn" href="../user/detail.php?id=<?= $selected_user_id ?>">Back</a>
                    </div>
                    <div class="shippingDetailHeaderContainer">
                        <div class="shippingDetailHeader">SHIPPING DETAILS</div>
                        <div class="selectedUserId">Id: <?= $selected_user_id ?></div>
                    </div>
                    <div class="shippingDetailSection">
                        <div class="shippingDetailHeaderSection">
                            <div class="shippingDetailTitleContainer">
                                <img src="../../images/member/shippingDetail/shipping_icon.png" alt="Shipping Icon" />
                                <div class="shippingDetailTitle">Shipping Details</div>
                            </div>
                            <div class="addShippingDetailBtnContainer">
                                <a id="addShippingDetailBtn" class="addShippingDetailBtn" href="addShippingDetail.php?user_id=<?= $selected_user_id ?>">&#43;</a>
                            </div>
                        </div>
                        <div class="allShippingDetailContainer">
                            <?php if ($shippingDetails == null) { ?>
                                <div class="noRecordContainer">
                                    No shipping details found.
                                </div>
                            <?php
                                } else { 
                                    foreach ($shippingDetails as $shippingDetail): ?>
                            <div class="shippingDetailCard">
                                <div class="shippingDetail">
                                    <div class="recipientName"><?= $shippingDetail->recipient_name ?></div>
                                    <div><?= $shippingDetail->recipient_phone_no ?></div>
                                    <div><?= $shippingDetail->address_line ?></div>
                                    <div><?= $shippingDetail->postcode ?>&nbsp;<?= $shippingDetail->city ?>,&nbsp;<?= $shippingDetail->state ?></div>
                                </div>
                                <div class="editBtnContainer">
                                    <img src="../../images/member/shippingDetail/edit_icon.png" alt="Edit Icon" data-get="editShippingDetail.php?user_id=<?= $selected_user_id ?>&shipping_detail_id=<?= $shippingDetail->shipping_detail_id ?>"/>
                                </div>
                            </div>
                            <?php endforeach; } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        // Check if already 3 shipping details
        let numOfShippingDetails = "<?= count($shippingDetails); ?>";
        $('#addShippingDetailBtn').on('click', function(e) {
            if (numOfShippingDetails == "3") {
                e.preventDefault();
                alert('You can only add up to 3 shipping details.');
            }
        });
    </script>
</body>
</html>

<?php
include '../../_foot.php';
?>