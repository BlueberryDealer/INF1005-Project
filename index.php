<?php
include __DIR__ . "/components/header.php";
include __DIR__ . "/components/navbar.php";

require_once __DIR__ . '/security/session.php';
$session = new SessionManager();
?>

<main id="maincontent">

  <section class="hero">

    <div class="hero-slideshow">
      <div class="hero-slide slide1"></div>
      <div class="hero-slide slide2"></div>
      <div class="hero-slide slide3"></div>
    </div>

    <div class="hero-content">
      <p class="hero-tag">EXPERIENCE REFRESHMENT.</p>

      <h1>Your Favorite Sodas and Drinks, Delivered.</h1>

      <p class="hero-sub">
        Discover the best in classic sodas, sports drinks, and energy boosts.
      </p>

      <a href="pages/products.php" class="hero-btn">
        Explore Our Collection
      </a>
    </div>

  </section>

  <section class="top-sellers">
    <div class="container">
      <h2 class="section-title">Top Sellers</h2>

      <div class="product-grid">
        <div class="product-card"></div>
        <div class="product-card"></div>
        <div class="product-card"></div>
        <div class="product-card"></div>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . "/components/footer.php"; ?>