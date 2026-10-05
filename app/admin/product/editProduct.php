<?php
require '../../_base.php';

auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Initialize errors array
$_err = [];
global $origin_product_image;

// Define a default product image
$default_product_image = 'default.jpg';
$default_variation_image = 'default.jpg';
// Define status and category options
$status_option = ['active' => 'active', 'inactive' => 'inactive'];
$category_option = $_db->query("SELECT category_id, name FROM category")->fetchAll(PDO::FETCH_KEY_PAIR);

// Load product and variations 
if (is_get()) {
  $id = req('productId');

  // Check if product exists
  if (!is_exists($id, 'product', 'product_id')) {
    redirect('productManagement.php');
  }

  // Fetch product information, including product_image
  $stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
  $stm->execute([$id]);
  $p = $stm->fetch();

  if (!$p || ($p->status !== 'active' && $p->status !== 'inactive')) {
    redirect('productManagement.php');
    exit;
  }

  // Ensure $variations is always an array
  $variations = [];

  // Fetch product variations
  $stm = $_db->prepare('SELECT * FROM product_variation_option WHERE product_id = ?');
  $stm->execute([$id]);
  $variations = $stm->fetchAll();

  // Store the current image
  $origin_product_image = $p->product_image ?: $default_product_image;  // Use default image if none exists.
  extract((array)$p);  // Extract product fields
}

