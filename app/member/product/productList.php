<?php
require '../../_base.php';

$category_name = 'Our Products';
$category_description = 'Browse through our collection of camping equipment and find the best products for your adventure.';

if (is_get() || is_post()) {
  // Get the search query, category filter, min price, max price, and sort order from GET parameters
  $_err = [];

  $search_query = req('query', '');
  $category_filter = req('category', 'all');
  $min_price = req('min_price', '');
  $max_price = req('max_price', '');
  $sort_order = req('sort_order', 'none');

  // Validate: min price cannot be greater than max price
  if (!empty($min_price) && !empty($max_price) && $min_price > $max_price) {
    $_err['price_range'] = 'Minimum price cannot be greater than maximum price.';
  }

  if (!empty($_err)) {
    $errorMessages = implode("\n", $_err);
    error('error_message', $errorMessages);
    redirect('productList.php');
    exit;
  }

  // Fetch categories for the category filter dropdown
  $categories_stmt = $_db->query("SELECT category_id AS categoryid, name, description FROM category");
  $categories = $categories_stmt->fetchAll();

  // If a specific category is selected (not 'all'), fetch the category details
  if ($category_filter != 'all') {

    // Prepare an SQL query to fetch the category name and description based on category_id
    $category_stmt = $_db->prepare("SELECT name, description FROM category WHERE category_id = :category_id");

    // Execute the query, passing the category ID from $category_filter
    $category_stmt->execute([':category_id' => $category_filter]);

    // Fetch the category result (name and description)
    $category = $category_stmt->fetch();

    // If the category exists, update the page title and description with the fetched values
    if ($category) {
      $category_name = $category->name;
      $category_description = $category->description;
    }
  }


  // Build SQL query to fetch products and their first variation price
  $sql = "SELECT p.*, c.name AS category_name, MIN(pvo.unit_price) AS first_variation_price
        FROM product p
        INNER JOIN category c ON p.category_id = c.category_id
        LEFT JOIN product_variation_option pvo ON p.product_id = pvo.product_id
        WHERE p.status = 'active'";

  $params = [];

  // Add search filter, check product name, desciption and category
  if (!empty($search_query)) {
    $sql .= " AND (LOWER(p.name) LIKE LOWER(:search_query) 
                   OR LOWER(c.name) LIKE LOWER(:search_query)
                   OR LOWER(p.description) LIKE LOWER(:search_query))";
    $params[':search_query'] = '%' . $search_query . '%';
  }


  // Add category filter
  if ($category_filter != 'all') {
    $sql .= " AND p.category_id = :category_id";
    $params[':category_id'] = $category_filter;
  }

  // Add price range filters
  if (!empty($min_price)) {
    $sql .= " AND pvo.unit_price >= :min_price";
    $params[':min_price'] = $min_price;
  }

  if (!empty($max_price)) {
    $sql .= " AND pvo.unit_price <= :max_price";
    $params[':max_price'] = $max_price;
  }

  // Group by product for the first variation price
  $sql .= " GROUP BY p.product_id";

  // Add sorting based on price
  if ($sort_order != 'none') {
    $sql .= $sort_order == 'desc' ? " ORDER BY first_variation_price DESC" : " ORDER BY first_variation_price ASC";
  }

  // Execute query
  $stmt = $_db->prepare($sql);
  $stmt->execute($params);
  $products = $stmt->fetchAll(); // Fetch result as object

}

// Set the page title
$_title = $category_name;
include '../../_customerPortalHead.php';
?>

<link rel="stylesheet" href="/css/member/productList.css">
<main>
  <!-- Category Title -->
  <h1><?= $category_name ?></h1>

  <!-- Category Description -->
  <?php if (!empty($category_description)): ?>
    <p class="category-description"><?= $category_description ?></p>
  <?php endif; ?>

  <!-- Search and Category Filter Form -->
  <form method="GET" action="productList.php" class="search-sort-form">
    <!-- Search Bar -->
    <?= html_search('query', 'placeholder="Search for products..."') ?>

    <!-- Category Filter -->
    <select name="category">
      <option value="all">All Categories</option>
      <?php foreach ($categories as $category): ?>
        <option value="<?= $category->categoryid ?>" <?= ($category_filter == $category->categoryid) ? 'selected' : '' ?>>
          <?= $category->name ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Min Price -->
    <?= html_number('min_price', '0', '', '', 'placeholder="Min Price"') ?>

    <!-- Max Price -->
    <?= html_number('max_price', '1', '', '', 'placeholder="Max Price"') ?>

    <!-- Sort Order -->
    <select name="sort_order">
      <option value="none" <?= $sort_order == 'none' ? 'selected' : '' ?>>No Sorting</option>
      <option value="asc" <?= $sort_order == 'asc' ? 'selected' : '' ?>>Sort by Price: Low to High</option>
      <option value="desc" <?= $sort_order == 'desc' ? 'selected' : '' ?>>Sort by Price: High to Low</option>
    </select>

    <!-- Submit Button -->
    <button type="submit">Search</button>

    <!-- Reset Button -->
    <button data-get="productList.php">Reset</button>
  </form>

  <!-- Product Grid -->
  <?php if (count($products) > 0): ?>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <a href="productDetail.php?productId=<?= $product->product_id ?>">
            <img src="/images/admin/product/product_image/<?= $product->product_image ?>" alt="<?= $product->name ?>">
            <h2><?= $product->name ?></h2>
            <?php if (!empty($product->first_variation_price)): ?>
              <p>RM <?= number_format($product->first_variation_price, 2) ?></p>
            <?php else: ?>
              <p>Price: Not Available</p>
            <?php endif; ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="product-grid no-products">
      <p class="no-products">No products found.</p>
    </div>
  <?php endif; ?>
</main>

<?php
include '../../_foot.php';
?>