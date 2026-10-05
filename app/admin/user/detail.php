<?php
require '../../_base.php';
//-----------------------------------------------------------------------------
auth('index.php', 'Admin', 'Root');  //only admin and root can access this page



$id = req('id'); //get the id

//check admin can not access to root detail and update page
if ($id == 7) {
    auth('index.php', 'Root');
}


$stm = $_db->prepare('
    SELECT u.*, s.address_line, s.postcode, s.city, s.state
    FROM user u
    LEFT JOIN shipping_detail s ON u.user_id = s.user_id
    WHERE u.user_id = ?
');
//
$stm->execute([$id]);
$s = $stm->fetch();
//print_r($s);
$f = $s->photo;


if (!$s) { // Record not found in database
    redirect('../../index.php');
}




// ----------------------------------------------------------------------------
$_title = 'Detail';
$_css_file = '/css/admin/admin.css';
include '../../_head.php';
?>
<?php

if ($s->role == 'Admin' || $s->role == 'Root') {
    html_h1('h1', 'Admin Detail');
} else {
    html_h1('h1', 'Member Detail');
}
?>

<?php if ($_user->role == 'Root'): ?>
    <button data-get="index.php" class="back">Back to Member List</button>
    <button data-get="../adminMemberList.php" class="back">Back to Admin List</button>
<?php else: ?>
    <button data-get="index.php" class="back">Back to Member List</button>
<?php endif; ?>
<table class="table detail">
    <tr>
        <th>Id</th>
        <td><?= $s->user_id ?></td>
    </tr>
    <tr>
        <th>User Name</th>
        <td><?= $s->username ?></td>
    </tr>
    <tr>
        <th>Password</th>
        <td><?= $s->password ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?= $s->email ?></td>
    </tr>
    <tr>
        <th>Phone Number</th>
        <td><?= $s->phone_no ?></td>
    </tr>
    <tr>
        <th>Photo</th>
        <td><img src="../../../images/admin/user/uploads/<?= $f ?>" alt="User Photo"></td>
    </tr>
    <tr>
        <th>Role</th>
        <td><?= $s->role ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?= $s->status ?></td>
    </tr>
    <?php if ($s->role == 'Member'): ?>
        <tr>
            <th>Shipping Details</th>
            <td>
                <div class="viewShippingDetailsContainer">
                    <a id="viewShippingDetails" href="../shippingDetail/shippingDetail.php?user_id=<?= $s->user_id ?>">View Shipping Details</a>
                </div>
            </td>
        </tr>
    <?php endif; ?>
</table>

<br>



<?php
include '../../_foot.php';
