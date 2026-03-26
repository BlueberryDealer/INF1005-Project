<?php
require_once __DIR__ . '/security/session.php';
require_once __DIR__ . '/models/order_model.php';
require_once __DIR__ . '/security/sanitization.php';
require_once __DIR__ . '/security/csrf.php';
$session = new SessionManager();
$topSellers = [];
$csrfToken = CSRFToken::get();

try {
    $topSellers = getHomepageTopSellingProducts(4);
} catch (Throwable $e) {
    $topSellers = [];
}

include __DIR__ . "/components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/components/navbar.php"; ?>

<main id="maincontent">

  <!-- ===== FULL-BLEED HERO WITH PARALLAX LAYERS ===== -->
  <section class="hero" aria-label="Welcome banner">

    <!-- Cursor-reactive glow overlay -->
    <div class="hero-glow" id="heroGlow" aria-hidden="true"></div>

    <div class="hero-slideshow" aria-hidden="true">
      <div class="hero-slide slide1"></div>
      <div class="hero-slide slide2"></div>
      <div class="hero-slide slide3"></div>
    </div>

    <div class="hero-overlay">
      <div class="container">
        <div class="hero-content">
          <p class="hero-tag reveal-up" data-delay="0">Experience Refreshment.</p>

          <h1 class="reveal-up" data-delay="1">Your Favorite Sodas<br>and Drinks, <span class="text-accent">Delivered.</span></h1>

          <p class="hero-sub reveal-up" data-delay="2">
            Discover the best in classic sodas, sports drinks, and energy boosts.
          </p>

          <a href="pages/products.php" class="hero-btn magnetic-btn reveal-up" data-delay="3" role="button">
            Explore Our Collection
            <svg class="hero-btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="hero-scroll-hint" aria-hidden="true">
      <span class="scroll-text">Scroll</span>
      <div class="scroll-line"></div>
    </div>

  </section>

  <!-- ===== BRAND MARQUEE STRIP ===== -->
  <section class="marquee-strip" aria-label="Brand tagline">
    <div class="marquee-track">
      <div class="marquee-content">
        <span class="marquee-item">Premium Selection</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Same-Day Delivery</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Quality Guaranteed</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">50+ Brands</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Zero Compromise</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Premium Selection</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Same-Day Delivery</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Quality Guaranteed</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">50+ Brands</span>
        <span class="marquee-dot" aria-hidden="true"></span>
        <span class="marquee-item">Zero Compromise</span>
        <span class="marquee-dot" aria-hidden="true"></span>
      </div>
    </div>
  </section>

  <!-- ===== TOP SELLERS ===== -->
  <section class="top-sellers parallax-section" data-speed="0.03" aria-label="Top selling products">
    <div class="container">
      <div class="section-header reveal-up">
        <h2 class="section-title-bold">Top Sellers</h2>
        <a href="pages/products.php" class="section-link magnetic-btn">
          View All
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      </div>

      <div class="product-grid">
        <?php if (!empty($topSellers)): ?>
          <?php foreach ($topSellers as $product): ?>
            <?php $detailUrl = '/pages/product_details.php?id=' . (int)$product['product_id']; ?>
            <div
              class="shop-card product-card reveal"
              data-product-id="<?= (int)$product['product_id'] ?>"
              data-name="<?= Sanitizer::escape((string)$product['name']) ?>"
              data-price="<?= Sanitizer::escape((string)$product['price']) ?>"
            >
              <a href="<?= $detailUrl ?>" class="shop-card-img"
                aria-label="View details for <?= Sanitizer::escape((string)$product['name']) ?>">
                <img
                  src="/images/<?= Sanitizer::escape((string)$product['image_url']) ?>"
                  alt="<?= Sanitizer::escape((string)$product['name']) ?>"
                  loading="lazy"
                  onerror="this.src='/images/placeholder.png'"
                >
              </a>

              <div class="shop-card-body">
                <a href="<?= $detailUrl ?>" class="shop-card-title-link">
                  <h3 class="shop-card-title"><?= Sanitizer::escape((string)$product['name']) ?></h3>
                </a>
                <p class="shop-card-desc card-text">
                  <?= Sanitizer::escape((string)($product['description'] ?? 'A customer favorite from our best-selling collection.')) ?>
                </p>
                <span class="shop-card-price">$<?= number_format((float)$product['price'], 2) ?></span>

                <?php if ($session->getRole() === 'admin'): ?>
                  <button class="shop-btn shop-btn--disabled" disabled>Not available for admins</button>
                <?php elseif ((int)$product['quantity'] <= 0): ?>
                  <button class="shop-btn shop-btn--disabled" disabled>Unavailable</button>
                <?php else: ?>
                  <button class="shop-btn add-cart">Add to Cart</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="product-card tilt-card reveal-up" data-delay="0"></div>
          <div class="product-card tilt-card reveal-up" data-delay="1"></div>
          <div class="product-card tilt-card reveal-up" data-delay="2"></div>
          <div class="product-card tilt-card reveal-up" data-delay="3"></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ===== WHY QUENCH (3D tilt cards) ===== -->
  <section class="why-us parallax-section" data-speed="0.02" aria-label="Why choose QUENCH">
    <div class="container">
      <h2 class="section-title-bold center reveal-up">Why QUENCH</h2>

      <div class="row g-4">

        <div class="col-md-4 reveal-up" data-delay="0">
          <div class="value-block tilt-card">
            <div class="why-icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h3>Premium Selection</h3>
            <p>Curated collection of the finest sodas, craft beverages, and energy drinks from top brands worldwide.</p>
          </div>
        </div>

        <div class="col-md-4 reveal-up" data-delay="1">
          <div class="value-block tilt-card">
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

        <div class="col-md-4 reveal-up" data-delay="2">
          <div class="value-block tilt-card">
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
      <div class="cta-inner reveal-up">
        <p class="cta-overline">Ready to Refresh?</p>
        <h2>Browse our full catalog and find<br>your new favorite drink today.</h2>
        <a href="pages/products.php" class="cta-btn magnetic-btn" role="button">
          Shop Now
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      </div>
    </div>
  </section>

