<?php
include '../_base.php';
$_title = 'finecamp | Forgot Password';
include '../_authenticationHead.php';

if ($_user != '') {
    if ($_user->role == 'Member') {
        redirect('/member/home/home.php');
    } else if ($_user->role == 'Admin' || $_user->role == 'Root') {
        redirect('/admin/adminDashboard.php');
    }
}

// ----------------------------------------------------------------------------

if (is_post()) {
    $username = req('username');
    $email = req('email');

    //Validate: username
    if ($username == '') {
        $_err['username'] = 'Required';
    }

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if (!is_exists($email, 'user', 'email')) {
        $_err['email'] = 'Not exists';
    }

    // Send reset token (if valid)
    if (!$_err) {
        // TODO: (1) Select user
        $stm = $_db->prepare('SELECT * FROM user WHERE email= ? AND username = ?');
        $stm->execute([$email, $username]);
        $u = $stm->fetch();

        if ($u) {
            // TODO: (2) Generate token id
            $id = sha1(uniqid() . rand());

            // TODO: (3) Delete old and insert new token
            $stm = $_db->prepare('
            DELETE FROM token WHERE user_id = ?;

            INSERT INTO token (token_id, expire, user_id) VALUES (?, ADDTIME(NOW(), "00:05"), ?);
            ');
            $stm->execute([$u->user_id, $id, $u->user_id]);

            // TODO: (4) Generate token url
            $url = base("authentication/changePassword.php?token_id=$id");

            // TODO: (5) Send email
            $m = get_mail();
            $m->addAddress($u->email, $u->username);
            $m->Subject = "Reset Password";

            if ($u->photo != '') {
                $m->addEmbeddedImage("../images/member/user/uploads/$u->photo", 'photo');
            } else {
                $m->addEmbeddedImage("../images/authentication/fineCampLogo.jpg", 'photo');
            }

            $m->isHTML(true);
            $m->Body = "
            <img src='cid:photo'
                 style='width: 200px; height: 200px;
                        border: 1px solid #333'>
            <p>Dear $u->name,<p>
            <h1 style='color: red'>Reset Password</h1>
            <p>
                Please click <a href='$url'>here</a>
                to reset your password.
            </p>
            <p>Fine Camp</p>
            ";
            $m->send();
            temp('info', 'Email sent');
            redirect('/');
        } else {
            temp('info', 'Invalid User');
            redirect('');
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Forgot Password';
$_css_file = '/css/authentication/forgotPassword.css';
include '../_head.php';
?>


<h1 id="header">Forgot Password</h1>
<span>
    <form method="post">

        <label for="username">Username</label><br>
        <?= html_text('username', 'maxlength="100" placeholder="John Doe"') ?>
        <?= err('username') ?>

        <br>

        <label for="email">Email</label><br>
        <?= html_text('email', 'maxlength="100" placeholder="example@gmail.com"') ?>
        <?= err('email') ?>

        <br>

        <span id="link">
            <a href="register.php" id="register">Register</a>
        </span>

        <br>
        <span id="button">
            <button>Send Email</button>
        </span>
    </form>
</span>


<?php
include '../_foot.php';
