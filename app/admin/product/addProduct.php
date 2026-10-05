<?php
require '../../_base.php';

auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Initialize errors array
$_err = [];
$default_product_image = 'default.jpg';  // Ensure this image exists in your product image directory.
$variations = [];
// Define status and category options
$status_option = ['active' => 'active', 'inactive' => 'inactive'];
$category_option = $_db->query("SELECT category_id, name FROM category")->fetchAll(PDO::FETCH_KEY_PAIR);


if (is_post()) {
  // Save form data to session before validation
  $_SESSION['product_form_data'] = [
    'name' => req('name'),
    'description' => req('description'),
    'min_stock_level' => req('min_stock_level'),
    'status' => req('status'),
    'category_id' => req('category_id'),
    'variation_name' => req('variation_name'),
  ];

  $name = $_SESSION['product_form_data']['name'];
  $description = $_SESSION['product_form_data']['description'];
  $min_stock_level = $_SESSION['product_form_data']['min_stock_level'];
  $status = $_SESSION['product_form_data']['status'];
  $category_id = $_SESSION['product_form_data']['category_id'];
  $variation_name = $_SESSION['product_form_data']['variation_name'];

  $product_image_file = get_file('product_image'); // Get the uploaded file
  $new_variations = $_POST['new_variations'] ?? []; //get new variation

  $_err = [];
  $temp_variation_images = [];
  // define max value
  $max_value = 99999999;

  // Validate product name
  if ($name == '') {
    $_err['name'] = 'Name is required';
  } else if (strlen($name) > 100) {
    $_err['name'] = 'Maximum 100 characters allowed';
  }

  // Validate product description
  if ($description == '') {
    $_err['description'] = 'Description is required';
  } else if (strlen($description) > 255) {
    $_err['description'] = 'Maximum 255 characters allowed';
  }

  // Validate minimum stock level
  if ($min_stock_level == '') {
    $_err['min_stock_level'] = 'Minimum stock level is required';
  } else if (!is_numeric($min_stock_level) || intval($min_stock_level) <= 0) {
    $_err['min_stock_level'] = 'Minimum stock level must be a positive integer greater than 0';
  } else if (intval($min_stock_level) > $max_value) {
    $_err['min_stock_level'] = "Minimum stock level cannot exceed $max_value";
  }

  // Validate status
  if (empty($status)) {
    $_err['status'] = 'Status is required.';
  } else if (!in_array($status, ['active', 'inactive'])) {
    $_err['status'] = 'Status must be either "active" or "inactive".';
  }

  // Validate category
  if (empty($category_id)) {
    $_err['category_id'] = 'Category is required.';
  } else if (!array_key_exists($category_id, $category_option)) {
    $_err['category_id'] = 'Invalid category selected.';
  }

  // Validate variation name
  if ($variation_name == '') {
    $_err['variation_name'] = 'Variation name is required';
  } else if (strlen($variation_name) > 30) {
    $_err['variation_name'] = 'Maximum 30 characters allowed';
  }

  // Validate product image
  if (empty($product_image_file)) {
    $_err['product_image'] = 'Product image is required';
  } else {
    if (!str_starts_with($product_image_file->type, 'image/')) {
      $_err['product_image'] = 'Must be an image';
    } else if ($product_image_file->size > 5 * 1024 * 1024) { // Max size 5MB
      $_err['product_image'] = 'Maximum 5MB';
    }
  }

  // Validate at least one variation is provided
  if (empty($new_variations)) {
    $_err['new_variations'] = 'At least one variation is required for the product.';
  }

  // Validate new variations and their images
  foreach ($new_variations as $index => $variation_data) {
    // Validate new variation name
    if (empty($variation_data['name'])) {
      $_err["new_variation_name_{$index}"] = 'New variation option name is required.';
    } else if (strlen($variation_data['name']) > 30) {
      $_err["new_variation_name_{$index}"] = 'New variation option name must not exceed 30 characters.';
    }

    // Validate new unit price
    if (empty($variation_data['unit_price']) || $variation_data['unit_price'] <= 0 || !is_money($variation_data['unit_price'])) {
      $_err["new_variation_unit_price_{$index}"] = 'New unit price must be a valid amount greater than 0 with up to two decimal places.';
    } else if ($variation_data['unit_price'] > $max_value) {
      $_err["new_variation_unit_price_{$index}"] = "New unit price cannot exceed RM $max_value.";
    }

    // Validate new stock quantity
    if (!is_numeric($variation_data['stock_quantity']) || $variation_data['stock_quantity'] < 0) {
      $_err["new_variation_stock_quantity_{$index}"] = 'New stock quantity must be a non-negative integer.';
    } else if ($variation_data['stock_quantity'] > $max_value) {
      $_err["new_variation_stock_quantity_{$index}"] = "New stock quantity cannot exceed $max_value.";
    }

    // Validate new weight
    if (empty($variation_data['weight']) || $variation_data['weight'] <= 0 || !is_money($variation_data['weight'])) {
      $_err["new_variation_weight_{$index}"] = 'New weight must be greater than 0 with up to two decimal places.';
    } else if ($variation_data['weight'] > $max_value) {
      $_err["new_variation_weight_{$index}"] = "New weight cannot exceed $max_value kg.";
    }

    // Validate variation image
    $variation_image_file = get_file('new_variation_images', $index); // Get the variation image
    if (empty($variation_image_file)) {
      $_err["new_variation_image_{$index}"] = "Variation Image is required.";
    } else if (!str_starts_with($variation_image_file->type, 'image/')) {
      $_err["new_variation_image_{$index}"] = "Variation Image must be an image.";
    } else if ($variation_image_file->size > 5 * 1024 * 1024) {
      $_err["new_variation_image_{$index}"] = "The image for variation must be less than 5MB.";
    } else {
      // Temporarily save image data in an array
      $temp_variation_images[$index] = $variation_image_file;
    }
  }

  // If there are errors, display them and stop execution
  if (!empty($_err)) {
    $errorMessages = implode("\n", $_err);
    error('error_message', $errorMessages);
    redirect('addProduct.php');
    exit; // Stop further execution
  }

  // If no errors, insert the product and variations
  if (empty($_err)) {
    //product upload image
    $product_image = save_photo($product_image_file, '../../images/admin/product/product_image', 1000, 1000);

    // Insert the product details into the database
    $stm = $_db->prepare('INSERT INTO product (name, description, min_stock_level, status, category_id, product_image, variation_name) VALUES (?,?,?,?,?,?,?)');
    $stm->execute([$name, $description, $min_stock_level, $status, $category_id, $product_image, $variation_name]);

    // Get the last inserted product ID
    $product_id = $_db->lastInsertId();

    // Insert each variation into the database and handle the image upload
    foreach ($new_variations as $index => $variation_data) {
      $stm = $_db->prepare('INSERT INTO product_variation_option (name, unit_price, stock_quantity, weight, product_id) VALUES (?,?,?,?,?)');
      $stm->execute([
        $variation_data['name'],
        $variation_data['unit_price'],
        $variation_data['stock_quantity'],
        $variation_data['weight'],
        $product_id
      ]);

      // Get the last inserted variation ID
      $new_variation_id = $_db->lastInsertId();

      // Save the variation image
      if (isset($temp_variation_images[$index])) {

        //upload variation photo
        $variation_file = $temp_variation_images[$index];
        $variation_image_name = save_photo($variation_file, '../../images/admin/product/product_variations_image', 1000, 1000);

        // Update variation with the image
        $stm = $_db->prepare('UPDATE product_variation_option SET image = ? WHERE product_variation_option_id = ?');
        $stm->execute([$variation_image_name, $new_variation_id]);
      }
    }

    // Clear session after successful insertion
    unset($_SESSION['product_form_data']);

    // Redirect after successful insertion
    temp('info', 'Product and variations added successfully');
    redirect('productManagement.php');
  }
}

