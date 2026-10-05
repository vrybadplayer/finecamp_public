<?php
require '../../_base.php';

auth('authentication/loginAdmin.php', 'Admin', 'Root');

if (is_post()) {

  // Get the product ID from the request
  $id = req('id');

  // Update the product status to 'terminated' rather than deleting it
  $stm = $_db->prepare('UPDATE product SET status = ? WHERE product_id = ?');
  $stm->execute(['terminated', $id]);

  // Delete cart items that are associated with the product
  $stm_cart = $_db->prepare('DELETE FROM cart_item WHERE product_id = ?');
  $stm_cart->execute([$id]);

  // Provide feedback to the user that the product status was updated
  temp('info', 'Product ID ' . $id . ' successfully deleted');

  // Redirect back to the index page after the operation
  redirect('productManagement.php');
}
