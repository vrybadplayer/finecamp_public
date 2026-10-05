<!--Member site profile -->
<?php


global $origin_photo;

require '../../_base.php';
auth('/authentication/login.php', 'Admin', 'Root', 'Member');
// ----------------------------------------------------------------------------

$id = $_user->user_id; // get the id from the $_user object
$stm = $_db->prepare('SELECT * FROM user WHERE user_id = ?');

$stm->execute([$id]);
$s = $stm->fetch();

$f = $s->photo;
$origin_photo = $f;
$password = $s->password;

if (!$s) { // Record not found in database
    redirect('/');
}

if (is_get()) {
    $photo = $s->photo;
    $username = $s->username;
    $email = $s->email;
}


if (is_post()) {
    $updateUsername = req('updateUsername');
    $updateUserPassword = req('updateUserPassword');
    $updateEmail = req('updateEmail');
    $updatePhoneNo = req('updatePhoneNo');
    // Debug 
    // var_dump($updateUsername);
    // var_dump($updateUserPassword);

    $f = get_file('photo');
    // Validate: photo (file)
    if ($f == null) {
        // $_err['photo'] = 'Required';
    } else if (!str_starts_with($f->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    } else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }

    if ($f && $f != $origin_photo) { //if photo have image and not same as origin photo
        $photo = uniqid() . '.jpg';
        require_once '../../lib/SimpleImage.php';
        $img = new SimpleImage();
        $img->fromFile($f->tmp_name)
            ->thumbnail(200, 200)
            ->toFile("../../images/member/user/uploads/$photo", 'image/jpeg');
        temp('info', 'Photo uploaded');
        $img = new SimpleImage();
        $img->fromFile($f->tmp_name)
            ->thumbnail(200, 200)
            ->toFile("../../images/admin/user/uploads/$photo", 'image/jpeg');
        temp('info', 'Photo uploaded');
    } else {
        $photo = $origin_photo;
    }

    if ($f && $f != $origin_photo) {
        $stm = $_db->prepare('UPDATE user SET photo = ? WHERE user_id = ?');
        $stm->execute([$photo, $id]);
        temp('info', 'Update profile picture Successful');
    }

    if ($updateUsername) {
        $stm = $_db->prepare('UPDATE user SET username = ? WHERE user_id = ?');
        $stm->execute([$updateUsername, $id]);
        $_user->username = $updateUsername; // update the username in the session
        temp('info', 'Update Username Successful');
        redirect('userProfile.php'); //refresh page after update username
    }

    //Validate phone_no
    if ($updatePhoneNo) {
        if (!preg_match('/^01\d{1}-\d{7}$/', $updatePhoneNo)) {
            $_err['updatePhoneNo'] = 'Invalid phone number format : 01x-xxxxxxx (11 digit number)';
        } else if ($updatePhoneNo != $s->phone_no && !is_unique($updatePhoneNo, 'user', 'phone_no')) {
            $_err['updatePhoneNo'] = 'Duplicated phone number ';
        }
    }
    if (!$_err['updatePhoneNo'] && $updatePhoneNo) {
        $stm = $_db->prepare('UPDATE user SET phone_no = ? WHERE user_id = ?');
        $stm->execute([$updatePhoneNo, $id]);
        temp('info', 'Update Phone Number Successful');
    }

    // Validate: password1
    if (strlen($updateUserPassword) > 20) {
        $_err['updateUserPassword'] = 'Too Long';
    } else if (strlen($updateUserPassword) < 6) {
        $_err['updateUserPassword'] = 'Too Short';
    } else if (!is_strong_password($updateUserPassword)) {
        $_err['updateUserPassword'] = 'Password Must Contain: <br>Uppercase, Lowercases,<br> Digit and Symbol';
    }

    if ($updateUserPassword && !$_err['updateUserPassword']) {
        $stm = $_db->prepare('UPDATE user SET password = SHA1(?) WHERE user_id = ?');
        $stm->execute([$updateUserPassword, $id]);
        temp('info', 'Update Password Successful');
    }

    if (!filter_var($updateEmail, FILTER_VALIDATE_EMAIL)) {
        $_err['updateEmail'] = 'Invalid email format';
    } else if (!is_unique($updateEmail, 'user', 'email') && $updateEmail != $s->email) {
        $_err['updateEmail'] = 'Duplicated email';
    }

    if ($updateEmail && !$_err['updateEmail']) {
        //Validate email
        $stm = $_db->prepare('UPDATE user SET email = ? WHERE user_id = ?');
        $stm->execute([$updateEmail, $id]);
        temp('info', 'Update Email Successful');
    }

    redirect('userProfile.php'); //refresh page after update password
}

