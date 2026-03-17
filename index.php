<?php
require_once __DIR__ . '/security/session.php';
$session = new SessionManager();

include __DIR__ . "/components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/components/navbar.php"; ?>

<main id="maincontent">

  <!-- ===== FULL-BLEED HERO ===== -->
  <section class="hero" aria-label="Welcome banner">

    <div class="hero-slideshow" aria-hidden="true">
      <div class="hero-slide slide1"></div>
      <div class="hero-slide slide2"></div>
      <div class="hero-slide slide3"></div>
    </div>

    <div class="hero-overlay">
      <div class="container">
        <div class="hero-content">
          <p class="hero-tag reveal">Experience Refreshment.</p>

          <h1 class="reveal">Your Favorite Sodas<br>and Drinks, <span class="text-accent">Delivered.</span></h1>

          <p class="hero-sub reveal">
            Discover the best in classic sodas, sports drinks, and energy boosts.
          </p>

          <a href="pages/products.php" class="hero-btn reveal" role="button">
            Explore Our Collection
            <svg class="hero-btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

  </section>

  <!-- ===== BRAND TAGLINE STRIP ===== -->
  <section class="tagline-strip" aria-label="Brand tagline">
    <div class="container text-center">
      <p class="tagline-text reveal">
        Where Great Taste Meets Convenience. <span class="text-accent-bright">Zero Compromise.</span>
      </p>
    </div>
  </section>

  <!-- ===== TOP SELLERS ===== -->
  <section class="top-sellers" aria-label="Top selling products">
    <div class="container">
      <div class="section-header reveal">
        <h2 class="section-title-bold">Top Sellers</h2>
        <a href="pages/products.php" class="section-link">
          View All
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      </div>

      <div class="product-grid">
        <div class="product-card reveal"></div>
        <div class="product-card reveal"></div>
        <div class="product-card reveal"></div>
        <div class="product-card reveal"></div>
      </div>
    </div>
  </section>

  <!-- ===== WHY QUENCH (bold border cards) ===== -->
  <section class="why-us" aria-label="Why choose QUENCH">
    <div class="container">
      <h2 class="section-title-bold center reveal">Why QUENCH</h2>

      <div class="row g-4">

        <div class="col-md-4 reveal">
          <div class="value-block">
            <div class="why-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Premium Selection</h3>
            <p>Curated collection of the finest sodas, craft beverages, and energy drinks from top brands worldwide.</p>
          </div>
        </div>

        <div class="col-md-4 reveal">
          <div class="value-block">
            <div class="why-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <rect x="1" y="3" width="15" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M16 8h4l3 4v4h-7V8z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="5.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                <circle cx="18.5" cy="18.5" r="2.5" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </div>
            <h3>Fast Delivery</h3>
            <p>Same-day delivery across Singapore. Your drinks arrive chilled and ready to enjoy at your doorstep.</p>
          </div>
        </div>

        <div class="col-md-4 reveal">
          <div class="value-block">
            <div class="why-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Quality Guaranteed</h3>
            <p>Every product is stored and shipped under optimal conditions. Not satisfied? We'll make it right.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== CTA BANNER ===== -->
  <section class="cta-banner" aria-label="Call to action">
    <div class="container text-center">
      <div class="cta-inner reveal">
        <h2>Ready to Refresh?</h2>
        <p>Browse our full catalog and find your new favorite drink today.</p>
        <a href="pages/products.php" class="cta-btn" role="button">
          Shop Now
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . '/components/footer.php'; ?>