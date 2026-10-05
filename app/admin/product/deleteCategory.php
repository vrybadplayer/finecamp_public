<?php

require '../../_base.php';

auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Fetch the category ID from the URL

if (is_post()) {
  $category_id = req('category_id');

  // Ensure category ID is provided and not 1
  if (empty($category_id) || $category_id == 1) {
    redirect('categoryManagement.php'); // Redirect if category ID is not valid or if it's "No Category"
    exit;
  }

  // Check if "No Category" exists
  $stm = $_db->prepare('SELECT category_id FROM category WHERE LOWER(name) = ?');
  $stm->execute(['no category']);
  $no_category = $stm->fetch();

  if (!$no_category) {
    // If "No Category" doesn't exist, redirect and show an error
    temp('info', 'No Category not found. Please create a "No Category" entry first.');
    redirect('categoryManagement.php');
    exit;
  }

  // Update all products with the category to be deleted, changing them to "No Category"
  $stm = $_db->prepare('UPDATE product SET category_id = ? WHERE category_id = ?');
  $stm->execute([$no_category->category_id, $category_id]);

  // Delete the category from the database
  $stm = $_db->prepare('DELETE FROM category WHERE category_id = ?');
  $stm->execute([$category_id]);

  // Set a success message and redirect
  temp('info', 'Category deleted successfully, and products in the category have been updated to "No Category".');
  redirect('categoryManagement.php');
  exit;
}
