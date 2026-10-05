<!DOCTYPE html>
<html lang="en">

<?php
// Initialize cart item count to 0 as the default value
$numOfCartItems = 0;

// Check if the user is logged in 
if (isset($_user)) {

  //get current user
  $stm = $_db->prepare('SELECT * FROM user WHERE user_id = ?');
  $stm->execute([$_user->user_id]);
  $currentUser = $stm->fetch();

  if ($currentUser !== null && $currentUser->role == 'Member' && $currentUser->status == 'active') {
    $stm = $_db->prepare('
        SELECT COUNT(*) FROM cart_item
        WHERE user_id = ?
    ');
    $stm->execute([$currentUser->user_id]);
    $numOfCartItems = $stm->fetchColumn();
  }
}

?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $_title ?? 'Untitled' ?></title>
  <!-- <link rel="shortcut icon" href=""> -->
  <link rel="shortcut icon" href="/images/base/logo.jpg" type="image/x-icon">
  <script src="/js/base/jquery-3.7.1.min.js"></script>
  <script src="/js/admin/app.js"></script>
  <link rel="stylesheet" href="/css/base/app.css">
  <link rel="stylesheet" href="/css/member/header.css">
  <link rel="stylesheet" href="<?= $_css_file ?>">
</head>

<body>
  <!-- Flash message -->
  <div id="info"><?= temp('info') ?></div>
  <div id="error-info"><?= nl2br(error('error_message')) ?></div>

  <header>
    <nav class="navbar">
      <!-- Logo -->
      <div class="logo">
        <a href="/member/home/home.php">
          <img src="../../images/base/logo.jpg" alt="Logo" class="logo-img">
        </a>
      </div>

      <!-- Search Bar (Hide if on productList.php) -->
      <?php if (basename($_SERVER['SCRIPT_NAME']) !== 'productList.php'): ?>
        <div class="search-bar">
          <form action="/member/product/productList.php" method="GET">
            <?= html_search('query', 'placeholder="Search for products..."') ?>
          </form>
        </div>
      <?php endif; ?>

      <!-- Right Section (Our Products, Cart, User Profile or Login) -->
      <div class="right-section">
        <div class="dropdown">
          <button class="dropbtn" data-get="/member/product/productList.php">Products</button>
        </div>

        <!-- Cart Icon -->
        <a href="/member/order/cart.php" class="cart-icon">
          <img src="../../images/base/white_cart_icon.png" alt="Cart">
          <?php
          if (isset($_SESSION['user']) && $currentUser->role == 'Member' && $currentUser->status == 'active') {
          ?>
            <span class="cart-item-number"><?= $numOfCartItems ?></span>
          <?php } ?>
        </a>


        <!-- User Profile or Login -->
        <?php
        if (isset($_SESSION['user']) && $currentUser->role == 'Member' && $currentUser->status == 'active') {
        ?>
          <!-- User is logged in -->
          <div class="user-dropdown">
            <button class="user-btn">
              <img src="/images/member/user/uploads/<?= $currentUser->photo ?>" alt="User Image" class="user-img">
              <div class="username"> <?= $currentUser->username ?></div>
            </button>
            <div class="user-dropdown-content">
              <a href="/member/user/userProfile.php">View Profile</a>
              <a href="/member/order/orderHistoryList.php">Order History</a>
              <a id="logout" href="/authentication/logout.php">Logout</a>
            </div>
          </div>
        <?php } else { ?>
          <!-- User is not logged in -->
          <a href="/authentication/login.php" class="login-btn">
            <img src="/images/base/login.png" alt="Login">
          </a>
        <?php } ?>
      </div>
    </nav>
  </header>

  <!-- Chat System Icon -->
  <div id="chat-icon">
    <a href="https://wa.me/60183999639">
      <img src="/images/base/chat_icon.png" alt="Chat Icon">
    </a>
  </div>