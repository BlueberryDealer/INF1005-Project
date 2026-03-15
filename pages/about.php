<?php
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";
?>

<main class="about-page">

<section class="about-hero-image">

  <div class="about-hero-overlay">

    <p class="about-tag">ABOUT QUENCH</p>

    <h1 class="about-title">Refreshing Moments, Delivered.</h1>

    <p class="about-subtitle">
      QUENCH brings together your favorite sodas and drinks in one place,
      making it easy to discover, shop, and enjoy every refreshment.
    </p>

  </div>

</section>

  <section class="about-section">
    <div class="container about-grid">
      <div>
        <h2 class="about-heading">Our Story</h2>
        <p class="about-text">
          QUENCH was created with one simple idea: to make refreshing drinks
          more accessible, convenient, and enjoyable for everyone. From classic
          sodas to exciting new flavors, we want every customer to find a drink
          they love.
        </p>
        <p class="about-text">
          Our platform is designed to deliver a smooth shopping experience,
          combining modern design with a carefully curated beverage selection.
        </p>
      </div>

      <div class="about-card">
        <h3>Our Mission</h3>
        <p>
          To deliver refreshing beverages with convenience, variety, and style.
        </p>
      </div>
    </div>
  </section>

  <section class="about-section light-section">
    <div class="container">
      <h2 class="about-heading center">What We Offer</h2>

      <div class="about-features">
        <div class="feature-card">
          <h3>Classic Sodas</h3>
          <p>Timeless favorites that everyone knows and loves.</p>
        </div>

        <div class="feature-card">
          <h3>Energy Drinks</h3>
          <p>Bold choices for customers looking for an extra boost.</p>
        </div>

        <div class="feature-card">
          <h3>Sparkling Refreshments</h3>
          <p>Light, fizzy, and perfect for every occasion.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="about-section">
    <div class="container">
      <h2 class="about-heading center">Why Choose QUENCH</h2>

      <div class="about-features">
        <div class="feature-card">
          <h3>Wide Variety</h3>
          <p>A curated range of drinks for different tastes and preferences.</p>
        </div>

        <div class="feature-card">
          <h3>Modern Experience</h3>
          <p>A clean and simple shopping journey designed for convenience.</p>
        </div>

        <div class="feature-card">
          <h3>Reliable Delivery</h3>
          <p>Your favorite drinks, delivered quickly and smoothly.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="about-cta">
    <div class="container">
      <h2 class="about-heading center">Explore Our Collection</h2>
      <p class="about-subtitle center">
        Discover our range of sodas and refreshing beverages today.
      </p>
      <div class="about-cta-btn-wrap">
        <a href="/pages/products.php" class="hero-btn">Shop All Drinks</a>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . "/../components/footer.php"; ?>