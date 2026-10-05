<?php
include '../_base.php';
$_title = 'finecamp | Register';
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

    $email = req('email');
    $username = req('username');
    $password1 = req('password1');
    $password2 = req('password2');
    $phoneNo = req('phoneNo');
    $regex = "/^\d{3}-\d{7}|011-\d{8}$/";

    // Validate: username
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (strlen($username) > 100) {
        $_err['username'] = 'Too Long';
    }

    // Validate: password
    if ($password1 == '') {
        $_err['password1'] = 'Required';
    } else if (strlen($password1) > 20) {
        $_err['password1'] = 'Too Long';
    } else if (strlen($password1) < 6) {
        $_err['password1'] = 'Too Short';
    } else if (!is_strong_password($password1)) {
        $_err['password1'] = 'Password Must Contain: <br>Uppercase, Lowercases,<br> Digit and Symbol';
    }

    // Validate: confirm password
    if ($password2 == '') {
        $_err['password2'] = 'Required';
    } else if (!($password2 == $password1)) {
        $_err['password2'] = 'Not Match';
    }

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if (is_exists($email, 'user', 'email')) {
        $_err['email'] = 'Email already used';
    }

    //Validate: phone number
    if ($phoneNo == '') {
        $_err['phoneNo'] = 'Required';
    } else if (!preg_match($regex, $phoneNo)) {
        $_err['phoneNo'] = 'Invalid';
    }

    // Store user information into database
    if (!$_err) {

        // Prepare Statement
        $stm = $_db->prepare('INSERT INTO user (username, password, 
        email, phone_no, photo, role, status, try) VALUES (?,?,?,?,?,?,?, 0)');

        // Execute INSERT
        $stm->execute([$username, sha1($password1), $email, $phoneNo, 'default.jpg', 'Member', 'inactive']);

        // Send Verification Email
        $stm = $_db->prepare('SELECT * FROM user WHERE username = ? AND email = ?');
        $stm->execute([$username, $email]);
        $u = $stm->fetch();

        // Generate token id
        $id = sha1(uniqid() . rand());

        // TODO: (3) Delete old and insert new token
        $stm = $_db->prepare('
         DELETE FROM token WHERE user_id = ?;

         INSERT INTO token (token_id, expire, user_id) VALUES (?, ADDTIME(NOW(), "00:05"), ?);
         ');
        $stm->execute([$u->user_id, $id, $u->user_id]);

        // TODO: (4) Generate token url
        $url = base("authentication/registerVerification.php?token_id=$id");

        // TODO: (5) Send email
        $m = get_mail();
        $m->addAddress($email, $username);
        $m->Subject = "Verify Email";
        $m->addEmbeddedImage("../images/authentication/fineCampLogo.jpg", 'photo');


        $m->isHTML(true);
        $m->Body = "
         <img src='cid:photo'
              style='width: 200px; height: 200px;
                     border: 1px solid #333'>
         <p>Dear $username,<p>
         <h1 style='color: red'>Verify Email</h1>
         <p>
             Please click <a href='$url'>here</a>
             to verify your email.
         </p>
         <p>Fine Camp</p>
         ";
        $m->send();

        temp('info', 'Check E-mail for Verification');
        redirect('/');
    }
}

// ----------------------------------------------------------------------------

$_css_file = '../css/authentication/register.css';
$_title = 'Register';
include '../_head.php';
?>



<h1 id="header">Register Account</h1>
<span>
    <form method="post">

        <label for="username" id="label1">Username</label><br>
        <?= html_text('username', 'maxlength="100" placeholder="John Doe"') ?>
        <?= err('username') ?>

        <br>

        <label for="password1" id="label2">Password</label><br>
        <?= html_password('password1', 'maxlength="20" placeholder="**********"') ?>
        <?= err('password1') ?>

        <br>

        <label for="password2" id="label3">Confirm Password</label><br>
        <?= html_password('password2', 'maxlength="20" placeholder="**********"') ?>
        <?= err('password2') ?>

        <br>

        <label for="email" id="label4">E-mail</label><br>
        <?= html_text('email', 'maxlength="100" placeholder="example@gmail.com"') ?>
        <?= err('email') ?>

        <br>
        <label for="phoneNo" id="label5">Phone No.</label><br>
        <?= html_text('phoneNo', 'maxlength="12" placeholder="e.g. 012-3456789"') ?>
        <?= err('phoneNo') ?>

        <br>
        <br>
        <span id="button">
            <button>Register</button>
        </span>
    </form>
</span>


<?php
include '../_foot.php';
