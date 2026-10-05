<?php

require '../../_base.php';

// Authentication
auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Initialize errors array
$_err = [];

// Handle POST request to add a new category
if (is_post()) {
  // Get input values from the form
  $name = req('name');
  $description = req('description');
  $status = 'active';  // Automatically set the status to "active"

  //store the data into session
  $_SESSION['category_form_data'] = [
    'name' => $name,
    'description' => $description,
  ];

  // Validate: name
  if (empty($name)) {
    $_err['name'] = 'Category name is required';
  } elseif (strlen($name) > 50) {
    $_err['name'] = 'Maximum 50 characters allowed';
  } elseif (strtolower($name) === 'no category') {  // Check if the name is 'No Category'
    $_err['name'] = '"No Category" is not allowed as a category name';
  }

  // Validate: description
  if (empty($description)) {
    $_err['description'] = 'Category description is required';
  } elseif (strlen($description) > 200) {
    $_err['description'] = 'Maximum 200 characters allowed';
  }

  // If there are errors, display them in a dialog and redirect
  if (!empty($_err)) {
    // Prepare error messages
    $errorMessages = implode(
      "\n",
      $_err
    );
    error('error_message', $errorMessages);

    redirect('addCategory.php?');
    exit; // Stop further execution
  }

  // If there are no errors, proceed to insert the new category into the database
  if (empty($_err)) {
    $stm = $_db->prepare('INSERT INTO category (name, description, status) VALUES (?, ?, ?)');
    $stm->execute([$name, $description, $status]);

    //clear the category session
    unset($_SESSION['category_form_data']);

    temp('info', 'Category added successfully');
    redirect('categoryManagement.php');
    exit;
  }
}

// Set title and include the header
$_title = 'Add Category';
$_css_file = '/css/admin/editProduct.css';
include '../../_head.php';

?>

<div class="form-container">
  <button data-get="categoryManagement.php" class="back">Back</button>
  <?= html_h1('category_title', "Add New Category") ?>

  <form method="post" class="form2">
    <label for="name">Category Name</label>
    <?php
    $GLOBALS['name'] = $_SESSION['category_form_data']['name'] ?? '';
    html_text('name', 'maxlength="50"');
    ?>

    <label for="description">Category Description</label>
    <?php
    $GLOBALS['description'] = $_SESSION['category_form_data']['description'] ?? '';
    html_textarea('description', 'maxlength="200"');
    ?>
    <button type="submit">Add Category</button>
  </form>
</div>

<?php
include '../../_foot.php';
