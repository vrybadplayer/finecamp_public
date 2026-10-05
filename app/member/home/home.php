<?php
require '../../_base.php';
// Fetch New Arrival Products
$query_new_arrivals = "SELECT p.*, MIN(pvo.unit_price) AS first_variation_price
    FROM product p
    LEFT JOIN product_variation_option pvo ON p.product_id = pvo.product_id
    WHERE p.status = 'active'
    GROUP BY p.product_id
    ORDER BY p.product_id DESC
    LIMIT 8";

$stmt_new_arrivals = $_db->prepare($query_new_arrivals);
$stmt_new_arrivals->execute();
$new_arrivals = $stmt_new_arrivals->fetchAll();

// Set the page title
$_title = 'Welcome to Fine Camp';
$_css_file = '/css/member/home.css';
include '../../_customerPortalHead.php';
?>

<!-- Hero Section -->
<section id="hero" class="hero-section">
  <div class="hero-overlay">
    <div class="hero-content">
      <h1>Welcome to Fine Camp</h1>
      <p>Your One-Stop Shop for Premium Camping Equipment</p>
      <a href="/member/product/productList.php" class="hero-btn">Shop Now</a>
    </div>
  </div>
</section>



<!-- New Arrivals Section -->
<section id="new-arrivals" class="new-arrivals">
  <div class="container">
    <h2>New Arrivals</h2>
    <p class="description">Discover the latest camping equipment just added to our store.</p>
    <div class="product-grid">
      <?php if (count($new_arrivals) > 0): ?>
        <?php foreach ($new_arrivals as $product): ?>
          <div class="product-card">
            <a href="/member/product/productDetail.php?productId=<?= $product->product_id ?>">
              <img src="/images/admin/product/product_image/<?= $product->product_image ?>" alt="<?= $product->name ?>">
              <h3><?= $product->name ?></h3>
              <?php if (!empty($product->first_variation_price)): ?>
                <p>RM <?= number_format($product->first_variation_price, 2) ?></p>
              <?php else: ?>
                <p>Price: Not Available</p>
              <?php endif; ?>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No new arrivals at the moment. Please check back later.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- About Us Section -->
<section id="about-us" class="about-us">
  <div class="container">
    <div class="about-content">
      <!-- About Text -->
      <div class="about-text">
        <h2>About Fine Camp</h2>
        <p>At Fine Camp, we are passionate about helping outdoor enthusiasts embrace the beauty of nature with the best camping equipment. Our wide selection of top-quality gear is designed for comfort, durability, and adventure. Whether you're planning a weekend getaway or a long trekking expedition, Fine Camp has everything you need to make your outdoor experience unforgettable.</p>
        <p>We believe that camping should be accessible to everyone, which is why we offer a wide range of equipment suited for all types of campers – from beginners to seasoned pros. Explore our collection of tents, backpacks, sleeping bags, outdoor cooking gear, and more.</p>
        <p>Join the Fine Camp family and make your next adventure one to remember!</p>
      </div>

      <!-- About Image -->
      <div class="about-image">
        <img src="/images/member/home/about_us.jpg" alt="Fine Camp">
      </div>
    </div>
  </div>
</section>

<!-- YouTube Section -->
<section id="youtube-section" class="youtube-section">
  <div class="container">
    <div class="youtube-row">
      <!-- YouTube Video -->
      <div class="youtube-video">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/YEB4Wfk-ELI?si=kQMtbHd4hWcMmvqs" title="15 COOLEST CAMPING GADGETS YOU DIDN'T KNOW" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
      </div>

      <!-- Title and Description -->
      <div class="youtube-description">
        <h2>15 Coolest Camping Gadgets You Didn't Know About</h2>
        <p>Explore the latest and coolest camping gadgets in this amazing video. Whether you're a seasoned camper or just starting out, these gadgets will help enhance your outdoor adventures. From portable grills to solar-powered gadgets, find out what could make your next camping trip more enjoyable!</p>
      </div>
    </div>
  </div>
</section>


<!-- Services Section -->
<section id="services" class="services">
  <div class="container">
    <!-- Section Title and Description -->
    <div class="services-header">
      <h2>Our Services</h2>
      <p>We offer top-notch services to ensure your camping experience is seamless and enjoyable.</p>
    </div>
    <div class="service-row">
      <!-- Column 1: Quality Product -->
      <div class="service-column">
        <img src="/images/member/home/quality_icon.png" alt="Quality Product">
        <h3>Quality Product</h3>
        <p>Our products are crafted with the highest quality materials to ensure durability.</p>
      </div>

      <!-- Column 2: Flexible Payment -->
      <div class="service-column">
        <img src="/images/member/home/secure_payment_icon.png" alt="Flexible Payment">
        <h3>Flexible Payment</h3>
        <p>Choose from a variety of payment methods to suit your needs.</p>
      </div>

      <!-- Column 3: Online Support -->
      <div class="service-column">
        <img src="/images/member/home/support_icon.png" alt="Online Support">
        <h3>Online Support</h3>
        <p>We offer 24/7 online support to assist you with your queries.</p>
      </div>

      <!-- Column 4: Fast Shipping -->
      <div class="service-column">
        <img src="/images/member/home/fast_shipping_icon.png" alt="Fast Shipping">
        <h3>Fast Shipping</h3>
        <p>Enjoy fast and reliable shipping on all orders.</p>
      </div>
    </div>
  </div>
</section>


<!-- Contact Section with Google Map -->
<section id="find-us" class="find-us">
  <div class="container">
    <h2>Find Us</h2>
    <p class="description">Come visit us at our physical store! Easily accessible to all adventurers.</p>

    <div class="map-wrapper">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63742.96138386106!2d101.57021186953125!3d3.111877600000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4d574eb3f579%3A0x19647f85d55b0613!2sMontanic%20Adventure%20Store%20-%20SS2!5e0!3m2!1sen!2smy!4v1727020942922!5m2!1sen!2smy"
        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>
</section>


<?php
include '../../_foot.php';
?>