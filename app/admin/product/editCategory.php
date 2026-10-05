<?php

require '../../_base.php';

// Authentication
auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Initialize errors array
$_err = [];

// Handle GET request to load the category information
if (is_get()) {
  // Fetch the category data from the database
  $category_id = req('category_id');

  // Check if the category ID exists
  if (!$category_id || $category_id == 1) {
    redirect('categoryManagement.php');
    exit;
  }

  $stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
  $stm->execute([$category_id]);
  $category = $stm->fetch();

  // Check if category exists
  if (!$category) {
    redirect('categoryManagement.php');
    exit;
  }

  // Assign category fields to variables for form population
  $name = $category->name;
  $description = $category->description;
}

// Handle POST request to update the category information
if (is_post()) {
  // Get input values from the form
  $category_id = req('category_id');
  $name = req('name');
  $description = req('description');

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
    redirect('editCategory.php?categoryId=' . $category_id);
    exit;
  }

  // If no errors, proceed to update the category in the database
  if (empty($_err)) {
    $stm = $_db->prepare('UPDATE category SET name = ?, description = ? WHERE category_id = ?');
    $stm->execute([$name, $description, $category_id]);

    temp('info', 'Category updated successfully');
    redirect('categoryManagement.php');
    exit;
  }
}

// Set title and include the header
$_title = 'Category Details';
$_css_file = '/css/admin/editProduct.css';
include '../../_head.php';

?>

<div class="form-container">
  <button data-get="categoryManagement.php" class="back">Back</button>
  <?= html_h1('category_title', "Category Details") ?>

  <form method="post" class="form2">
    <!-- Show Category ID but make it read-only to the user -->
    <label for="category_id_display">Category ID</label>
    <?php
    // Display the Category ID (read-only to the user)
    $GLOBALS['category_id_display'] = $category_id;
    html_text('category_id_display', 'readonly', $category_id);

    // Hidden field to actually submit the category_id
    $GLOBALS['category_id'] = $category_id;
    html_hidden('category_id');
    ?>

    <!-- Category Name -->
    <label for="name">Category Name</label>
    <?php
    $GLOBALS['name'] = $name;
    html_text('name', 'maxlength="50"');
    ?>

    <!-- Category Description -->
    <label for="description">Category Description</label>
    <?php
    $GLOBALS['description'] = $description;
    html_textarea('description', 'maxlength="200"');
    ?>

    <button type="submit">Save Changes</button>
    <button class="delete_button" data-confirm="Are you sure you want to delete this category?" data-post="deleteCategory.php?category_id=<?= $category_id ?>" id="deleteCategory">
      Delete Category
    </button>
  </form>
</div>

<?php
include '../../_foot.php';