// If session exists, retrieve form data to prefill the form
$product_form_data = $_SESSION['product_form_data'] ?? [];

// Pre-fill form fields from session data
$name = $product_form_data['name'] ?? '';
$description = $product_form_data['description'] ?? '';
$min_stock_level = $product_form_data['min_stock_level'] ?? '';
$status = $product_form_data['status'] ?? 'active';
$category_id = $product_form_data['category_id'] ?? '1';
$variation_name = $product_form_data['variation_name'] ?? '';

// ----------------------------------------------------------------------------
$_title = 'Add Product';
$_css_file = '/css/admin/editProduct.css';
include '../../_head.php';
?>

<div class="form-container">
  <button data-get="productManagement.php" class="back">Back</button>
  <?= html_h1('h1', "Add Product") ?>
  <form method="post" class="form2" enctype="multipart/form-data">

    <label for="name">Product Name</label>
    <?php
    $GLOBALS['name'] = $name ?? '';
    html_text('name', 'maxlength="100"')
    ?>

    <label for="description">Description</label>
    <?php
    $GLOBALS['description'] = $description ?? '';
    html_textarea('description', 'maxlength="255"');
    ?>


    <label for="min_stock_level">Minimum Stock Level</label>
    <?php
    $GLOBALS['min_stock_level'] = $min_stock_level ?? '';
    html_number('min_stock_level', '0', '', '1')
    ?>

    <label for="status">Status</label>
    <?php
    $GLOBALS['status'] = $status;
    html_select('status', $status_option, '- Select Status -');
    ?>

    <label for="category_id">Category</label>
    <?php
    $GLOBALS['category_id'] = $category_id;
    html_select('category_id', $category_option, '- Select Category -');
    ?>

    <label for="product_image">Product Image</label>
    <label class="upload" tabindex="0">
      <?= html_file('product_image', 'image/*', 'hidden') ?>
      <img src="../../images/admin/product/product_image/default.jpg" alt="Default Product Image">
    </label>

    <!-- Variation Section -->
    <div id="variations_section">
      <h2>Product Variations</h2>
      <label for="variation_name">Variation Name</label>
      <?php
      $GLOBALS['variation_name'] = $variation_name ?? '';
      html_text('variation_name', 'maxlength="30"');
      ?>

      <table id="variations_table">
        <thead>
          <tr>
            <th>Variation Option</th>
            <th>Unit Price(RM)</th>
            <th>Stock Quantity</th>
            <th>Weight(kg)</th>
            <th>Image</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="6">No variations available. Add a new variation below.</td>
          </tr>
        </tbody>
      </table>

      <button type="button" id="add_variation">Add Variation</button>
    </div>

    <!-- Submit and Reset Buttons -->
    <section>
      <button type="submit">Save</button>
      <button data-get="clearAddProductSession.php">Reset</button>
    </section>
  </form>
</div>

<script src="/js/admin/product.js"></script>
<?php
include '../../_foot.php';