</main>

<input type="hidden" id="csrf-token" value="<?= Sanitizer::escape($csrfToken) ?>">

<?php if (!$session->isAuthenticated()): ?>
<!-- ===== SIGNUP POPUP (guests only, scroll-triggered) ===== -->
<div class="popup-overlay" id="signupPopupOverlay" aria-hidden="true">
  <div class="popup-modal" role="dialog" aria-labelledby="popupTitle" aria-modal="true">
    <button type="button" class="popup-close" id="popupCloseBtn" aria-label="Close popup">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>

    <div class="popup-body">
      <div class="popup-icon" aria-hidden="true">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
          <path d="M20 12V22H4V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M22 7H2V12H22V7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 22V7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 7H7.5C6.83696 7 6.20107 6.73661 5.73223 6.26777C5.26339 5.79893 5 5.16304 5 4.5C5 3.83696 5.26339 3.20107 5.73223 2.73223C6.20107 2.26339 6.83696 2 7.5 2C11 2 12 7 12 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 7H16.5C17.163 7 17.7989 6.73661 18.2678 6.26777C18.7366 5.79893 19 5.16304 19 4.5C19 3.83696 18.7366 3.20107 18.2678 2.73223C17.7989 2.26339 17.163 2 16.5 2C13 2 12 7 12 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <h2 class="popup-title" id="popupTitle">Get 10% Off Your First Order</h2>
      <p class="popup-text">Sign up for a QUENCH account and enjoy <strong>10% off</strong> your first purchase. Refreshment is just a click away.</p>

      <a href="/auth/register.php" class="popup-cta" role="button">
        Create Account
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>

      <p class="popup-note">A discount code will be sent to your email after signup!</p>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>
