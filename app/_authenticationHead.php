<!DOCTYPE html>
<html lang="en">

<?php
// Initialize cart item count to 0 as the default value
$numOfCartItems = 0;

// Check if the user is logged in 
if (isset($_user) && $_user !== null) {
  $stm = $_db->prepare('
        SELECT COUNT(*) FROM cart_item
        WHERE user_id = ?
    ');
  $stm->execute([$_user->user_id]);

  $numOfCartItems = $stm->fetchColumn();
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
  <div id="error-info"><?= nl2br(htmlspecialchars(error('error_message'))) ?></div>

  <header>
    <nav class="navbar">
      <!-- Logo -->
      <div class="logo">
        <a href="/member/home/home.php">
          <img src="../../images/base/logo.jpg" alt="Logo" class="logo-img">
        </a>
      </div>
    </nav>
  </header>