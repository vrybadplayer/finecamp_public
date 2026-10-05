<?php

require '../../_base.php';

// Authentication
auth('authentication/loginAdmin.php', 'Admin', 'Root');

if (is_get()) {
  unset($_SESSION['product_form_data']);
  redirect('addProduct.php');
}
