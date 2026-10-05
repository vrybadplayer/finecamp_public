<?php


$role_option = [
    'Member' => 'Member'
];


$status_option = [
    'active' => 'active',
    'inactive' => 'inactive',
    'terminated' => 'terminated'
];

global $origin_photo;

require '../../_base.php';
$id = req('id');
$stm = $_db->prepare('SELECT * FROM user WHERE user_id = ?');
$stm->execute([$id]);
$s = $stm->fetch();
$selected_user_role = $s->role;
// ----------------------------------------------------------------------------

if (is_get()) {
    // 
    // $id = req('id');
    // $stm = $_db->prepare('SELECT * FROM user WHERE user_id = ?');
    // $stm->execute([$id]);
    // $s = $stm->fetch();
    // $selected_user_role = $s->role;
    $f = $s->photo;
    $origin_photo = $f;

    //prevent unauthorized access to the root detail page
    auth('index.php','Admin', 'Root');  //only admin and root can access this page

    //check admin can not access to root detail and update page
if ($id == 7) {
    auth('index.php', 'Root');
}

    if (!$s) {
        redirect('../../index.php');
    }

    extract((array)$s);
}

if (is_post()) {
    // Input
    $user_id = req('id'); //get  id
    $username   = req('username');
    $email      = req('email');
    $password   = req('password');
    $confirm_password = req('confirm-password');
    $phone_no   = req('phone_no');
    $role      = $selected_user_role;
    $status     = req('status');
    //  $photo      = req('photo');

    $f = get_file('photo');
    // Validate: photo (file)
    if ($f == null) {
        // $_err['photo'] = 'Required';
    } else if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    } else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }

    // $role       = req('role');
    $status     = req('status');

    // // Validate id
    //     //create member
    //     $stm = $_db->prepare("SELECT COUNT(*) FROM user WHERE user_id = ?");
    //     $stm->execute([$user_id]);

    //     if ($stm->fetchColumn() > 0) {
    //         $_err['id'] = 'Duplicated id';
    //     }


    // Validate username
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (!is_unique($username, 'user', 'username')) {
        //  $_err['username'] = 'Duplicated ';
    }

    //Validate password
    if($password && $password != $s->password) { //if password is not empty and not same as origin password
     // Validate: password1
    if (strlen($password) > 20) {
        $_err['password'] = 'Too Long';
    } else if (strlen($password) < 6) {
        $_err['password'] = 'Too Short';
    } else if (!is_strong_password($password)) {
        $_err['password'] = 'Password Must Contain: <br>Uppercase, Lowercases,<br> Digit and Symbol';
    }
  }
  if($password && !$_err['password']){
    $stm = $_db->prepare('UPDATE user SET password = SHA1(?) WHERE user_id = ?');
    $stm->execute([$password, $user_id]);
 } 


    if ($confirm_password) { //if confirm password is not empty and valid execute update
        if ($password != $confirm_password) {
            $_err['confirm-password'] = 'Password not match';
        }
    }

    //    // Validate role (fix as member for current )
    //     if (!in_array($role, $role_option )) {
    //         $_err['role'] = 'Invalid role';
    //     }
    //     if ($role != 'Member' || $role == 'Admin') {
    //         $_err['role'] = 'Invalid role';
    //     }

    //validate email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Invalid email format';
    } else if (!is_unique($email, 'user', 'email')) {
        //   $_err['email'] = 'Duplicated email';
    }

    // }
    //Validate phone_no
    if ($phone_no == '') {
        $_err['phone_no'] = 'Required';
    } else if (!preg_match('/^01\d{1}-\d{7}$/', $phone_no)) {
        $_err['phone_no'] = 'Invalid phone number format : 01x-xxxxxxx';
    }
    // else if (!is_unique($phone_no, 'user', 'phone_no')) {
    //     $_err['phone_no'] = 'Duplicated phone number format ';
    // }

    //Validate status
    if ($status == '') {
        $_err['status'] = 'Required';
    } else if (!in_array($status, $status_option)) {
        //$_err['status'] = 'Invalid status';
    }

    if ($f && $f != $origin_photo) { //if photo have image and not same as origin photo
        $photo = uniqid() . '.jpg';
        require_once '../../lib/SimpleImage.php';
        $img = new SimpleImage();
        $img->fromFile($f->tmp_name)
            ->thumbnail(200, 200)
            ->toFile("../../images/admin/user/uploads/$photo", 'image/jpeg');
        temp('info', 'Photo uploaded'); //update photo member site also
        $img = new SimpleImage();
        $img->fromFile($f->tmp_name)
            ->thumbnail(200, 200)
            ->toFile("../../images/member/user/uploads/$photo", 'image/jpeg');
        
    } else {
        $photo = $origin_photo;
    }

    if (!$_err) {
        if ($f && $f != $origin_photo) {
            $stm = $_db->prepare('UPDATE user SET username = ?, email = ?, phone_no = ?, photo = ?, role = ?, status = ? WHERE user_id = ?');
            $stm->execute([$username,$email, $phone_no, $photo, $selected_user_role, $status, $user_id]);
        } else {
            $stm = $_db->prepare('UPDATE user SET username = ?, email = ?, phone_no = ?, role = ?, status = ? WHERE user_id = ?');
            $stm->execute([$username, $email, $phone_no, $selected_user_role, $status, $user_id]);
        }

        temp('info', 'Update Successful!');
        redirect("../adminDashboard.php");
    }
}