if (is_post()) {
  $variations = $_POST['variations'] ?? [];
  $new_variations = $_POST['new_variations'] ?? [];
  $product_id = req('product_id');
  $name = req('name');
  $description = req('description');
  $min_stock_level = req('min_stock_level');
  $status = req('status');
  $category_id = req('category_id');
  $variation_name = req('variation_name');
  $product_image_file = get_file('product_image'); // Get the uploaded file for product image
  $deleted_variations = isset($_POST['deleted_variations']) ? explode(',', $_POST['deleted_variations']) : [];
  $_err = []; // Initialize error array

  // define max value
  $max_value = 99999999;

  // Arrays to store variation images and new variation images
  $variation_images = [];
  $new_variation_images = [];

  // Validate: name
  if ($name == '') {
    $_err['name'] = 'Name is required';
  } else if (strlen($name) > 100) {
    $_err['name'] = 'Maximum 100 characters allowed';
  }

  // Validate: description
  if ($description == '') {
    $_err['description'] = 'Description is required';
  } else if (strlen($description) > 255) {
    $_err['description'] = 'Maximum 255 characters allowed';
  }

  // Validate: min stock level
  if ($min_stock_level == '') {
    $_err['min_stock_level'] = 'Minimum stock level is required';
  } else if (!is_numeric($min_stock_level) || intval($min_stock_level) <= 0) {
    $_err['min_stock_level'] = 'Minimum stock level must be a positive integer greater than 0';
  } else if (intval($min_stock_level) > $max_value) {
    $_err['min_stock_level'] = "Minimum stock level cannot exceed $max_value";
  }

  // Validate: status (must be 'active' or 'inactive')
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

  // Validate: variation_name
  if ($variation_name == '') {
    $_err['variation_name'] = 'Variation name is required';
  } else if (strlen($variation_name) > 30) {
    $_err['variation_name'] = 'Maximum 30 characters allowed';
  }

  // Validate: product image (file)
  if ($product_image_file) {
    if (!str_starts_with($product_image_file->type, 'image/')) {
      $_err['product_image'] = 'Must be an image';
    } else if ($product_image_file->size > 5 * 1024 * 1024) { // Max size 5MB
      $_err['product_image'] = 'Maximum 5MB';
    }
  }

  // Validate: At least one variation (either existing or new)
  if (empty($variations) && empty($new_variations)) {
    $_err['variations'] = 'At least one variation is required.';
  }

  // Validate: Existing Variations and store variation images
  foreach ($variations as $variation_id => $variation_data) {
    $variation_image_file = get_file('variation_images', $variation_id); // Get the variation image file

    // Validate variation name
    if (empty($variation_data['name'])) {
      $_err["variation_name_{$variation_id}"] = 'Variation name is required.';
    } else if (strlen($variation_data['name']) > 20) {
      $_err["variation_name_{$variation_id}"] = 'Variation name must not exceed 20 characters.';
    }

    // Validate unit price
    if (empty($variation_data['unit_price']) || $variation_data['unit_price'] <= 0 || !is_money($variation_data['unit_price'])) {
      $_err["variation_unit_price_{$variation_id}"] = 'Unit price must be a valid amount greater than 0 with up to two decimal places.';
    } else if ($variation_data['unit_price'] > $max_value) {
      $_err["variation_unit_price_{$variation_id}"] = "Unit price cannot exceed RM $max_value.";
    }

    // Validate stock quantity
    if (!is_numeric($variation_data['stock_quantity']) || $variation_data['stock_quantity'] < 0) {
      $_err["variation_stock_quantity_{$variation_id}"] = 'Stock quantity must be a non-negative integer.';
    } else if ($variation_data['stock_quantity'] > $max_value) {
      $_err["variation_stock_quantity_{$variation_id}"] = "Stock quantity cannot exceed $max_value.";
    }

    // Validate weight
    if (empty($variation_data['weight']) || $variation_data['weight'] <= 0 || !is_money($variation_data['weight'])) {
      $_err["variation_weight_{$variation_id}"] = 'Weight must be greater than 0 with up to two decimal places.';
    } else if ($variation_data['weight'] > $max_value) {
      $_err["variation_weight_{$variation_id}"] = "Weight cannot exceed $max_value kg.";
    }

    // Validate and store variation image if uploaded
    if (!empty($variation_image_file)) {
      if (!str_starts_with($variation_image_file->type, 'image/')) {
        $_err["variation_image_{$variation_id}"] = 'Variation image must be an image.';
      } else if ($variation_image_file->size > 5 * 1024 * 1024) {
        $_err["variation_image_{$variation_id}"] = 'Variation image must be less than 5MB.';
      } else {
        $variation_images[$variation_id] = $variation_image_file;
      }
    }
  }

  // Validate: New Variations and store new variation images
  foreach ($new_variations as $index => $variation_data) {
    $new_variation_image_file = get_file('new_variation_images', $index); // Get the new variation image file

    // Validate new variation name
    if (empty($variation_data['name'])) {
      $_err["new_variation_name_{$index}"] = 'New variation name is required.';
    } else if (strlen($variation_data['name']) > 30) {
      $_err["new_variation_name_{$index}"] = 'New variation name must not exceed 30 characters.';
    }

    // Validate new unit price
    if (empty($variation_data['unit_price']) || $variation_data['unit_price'] <= 0 || !is_money($variation_data['unit_price'])) {
      $_err["new_variation_unit_price_{$index}"] = 'Unit price must be a valid amount greater than 0 with up to two decimal places.';
    } else if ($variation_data['unit_price'] > $max_value) {
      $_err["new_variation_unit_price_{$index}"] = "Unit price cannot exceed RM $max_value.";
    }

    // Validate new stock quantity
    if (!is_numeric($variation_data['stock_quantity']) || $variation_data['stock_quantity'] < 0) {
      $_err["new_variation_stock_quantity_{$index}"] = 'Stock quantity must be a non-negative integer.';
    } else if ($variation_data['stock_quantity'] > $max_value) {
      $_err["new_variation_stock_quantity_{$index}"] = "Stock quantity cannot exceed $max_value.";
    }

    // Validate new weight
    if (empty($variation_data['weight']) || $variation_data['weight'] <= 0 || !is_money($variation_data['weight'])) {
      $_err["new_variation_weight_{$index}"] = 'Weight must be greater than 0 with up to two decimal places.';
    } else if ($variation_data['weight'] > $max_value) {
      $_err["new_variation_weight_{$index}"] = "Weight cannot exceed $max_value kg.";
    }

    // Validate and store new variation image if uploaded
    if (empty($new_variation_image_file)) {
      $_err["new_variation_image_{$index}"] = 'New variation image is required.';
    } else {
      if (!str_starts_with($new_variation_image_file->type, 'image/')) {
        $_err["new_variation_image_{$index}"] = 'New variation image must be an image.';
      } else if ($new_variation_image_file->size > 5 * 1024 * 1024) {
        $_err["new_variation_image_{$index}"] = 'New variation image must be less than 5MB.';
      } else {
        $new_variation_images[$index] = $new_variation_image_file;
      }
    }
  }

  // If there are errors, display them in a dialog and redirect
  if (!empty($_err)) {
    $errorMessages = implode("\n", $_err);
    error('error_message', $errorMessages);
    redirect('editProduct.php?productId=' . $product_id);
    exit; // Stop further execution
  }

  // If no errors, update the product and variations
  if (empty($_err)) {
    // Handle the product image upload
    if ($product_image_file) {
      // Fetch the existing product image from the database
      $stm = $_db->prepare('SELECT product_image FROM product WHERE product_id = ?');
      $stm->execute([$product_id]);
      $origin_product_image = $stm->fetchColumn();

      // Delete the old image if it exists and is not the default image
      if ($origin_product_image && $origin_product_image != $default_product_image) {
        unlink("../../images/admin/product/product_image/$origin_product_image"); // Remove old image
      }

      $product_image = save_photo($product_image_file, '../../images/admin/product/product_image', 1000, 1000);

      temp('info', 'Product image uploaded');
    }

    // Update product details in the database
    if ($product_image_file && $product_image_file != $origin_product_image) {
      $stm = $_db->prepare('UPDATE product SET name = ?, description = ?, min_stock_level = ?, status = ?, category_id = ?, product_image = ?, variation_name = ? WHERE product_id = ?');
      $stm->execute([$name, $description, $min_stock_level, $status, $category_id, $product_image, $variation_name, $product_id]);
    } else {
      $stm = $_db->prepare('UPDATE product SET name = ?, description = ?, min_stock_level = ?, status = ?, category_id = ?, variation_name = ? WHERE product_id = ?');
      $stm->execute([$name, $description, $min_stock_level, $status, $category_id, $variation_name, $product_id]);
    }

    // Process existing variations and their images
    foreach ($variations as $variation_id => $variation_data) {
      // Update variation data
      $stm = $_db->prepare('UPDATE product_variation_option SET name = ?, unit_price = ?, stock_quantity = ?, weight = ? WHERE product_variation_option_id = ? AND product_id = ?');
      $stm->execute([$variation_data['name'], $variation_data['unit_price'], $variation_data['stock_quantity'], $variation_data['weight'], $variation_id, $product_id]);

      // Handle existing variation image
      if (isset($variation_images[$variation_id])) {
        // Delete the old variation image if a new one is being uploaded
        $stm = $_db->prepare('SELECT image FROM product_variation_option WHERE product_variation_option_id = ? AND product_id = ?');
        $stm->execute([$variation_id, $product_id]);
        $old_image = $stm->fetchColumn();

        if ($old_image && $old_image != $default_variation_image) {
          unlink("../../images/admin/product/product_variations_image/$old_image"); // Remove the old image
        }

        //save new image
        $variation_image_name = save_photo($variation_images[$variation_id], '../../images/admin/product/product_variations_image', 1000, 1000);
        $stm = $_db->prepare('UPDATE product_variation_option SET image = ? WHERE product_variation_option_id = ? AND product_id = ?');
        $stm->execute([$variation_image_name, $variation_id, $product_id]);
      }
    }

    // Handle new variations and their images
    foreach ($new_variations as $index => $variation_data) {
      $stm = $_db->prepare('INSERT INTO product_variation_option (name, unit_price, stock_quantity, weight, product_id) VALUES (?, ?, ?, ?, ?)');
      $stm->execute([$variation_data['name'], $variation_data['unit_price'], $variation_data['stock_quantity'], $variation_data['weight'], $product_id]);

      $new_variation_id = $_db->lastInsertId();

      // Handle new variation image
      if (isset($new_variation_images[$index])) {
        $variation_image_name = save_photo($new_variation_images[$index], '../../images/admin/product/product_variations_image', 1000, 1000);
        $stm = $_db->prepare('UPDATE product_variation_option SET image = ? WHERE product_variation_option_id = ? AND product_id = ?');
        $stm->execute([$variation_image_name, $new_variation_id, $product_id]);
      }
    }

    // Handle deleted variations
    foreach ($deleted_variations as $variation_id) {
      if (!empty($variation_id)) {
        // delete the corresponding cart items with the same product_variation_option_id
        $stm_cart = $_db->prepare('DELETE FROM cart_item WHERE product_variation_option_id = ? AND product_id = ?');
        $stm_cart->execute([$variation_id, $product_id]);

        // delete the product variation options
        $stm = $_db->prepare('DELETE FROM product_variation_option WHERE product_variation_option_id = ? AND product_id = ?');
        $stm->execute([$variation_id, $product_id]);
      }
    }


    // Redirect after successful update
    temp('info', 'Product and variations updated successfully');
    redirect('editProduct.php?productId=' . $product_id);
  }
}

