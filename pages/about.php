<?php
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

include __DIR__ . "/../components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="about-page">

  <!-- ===== FULL-BLEED HERO ===== -->
  <section class="about-hero-fullbleed" aria-label="About QUENCH" data-parallax>
    <div class="about-hero-overlay">
      <div class="container">
        <h1 class="about-hero-title reveal">
          We Don't Just<br>Sell Drinks.<br><span class="text-accent">We Deliver Refreshment.</span>
        </h1>
      </div>
    </div>
  </section>

  <!-- ===== BRAND STATEMENT ===== -->
  <section class="about-statement" aria-label="Brand statement">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
          <p class="statement-text reveal">
            QUENCH was built on a simple belief — finding your favorite drinks 
            should be effortless. We curate the best sodas, energy drinks, and 
            sparkling refreshments so you never have to settle.
          </p>
          <p class="statement-text reveal">
            From classic flavors to the latest drops, we bring it all together 
            in one place — delivered straight to your door across Singapore.
          </p>
          <p class="statement-sig reveal">— The QUENCH Team</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== STATS ===== -->
  <section class="about-stats" aria-label="Key numbers">
    <div class="container">
      <div class="row text-center">

        <div class="col-6 col-md-3 reveal">
          <div class="about-stat">
            <span class="stat-number" data-count="50">0</span><span class="stat-suffix">+</span>
            <span class="stat-label">Brands</span>
          </div>
        </div>

        <div class="col-6 col-md-3 reveal">
          <div class="about-stat">
            <span class="stat-number" data-count="500">0</span><span class="stat-suffix">+</span>
            <span class="stat-label">Products</span>
          </div>
        </div>

        <div class="col-6 col-md-3 reveal">
          <div class="about-stat">
            <span class="stat-number" data-count="1000">0</span><span class="stat-suffix">+</span>
            <span class="stat-label">Customers</span>
          </div>
        </div>

        <div class="col-6 col-md-3 reveal">
          <div class="about-stat">
            <span class="stat-number" data-count="24">0</span><span class="stat-suffix">hr</span>
            <span class="stat-label">Delivery</span>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== WHAT WE CARRY ===== -->
  <section class="about-range" aria-label="What we carry">
    <div class="container">
      <h2 class="range-title reveal">What We Carry</h2>

      <div class="range-grid">

        <div class="range-item reveal">
          <div class="range-num" aria-hidden="true">01</div>
          <div class="range-body">
            <h3>Classic Sodas</h3>
            <p>Cola, root beer, lemon-lime, ginger ale — the drinks you grew up with, always in stock and always ice cold.</p>
          </div>
        </div>

        <div class="range-item reveal">
          <div class="range-num" aria-hidden="true">02</div>
          <div class="range-body">
            <h3>Energy Drinks</h3>
            <p>Pre-workout fuel, late-night power, or just that afternoon pick-me-up. Bold flavors, serious performance.</p>
          </div>
        </div>

        <div class="range-item reveal">
          <div class="range-num" aria-hidden="true">03</div>
          <div class="range-body">
            <h3>Sparkling Refreshments</h3>
            <p>Sparkling waters, fruit sodas, artisan tonics. Light, fizzy, and perfect for any occasion.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== VALUES ===== -->
  <section class="about-values" aria-label="Our values">
    <div class="container">
      <h2 class="values-title reveal">What Drives Us</h2>

      <div class="row g-4">

        <div class="col-md-4 reveal">
          <div class="value-block">
            <h3>Quality First</h3>
            <p>Every product is vetted for freshness and authenticity. We work directly with distributors to ensure you get the best.</p>
          </div>
        </div>

        <div class="col-md-4 reveal">
          <div class="value-block">
            <h3>Customer Obsessed</h3>
            <p>From intuitive navigation to fast checkout, every detail is designed around your experience.</p>
          </div>
        </div>

        <div class="col-md-4 reveal">
          <div class="value-block">
            <h3>Always Evolving</h3>
            <p>We constantly expand our catalog with trending flavors and new brands. There's always something new to discover.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== CTA ===== -->
  <section class="about-cta-bold" aria-label="Shop now">
    <div class="container text-center">
      <h2 class="cta-bold-title reveal">Ready to Refresh?</h2>
      <a href="/pages/products.php" class="cta-btn-bold reveal" role="button">
        Shop Now
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
    </div>
  </section>

</main>

<?php include __DIR__ . "/../components/footer.php"; ?>