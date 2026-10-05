<?php
include '../_base.php';

if ($_user) {
    logout();
}


//------------------------------------------------------



//------------------------------------------------------

$_title = 'finecamp | Portal';
$_css_file = '/css/authentication/portal.css';
include '../_head.php';
?>




<h1 id="welcome"><b>Welcome To</b></h1>

<span id="title">
    <img src="../images/authentication/fineCampLogo.jpg" id="logo" />
</span>

<span id="buttons">
    <a href="loginAdmin.php" class="button"><img src="../images/authentication/adminPortal.png" id="button"></a>
    <a href="/member/home/home.php" class="button"><img src="../images/authentication/customerPortal.png" id="button"></a>
</span>