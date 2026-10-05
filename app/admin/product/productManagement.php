<?php

require '../../_base.php';

// Authentication
auth('authentication/loginAdmin.php', 'Admin', 'Root');

// Define fields available for sorting
if (is_get() || is_post()) {
  $fields = [
    'product_id'    => 'Product ID',
    'name'          => 'Product Name',
    'category_name' => 'Category',
    'min_stock_level' => 'Min Stock Level',
    'low_stock_variations' => 'Low Stock',
    'status'        => 'Status'
  ];

  // Get sorting parameters from the URL
  $sort = req('sort');
  if (!key_exists($sort, $fields)) {
    $sort = 'product_id';  // Default sorting by product_id
  }

  $dir = req('dir');
  if (!in_array($dir, ['asc', 'desc'])) {
    $dir = 'asc';  // Default sorting direction is ascending
  }

  // Initialize search, status, and category filter variables
  $search_query = '';
  $status_filter = 'all'; // Default to all statuses
  $category_filter = 'all'; // Default to all categories
  $selected_status = $status_filter;
  $selected_category = $category_filter;

  // Handle GET and POST requests
  $status_filter = req('status') ?: 'all';
  $category_filter = req('category') ?: 'all';
  $search_query = req('search_input') ?: '';
  $selected_status = $status_filter;
  $selected_category = $category_filter;


  // Build the search query
  // SQL query to fetch product details along with low stock variations
  $query = "
    SELECT p.*, 
           c.name AS category_name,  -- Fetch the category name for each product
           
           -- Concatenate variation names and their stock quantities for low stock variations
           GROUP_CONCAT(
                CONCAT(v.name, ' (Stock: ', v.stock_quantity, ')') 
                SEPARATOR '\n') AS low_stock_variations
           
    FROM product p  -- Select from the product table
    
    -- Join with category table to get the category name associated with each product
    LEFT JOIN category c ON p.category_id = c.category_id
    
    -- Join with product_variation_option table to get variations that are below the minimum stock level
    LEFT JOIN product_variation_option v 
    ON p.product_id = v.product_id 
    AND v.stock_quantity < p.min_stock_level  -- Filter for variations with low stock
    
    -- Filter to include only products that are either active or inactive
    WHERE p.status IN ('active', 'inactive')
";


  // Apply search filtering
  $params = [];
  if (!empty($search_query)) {
    $query .= " AND (LOWER(p.product_id) LIKE LOWER(:search_query)
                    OR LOWER(p.name) LIKE LOWER(:search_query)
                    OR LOWER(p.status) LIKE LOWER(:search_query)
                    OR LOWER(c.name) LIKE LOWER(:search_query))";
    $params[':search_query'] = '%' . $search_query . '%';
  }

  // Apply status filtering
  if ($status_filter !== 'all') {
    $query .= " AND p.status = :status_filter";
    $params[':status_filter'] = $status_filter;
  }

  // Apply category filtering
  if ($category_filter !== 'all') {
    $query .= " AND p.category_id = :category_filter";
    $params[':category_filter'] = $category_filter;
  }

  // Add sorting to the query
  $query .= " GROUP BY p.product_id ORDER BY $sort $dir";

  // Execute the query
  $stmt = $_db->prepare($query);
  $stmt->execute($params);
  $products = $stmt->fetchAll();

  // Define status options for the filtering dropdown
  $status_option = [
    'all' => 'All',
    'active' => 'Active',
    'inactive' => 'Inactive'
  ];

  // Fetch categories for category filter dropdown
  $categories = $_db->query("SELECT category_id, name FROM category")->fetchAll(PDO::FETCH_KEY_PAIR);
  $category_option = ['all' => 'All'] + $categories;
}
// Set title and include the header
$_title = 'Product Management';
$_css_file = '/css/admin/product.css';
include '../../_head.php';
?>


<button data-get="/admin/adminDashboard.php" class="back">Back to Dashboard</button>
<?= html_h1('product_title', "Product Management") ?>


<div class="form-container">
  <form action="" method="GET">

    <!-- Search input field -->
    <label for="search_input">Search Product: </label>
    <?= html_search('search_input') ?>

    <!-- Filter by Status -->
    <label for="status">Filter by Status: </label>
    <?php
    $GLOBALS['status'] = $selected_status; // Make selected status available globally
    html_select('status', $status_option, '- Select Status -', 'class="filter-dropdown"');
    ?>

    <!-- Filter by Category -->
    <label for="category">Filter by Category: </label>
    <?php
    $GLOBALS['category'] = $selected_category; // Make selected category available globally
    html_select('category', $category_option, '- Select Category -', 'class="filter-dropdown"');
    ?>

    <!-- Submit button -->
    <input type="submit" name="submit" value="Search">

    <!-- Reset button -->
    <button data-get="productManagement.php">Reset</button>
  </form>
</div>

<!-- Flex container for button and result count -->
<div class="action-bar">
  <?= html_button('create', 'Add Product', 'data-get="addProduct.php"'); ?>
  <span class="count-found"><?= count($products) ?> product(s) found</span>
</div>

<table class="custom-table borderless-table">
  <thead>
    <tr>
      <?php
      //Construct the href parameters from search input, status and category
      $search_query = isset($_GET['search_input']) ? 'search_input=' . urlencode($_GET['search_input']) : '';
      $status_filter = isset($_GET['status']) ? 'status=' . urlencode($_GET['status']) : '';
      $category_filter = isset($_GET['category']) ? 'category=' . urlencode($_GET['category']) : '';

      // Combine the filters into a single href string
      $href = http_build_query(array_filter([
        'search_input' => $_GET['search_input'] ?? null,
        'status' => $_GET['status'] ?? null,
        'category' => $_GET['category'] ?? null
      ]));

      // table header
      table_headers($fields, $sort, $dir, $href);
      ?>
      <th class="center">Image</th>
      <th class="center">Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($products) > 0) : ?>
      <?php foreach ($products as $product) : ?>
        <tr class="product_details_row">

          <td><?= $product->product_id ?></td>
          <td><?= $product->name ?></td>
          <td><?= $product->category_name ?></td>
          <td><?= $product->min_stock_level ?></td>
          <td>
            <?php if (!empty($product->low_stock_variations)) : ?>
              <span style="color:red"><?= nl2br($product->low_stock_variations) ?></span>
            <?php else : ?>
              <span style="color: green">No Low Stock</span>
            <?php endif ?>
          </td>

          <td>
            <?php if ($product->status == 'active') : ?>
              <span style="color:green"><?= $product->status ?></span>
            <?php else : ?>
              <span style="color:red"><?= $product->status ?></span>
            <?php endif ?>
          </td>

          <td class="center">
            <?php if (!empty($product->product_image)): ?>
              <img src="../../images/admin/product/product_image/<?= $product->product_image ?>" alt="Product Image">
            <?php else: ?>
              <img src="../../images/admin/product/product_image/default.jpg" alt="Default Product Image">
            <?php endif; ?>
          </td>

          <td class="center">
            <button class="edit_button" data-get="editProduct.php?productId=<?= $product->product_id ?>" id="editProduct">
              Edit
            </button>
            <button class="delete_button" data-confirm="Are you sure you want to delete this product?" data-post="deleteProduct.php?id=<?= $product->product_id ?>" id="deleteProduct">
              Delete
            </button>
          </td>
        </tr>
      <?php endforeach ?>
    <?php else : ?>
      <tr>
        <td colspan="8" style="text-align: center;">No records found.</td>
      </tr>
    <?php endif ?>
  </tbody>
</table>


<?php
include '../../_foot.php';
?>