// ----------------------------------------------------------------------------
$_title = 'Product Details';
$_css_file = '/css/admin/editProduct.css';
include '../../_head.php';
?>

<div class="form-container">
  <button data-get="productManagement.php" class="back">Back</button>
  <?= html_h1('h1', "Product Details") ?>

  <form method="post" class="form2" enctype="multipart/form-data">
    <!-- Display Product ID (readonly) -->
    <label for="product_id_display">Product ID</label>
    <?php
    // display product id
    $GLOBALS['product_id_display'] = $product_id;
    html_text('product_id_display', 'readonly', $product_id);

    // hidden product id
    html_hidden('product_id');
    ?>


    <label for="name">Product Name</label>
    <?= html_text('name', 'maxlength="100"') ?>

    <label for="description">Description</label>
    <?= html_textarea('description', 'maxlength="255"') ?>

    <label for="min_stock_level">Minimum Stock Level</label>
    <?= html_number('min_stock_level', '0', '', '1') ?>

    <label for="status">Status</label>
    <?php
    $GLOBALS['status'] = $status ?? 'active';  // Set default status if not provided
    html_select('status', $status_option, '- Select Status -');
    ?>

    <label for="category_id">Category</label>
    <?php
    $GLOBALS['category_id'] = $category_id ?? '1';  // Set default category if not provided
    html_select('category_id', $category_option, '- Select Category -');
    ?>

    <label for="product_image">Product Image</label>
    <label class="upload" tabindex="0">
      <?= html_file('product_image', 'image/*', 'hidden') ?>
      <?php if (!empty($origin_product_image)): ?>
        <img src="../../images/admin/product/product_image/<?= $origin_product_image ?>" alt="Product Image">
      <?php else: ?>
        <img src="../../images/admin/product/product_image/<?= $default_product_image ?>" alt="Default Product Image">
      <?php endif; ?>
    </label>

    <!-- Variation Section -->
    <div id="variations_section">
      <h2>Product Variations</h2>
      <label for="variation_name">Variation Name</label>
      <input type="text" name="variation_name" value="<?= $variation_name ?>" maxlength="30">

      <table id="variations_table">
        <thead>
          <tr>
            <th><?= $variation_name ?></th>
            <th>Unit Price(RM)</th>
            <th>Stock Quantity</th>
            <th>Weight(kg)</th>
            <th>Image</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($variations) || !empty($_POST['new_variations'])): ?>
            <?php foreach ($variations as $variation): ?>
              <tr>
                <td><input type="text" maxlength="20" name="variations[<?= $variation->product_variation_option_id ?>][name]" value="<?= $variation->name ?>"></td>
                <td><input type="number" min="0.01" step="0.01" name="variations[<?= $variation->product_variation_option_id ?>][unit_price]" value="<?= $variation->unit_price ?>"></td>
                <?php if ($variation->stock_quantity < $min_stock_level): ?>
                  <td><input style="color:red" type="number" min="0" name="variations[<?= $variation->product_variation_option_id ?>][stock_quantity]" value="<?= $variation->stock_quantity ?>"></td>
                <?php else: ?>
                  <td><input type="number" min="0" name="variations[<?= $variation->product_variation_option_id ?>][stock_quantity]" value="<?= $variation->stock_quantity ?>"></td>
                <?php endif; ?>
                <td><input type="number" min="0.01" step="0.01" name="variations[<?= $variation->product_variation_option_id ?>][weight]" value="<?= $variation->weight ?>"></td>
                <td>
                  <div class="variation_image">
                    <label class="upload" tabindex="0">
                      <?= html_file('variation_images[' . $variation->product_variation_option_id . ']', 'image/*', 'hidden') ?>
                      <?php if (!empty($variation->image)): ?>
                        <img src="../../images/admin/product/product_variations_image/<?= $variation->image ?>" alt="Variation Image" style="max-width: 200px;">
                      <?php else: ?>
                        <img src="../../images/admin/product/product_variations_image/default.jpg" alt="Default Image" style="max-width: 200px;">
                      <?php endif; ?>
                    </label>
                  </div>
                </td>
                <td>
                  <button type="button" class="delete_variation" data-id="<?= $variation->product_variation_option_id ?>">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php elseif (empty($_POST['new_variations'])): ?>
            <tr>
              <td colspan="6">No variations available. Add a new variation below.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <button type="button" id="add_variation">Add Variation</button>
      <?= html_hidden('deleted_variations') ?>
    </div>

    <!-- Submit and Reset Buttons -->
    <section>
      <button type="submit">Save</button>
      <button type="reset">Reset</button>
      <button class="delete_variation" data-confirm="Are you sure you want to delete this product?" data-post="deleteProduct.php?id=<?= $product_id ?>" id="deleteProduct">
        Delete Product
      </button>
    </section>
  </form>
</div>

<script src="/js/admin/product.js"></script>
<?php
include '../../_foot.php';
