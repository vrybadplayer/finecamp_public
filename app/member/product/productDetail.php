<?php
require '../../_base.php';

if (is_get()) {

  //get product id
  $product_id = req('productId');

  //less then 0 but not null
  if ($product_id <= 0 && $product_id !== null) {
    // Redirect to product list if no valid product ID is provided
    redirect('productList.php');
    exit;
  }

  // Fetch product details from the database with category name
  $stmt = $_db->prepare("SELECT p.*, c.name AS category_name FROM product p
                       INNER JOIN category c ON p.category_id = c.category_id
                       WHERE p.product_id = ? AND p.status = 'active'");
  $stmt->execute([$product_id]);
  $product = $stmt->fetch(); // Fetch as object


  if (!$product) {
    // Redirect to product list if product not found
    redirect('productList.php');
    exit;
  }

  // Fetch product variations
  $variations_stmt = $_db->prepare("SELECT * FROM product_variation_option WHERE product_id = ?");
  $variations_stmt->execute([$product_id]);
  $variations = $variations_stmt->fetchAll();

  // Collect all images (product image + variation images)
  $images = [];
  if (!empty($product->product_image)) {
    $images[] = '/images/admin/product/product_image/' . $product->product_image;
  }
  foreach ($variations as $variation) {
    if (!empty($variation->image)) {
      $images[] = '/images/admin/product/product_variations_image/' . $variation->image;
    }
  }

  // - Fetch products from the same category (excluding current product)
  // - Join category to get category name
  // - Left join to get the minimum variation price
  // - Limit the result to 4 products
  $related_product_stmt = $_db->prepare("
  SELECT p.*, c.name AS category_name, MIN(pvo.unit_price) AS first_variation_price
  FROM product p
  INNER JOIN category c ON p.category_id = c.category_id
  LEFT JOIN product_variation_option pvo ON p.product_id = pvo.product_id
  WHERE p.category_id = ? AND p.product_id != ? AND p.status = 'active'
  GROUP BY p.product_id
  LIMIT 4
");
  $related_product_stmt->execute([$product->category_id, $product_id]);
  $related_products = $related_product_stmt->fetchAll();

  // If less than 4 products are found, fetch additional products from other categories
  if (count($related_products) < 4) {
    $remaining_slots = 4 - count($related_products);

    //find the not related product based on the remaining slot
    $sql = "
      SELECT p.*, c.name AS category_name, MIN(pvo.unit_price) AS first_variation_price
      FROM product p
      INNER JOIN category c ON p.category_id = c.category_id
      LEFT JOIN product_variation_option pvo ON p.product_id = pvo.product_id
      WHERE p.category_id != ? AND p.product_id != ? AND p.status = 'active'
      GROUP BY p.product_id
      LIMIT $remaining_slots
    ";

    $other_product_stmt = $_db->prepare($sql);
    $other_product_stmt->execute([$product->category_id, $product_id]);
    $other_products = $other_product_stmt->fetchAll();

    // Merge the related products
    $related_products = array_merge($related_products, $other_products);
  }
}
// Set the page title
$_title = $product->name;
$_css_file = "/css/member/productDetail.css";
include '../../_customerPortalHead.php';
?>

<div class="product-details-container">
  <!-- Left Side: Image Slider -->
  <div class="image-slider">
    <div class="slider-wrapper">
      <?php foreach ($images as $index => $image_url): ?>
        <!-- Add 'active' class to the first slide (when $index is 0) -->
        <div class="slide <?= $index === 0 ? 'active' : '' ?>">
          <img src="<?= $image_url ?>" alt="<?= $product->name ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <!-- Navigation Arrows -->
    <button class="prev">&#10094;</button>
    <button class="next">&#10095;</button>
  </div>

  <!-- Right Side: Product Info -->
  <div class="product-info">
    <h1><?= $product->name ?></h1>
    <p><?= $product->description ?></p>

    <form action="../order/addToCart.php" method="GET">
      <?php
      //ensure $product_id and $variation_id are available globally or assign them manually
      $GLOBALS['product_id'] = $product->product_id;
      $GLOBALS['variation_id'] = ''; // Initially empty

      // Use the html_hidden function
      html_hidden('product_id');
      html_hidden('variation_id', 'id="variation_id"');
      ?>


      <!-- Variation Selection -->
      <div class="variations">
        <p><?= $product->variation_name ?>:</p>
        <div class="variation-options">
          <?php foreach ($variations as $variation): ?>
            <div class="variation-option <?= $variation->stock_quantity == 0 ? 'disabled' : '' ?>"
              data-variation-id="<?= $variation->product_variation_option_id ?>"
              data-stock="<?= $variation->stock_quantity ?>"
              data-image="<?= '/images/admin/product/product_variations_image/' . $variation->image ?>">
              <p><strong><?= $variation->name ?></strong></p>
              <p>RM <?= number_format($variation->unit_price, 2) ?></p>
              <?php if ($variation->stock_quantity == 0): ?>
                <p id="out-of-stock">Out of Stock</p>
              <?php else: ?>
                <p>Stock: <?= $variation->stock_quantity ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Quantity Input -->
      <label>Quantity:</label>
      <?php
      $GLOBALS['quantity'] = 1; // Default value
      html_number('quantity', '1', '', '1', 'required');
      ?>

      <!-- Add to Cart Button -->
      <button type="submit" id="add-to-cart-button" disabled>Add to Cart</button>
    </form>
  </div>
</div>

<div class="title-container">
  <?= html_h2('related-product-title', 'Related Products') ?>
</div>

<!-- Related Product Grid -->
<?php if (count($related_products) > 0): ?>
  <div class="product-grid">
    <?php foreach ($related_products as $product): ?>
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


<?php
include '../../_foot.php';
?>

<script src="/js/member/productDetail.js"></script>