<?php
include '../_base.php';
$_title = 'finecamp | Admin Login';
include '../_authenticationHead.php';


// ----------------------------------------------------------------------------
if ($_user != '') {
    if ($_user->role == 'Member') {
        redirect('/member/home/home.php');
    } else if ($_user->role == 'Admin' || $_user->role == 'Root') {
        redirect('/admin/adminDashboard.php');
    }
}

if (is_post()) {
    $email    = req('email');
    $password = req('password');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) > 20) {
        $_err['password'] = 'Error';
    }

    // Login user
    if (!$_err) {
        // TODO
        $stm = $_db->prepare('
            SELECT * FROM user
            WHERE email = ? AND password = SHA1(?)
        ');
        $stm->execute([$email, $password]);
        $u = $stm->fetch();

        if ($u) {
            if (($u->role == 'Admin' || $u->role == 'Root') && $u->status == 'active') {

                $stm = $_db->prepare('UPDATE user SET try = ? WHERE email= ?;');
                $stm->execute(['0', $email]);

                temp('info', 'Login successfully');
                adminLogin($u);
            } else if ($u->role == 'Member') {
                temp('info', 'Account is not admin');
                redirect();
            } else {
                temp('info', 'Account Inactive or Terminated');
                redirect();
            }
        } else {
            $_err['password'] = 'Not Matched';

            $stm = $_db->prepare('SELECT try FROM user WHERE email = ?;');
            $stm->execute([$email]);
            $try = (int)$stm->fetchColumn();
            $try += 1;

            $stm = $_db->prepare('UPDATE user SET try = ? WHERE email = ?;');
            $stm->execute([$try, $email]);

            if ($try >= 3) {
                $stm = $_db->prepare('UPDATE user SET status = ? WHERE email = ?;');
                $stm->execute(['inactive', $email]);
                temp('info', 'Account Locked. Check Email to Unlock');

                // Send Unblock Email
                $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
                $stm->execute([$email]);
                $u = $stm->fetch();
                $m->Subject = "Account Temporarily Blocked";
                // Generate token id
                $id = sha1(uniqid() . rand());

                // TODO: (3) Delete old and insert new token
                $stm = $_db->prepare('
                DELETE FROM token WHERE user_id = ?;

                INSERT INTO token (token_id, expire, user_id) VALUES (?, ADDTIME(NOW(), "00:05"), ?);
                ');
                $stm->execute([$u->user_id, $id, $u->user_id]);

                // TODO: (4) Generate token url
                $url = base("authentication/unlockAccount.php?token_id=$id");

                // TODO: (5) Send email
                $m = get_mail();
                $m->addAddress($email, $u->username);

                $m->addEmbeddedImage("../images/authentication/fineCampLogo.jpg", 'photo');


                $m->isHTML(true);
                $m->Body = "
                <img src='cid:photo'
                style='width: 200px; height: 200px; border: 1px solid #333'>
                <p>Dear user,<p>
                <h1 style='color: red'>Unblock Email</h1>
                <p>
                Please click <a href='$url'>here</a>
                to unblock your email.
                </p>
                <p>Fine Camp</p>
                ";
                $m->send();
            }
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Login';
$_css_file = '/css/authentication/login.css';
include '../_head.php';
?>


<h1 id="header">Admin Login</h1>
<span>
    <form method="post">

        <label for="email" id="email">Email</label><br>
        <?= html_text('email', 'maxlength="100" placeholder="example@gmail.com"') ?>
        <?= err('email') ?>

        <br>

        <label for="password">Password</label><br>
        <?= html_password('password', 'maxlength="20" placeholder="**********"') ?>
        <?= err('password') ?>

        <br>

        <span id="link">
            <a href="register.php" id="register">Register</a>
            <a href="forgotPassword.php" id="forgotPassword">Forgot Password</a>
        </span>

        <br>
        <span id="button">
            <button>Login</button>
        </span>
    </form>
</span>

<?php
include '../_foot.php';
