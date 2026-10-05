<?php
include '../_base.php';

if ($_user != '') {
    if ($_user->role == 'Member') {
        redirect('/member/home/home.php');
    } else if ($_user->role == 'Admin' || $_user->role == 'Root') {
        redirect('/admin/adminDashboard.php');
    }
}

// ----------------------------------------------------------------------------

if (is_post()) {
    $password1 = req('password1');
    $password2 = req('password2');
    $token_id = req('token_id');

    if (!$token_id) {
        redirect('/');
    }

    // Validate: password1
    if ($password1 == '') {
        $_err['password1'] = 'Required';
    } else if (strlen($password1) > 20) {
        $_err['password1'] = 'Too Long';
    } else if (strlen($password1) < 6) {
        $_err['password1'] = 'Too Short';
    } else if (!is_strong_password($password1)) {
        $_err['password1'] = 'Password Must Contain: <br>Uppercase, Lowercases,<br> Digit and Symbol';
    }

    // Validate: password2
    if ($password2 == '') {
        $_err['password2'] = 'Required';
    } else if (!($password1 == $password2)) {
        $_err['password2'] = 'Not Match';
    }

    // Update Password if valid
    if (!$_err) {
        // Use token to get user_id
        $stm = $_db->prepare('SELECT * FROM token WHERE token_id = ?');
        $stm->execute([$token_id]);
        $u = $stm->fetch();

        // Delete token and update user password
        $stm = $_db->prepare('
            DELETE FROM token WHERE user_id = ?;

            UPDATE user SET password = ? WHERE user_id = ?;
        ');
        $stm->execute([$u->user_id, sha1($password1), $u->user_id]);

        temp('info', 'Password Resetted');
        redirect('/');
    }
}

// ----------------------------------------------------------------------------

$_title = 'finecamp | Change Password';
$_css_file = '/css/authentication/changePassword.css';
include '../_authenticationHead.php';
?>


<h1 id="header">Change Password</h1>
<span>
    <form method="post">

        <label for="password1" id="label1">New Password</label><br>
        <?= html_password('password1', 'maxlength="20" placeholder="**********"') ?>
        <?= err('password') ?>

        <br>

        <label for="password2">Confirm Password</label><br>
        <?= html_password('password2', 'maxlength="20" placeholder="**********"') ?>
        <?= err('password') ?>

        <br>

        <br>
        <span id="button">
            <button>Confirm</button>
        </span>
    </form>
</span>


<?php
include '../_foot.php';
