<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// (1) Authorisation (Member)
auth('authentication/login.php', 'Member');

// (2) Get all shipping details
$stm = $_db->prepare('
    SELECT * FROM `shipping_detail`
    WHERE user_id = ?
');
$stm->execute([$_user->user_id]);
$shippingDetails = $stm->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'finecamp | Shipping Details';
$_css_file = '../../css/member/shippingDetail.css';
include '../../_customerPortalHead.php';
?>

        <div class="shippingDetailPage">
            <div class="shippingDetailPageContainer">
                <div class="shippingDetailContainer">
                    <div class="backBtnContainer">
                        <a class="backBtn" href="../order/cart.php">Back</a>
                    </div>
                    <div class="shippingDetailHeaderContainer">
                        <div class="shippingDetailHeader">SHIPPING DETAILS</div>
                    </div>
                    <div class="shippingDetailSection">
                        <div class="shippingDetailHeaderSection">
                            <div class="shippingDetailTitleContainer">
                                <img src="../../images/member/shippingDetail/shipping_icon.png" alt="Shipping Icon" />
                                <div class="shippingDetailTitle">Shipping Details</div>
                            </div>
                            <div class="addShippingDetailBtnContainer">
                                <a id="addShippingDetailBtn" class="addShippingDetailBtn" href="addShippingDetail.php">&#43;</a>
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
                                    <img src="../../images/member/shippingDetail/edit_icon.png" alt="Edit Icon" data-get="editShippingDetail.php?shipping_detail_id=<?= $shippingDetail->shipping_detail_id ?>"/>
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