// ----------------------------------------------------------------------------
$_title = "Member Profile";
$_css_file = '/css/admin/admin.css';
include '../../_customerPortalHead.php';
?>
<?php html_h1('h1', 'Member Profile') ?>
<button data-get="../home/home.php" class="back">Back</button>
<table class="table detail">
    <tr>
        <th>Id</th>
        <td><?= $s->user_id ?>
        </td>
    </tr>
    <tr>
        <th>Photo</th>
        <td>
            <form method="post" enctype="multipart/form-data">
                <label for="photo">Upload your photo</label>
                <label class="upload">
                    <?= html_file('photo', 'image/*', 'hidden') ?>
                    <img src="../../images/member/user/uploads/<?= $photo ?>" alt="Profile Photo">
                </label>
                <?= err('photo') ?>
                <?= html_button('editPhoto', 'Edit', 'type="button"') ?>
                <?= html_button('updatePhotoBtn', 'Update', 'type="submit" style="display: none;"') ?>
            </form>
        </td>
    </tr>
    <!-- Update Username -->
    <tr>
        <th>User Name</th>
        <td>
            <form method="post">
                <?= $s->username ?>
                <?= html_text('updateUsername', 'maxlength="100" style="display: none;"') ?>
                <?= html_button('editUsername', 'Edit', 'type="button"') ?>
                <?= html_button('cancelUsername', 'Cancel', 'type="button" style="display: none;"') ?>
                <?= html_button('updateUsernameBtn', 'Update', 'type="submit" style="display: none;"') ?>
            </form>
        </td>
    </tr>

    <!-- Update Password -->
    <tr>
        <th>Password</th>
        <td>
            <form method="post">
            ********
                <?= html_password('updateUserPassword', 'maxlength="100" style="display: none;"') ?>
                <?= html_button('editPassword', 'Edit', 'type="button"') ?>
                <?= html_button('cancelPassword', 'Cancel', 'type="button" style="display: none;"') ?>
                <?= html_button('updatePassword', 'Update', 'type="submit" style="display: none;"') ?>
            </form>
        </td>
    </tr>

    <!-- Update Email -->
    <tr>
        <th>Email</th>
        <td>
            <form method="post">
                <?= $s->email ?>
                <?= html_text('updateEmail', 'maxlength="100" style="display: none;"') ?>
                <?= html_button('editEmail', 'Edit', 'type="button"') ?>
                <?= html_button('cancelEmail', 'Cancel', 'type="button" style="display: none;"') ?>
                <?= html_button('updateEmailBtn', 'Update', 'type="submit" style="display: none;"') ?>
            </form>
        </td>
    </tr>

    <!-- Update Phone Number -->
    <tr>
        <th>Phone Number</th>
        <td>
            <form method="post">
                <?= $s->phone_no ?>
                <?= html_text('updatePhoneNo', 'maxlength="100" style="display: none;"') ?>
                <?= html_button('editPhoneNo', 'Edit', 'type="button"') ?>
                <?= html_button('cancelPhoneNo', 'Cancel', 'type="button" style="display: none;"') ?>
                <?= html_button('updatePhoneNoBtn', 'Update', 'type="submit" style="display: none;"') ?>
            </form>
        </td>
    </tr>


</table>

<br>

<?php
include '../../_foot.php';
?>