// ----------------------------------------------------------------------------
$_title = 'Update Member Profile';
$_css_file = '/css/admin/admin.css';
include '../../_head.php';
?>
<?php
if ($s->role == 'Admin' || $s->role == 'Root') {
    html_h1('h1', 'Update Admin');
} else {
    html_h1('h1', 'Update Member');
}
?>

<?php if ($_user->role == 'Root'): ?>
    <button data-get="index.php" class="back">Back to Member List</button>
    <button data-get="../adminMemberList.php" class="back">Back to Admin List</button>
<?php else: ?>
    <button data-get="index.php" class="back">Back to Member List</button>
<?php endif; ?>

<form method="post" class="form2" enctype="multipart/form-data">
    <label for="user_id">User ID</label>
    <?= html_text('user_id', 'maxlength="10" data-upper disabled') ?>
    <?= err('user_id') ?>


    <label for="username">Username</label>
    <?= html_text('username', 'maxlength="100"') ?>
    <?= err('username') ?>

    <label for="password">Password</label>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?>


    <label for="confirm-password">Confirm Password</label>
    <?= html_password('confirm-password', 'maxlength="100"') ?>
    <?= err('confirm-password') ?>

    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>


    <label for="phone_no">Phone Number</label>
    <?= html_text('phone_no', 'maxlength="20"') ?>
    <?= err('phone_no') ?>


    <label for="photo">Upload your photo</label>
    <label class="upload">
        <?= html_file('photo', 'image/*', 'hidden') ?>
        <img src="../../../images/admin/user/uploads/<?= $f ?>"> </label>
    <?= err('photo') ?>

    <?= html_h2("h2", "Member Privilege") ?>
    <?= html_blank_label() ?><?= html_blank_label() ?>

    <label for="">Current Role :<?= $role ?></label>
    <?php
    $status_color = ($status == 'terminated' || $status == 'inactive') ? 'red' : 'black';
    ?>
    <label for="">Current Status:</label> <span style="color: <?= $status_color ?>;"><?= $status ?></span>

    <!-- <label for="role">Role</label>
        <?= html_select('role', $role_option) ?>
    <?= err('role') ?> -->


    <label for="status">status</label>
    <?= html_select('status', $status_option) ?>
    <?= err('status') ?>

    <section>
        <button id="btn">Submit</button>
        <button type="reset" id="btn">Reset</button>
    </section>
</form>

<?php
include '../../_foot.php';
