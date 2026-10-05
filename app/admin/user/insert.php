<?php
require '../../_base.php';
auth('index.php','Admin', 'Root');  //only admin and root can access this page
// ----------------------------------------------------------------------------
$role_option = [ 
    'Member' => 'Member'
];


 $status_option = [ //status option
    'active' => 'active',
    'inactive' => 'inactive', //block
    'terminated' => 'terminated' //delete
];

if(is_get()){
    $user_id = $_db->query("SELECT MAX(user_id) FROM user")->fetchColumn() + 1; //get lastest id

}

if (is_post()) {
        // Input
    $user_id = $_db->query("SELECT MAX(user_id) FROM user")->fetchColumn() + 1; //get lastest id
    $username   = req('username');
    $password   = req('password');
    $confirm_password = req('confirm-password');
    $email      = req('email');
    $phone_no   = req('phone_no');
  //  $photo      = req('photo');

    $f = get_file('photo'); 

    // Validate: photo (file)
    if ($f == null) {
        $_err['photo'] = 'Required';
    }
    else if (!str_starts_with($f->type,'image/')) { 
        $_err['photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) { 
        $_err['photo'] = 'Maximum 1MB';
    }

    if (!$_err) {
        //move_uploaded_file($f->tmp_name, "uploads/$f->name"); //save the file to uploads folder
        // save photo
        $photo = uniqid() . '.jpg';
    require_once '../../lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail(200, 200)
        ->toFile("../../images/admin/user/uploads/$photo", 'image/jpeg');
        temp('info', 'Photo uploaded');
   // redirect(); //no need redirect
}

    $role       = req('role');
    $status     = req('status');

    // Validate id
        //create member
        $stm = $_db->prepare("SELECT COUNT(*) FROM user WHERE user_id = ?");
        $stm->execute([$user_id]);

        if ($stm->fetchColumn() > 0) {
            $_err['id'] = 'Duplicated id';
        }
    

    // Validate username
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (!is_unique($username, 'user', 'username')) {
        $_err['username'] = 'Duplicated ';
    }

    //Validate password
    // Validate: password1
    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) > 20) {
        $_err['password'] = 'Too Long';
    } else if (strlen($password) < 6) {
        $_err['password'] = 'Too Short';
    } else if (!is_strong_password($password)) {
        $_err['password'] = 'Password Must Contain: <br>Uppercase, Lowercases,<br> Digit and Symbol';
    }

    if ($confirm_password == '') {
        $_err['confirm-password'] = 'Required';
    }else if ($password != $confirm_password) {
        $_err['confirm-password'] = 'Password not match';
    }

    //Validate email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Invalid email format';
    } else if (!is_unique($email, 'user', 'email')) {
        $_err['email'] = 'Duplicated email';
    }

    //Validate role (fix as member for current )
    if (!in_array($role, $role_option)) {
     $_err['role'] = 'Invalid role';
    if($role != 'Member'){
    $_err['role'] = 'Invalid role'; //to ensure the role is member
    } 
    if($role == ''){
        $_err['role'] = 'Required'; 
    }
}

    // }
    //Validate phone_no
    if ($phone_no == '') {
        $_err['phone_no'] = 'Required';
    } else if (!preg_match('/^01\d{1}-\d{7}$/', $phone_no)) {
        $_err['phone_no'] = 'Invalid phone number format : 01x-xxxxxxx'; 
    } 
    else if (!is_unique($phone_no, 'user', 'phone_no')) {
        $_err['phone_no'] = 'Duplicated phone number format ';
    }

    //Validate status
    if ($status == '') {
        $_err['status'] = 'Required';
    } else if (!in_array($status, $status_option)) {
        //$_err['status'] = 'Invalid status';
    }

    // Output
    if (!$_err) {
        $stm = $_db->prepare('INSERT INTO user (user_id, username, password, email, phone_no, photo, role, status) VALUES (?, ?, SHA1(?), ?, ?, ?, ?, ?)');
        $stm->execute([$user_id, $username, $password, $email, $phone_no, $photo, $role, $status]);
        temp('info', 'New Member created successfully');
        redirect("index.php");
    }

}
// ----------------------------------------------------------------------------
$_title = 'Create New Member';
$_css_file = '/css/admin/admin.css';
include '../../_head.php';

?>
<?=html_h1('h1',$_title) ?>
<button data-get="index.php" class="back">Back to Member List</button>
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
    <?= html_file('photo','image/*','hidden') ?> 
        <img src="/images/base/imagelogo.png"> </label>
    <?= err('photo') ?>

    <label for="role">Role</label>
    <!-- member need selection or fix as member ?-->
    <?= html_select('role', $role_option) ?>
    <?= err('role') ?>

    <label for="status">status</label>
    <?= html_select('status', $status_option) ?>
    <?= err('status') ?>

    <section>
        <button class="back">Submit</button>
        <button type="reset" class="back">Reset</button>
    </section>
</form>

<?php
include '../../_foot.php';
