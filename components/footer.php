<footer class="site-footer">
  <div class="container footer-container">
    <div class="footer-top">

      <div class="footer-brand-block">
        <div class="footer-brand">QUENCH</div>
        <p class="footer-text">
          Refreshing sodas and drinks, delivered with style.
        </p>
      </div>

      <div class="footer-links-block">
        <h3 class="footer-heading">Quick Links</h3>
        <ul class="footer-links">
          <li><a href="/index.php">Home</a></li>
          <li><a href="/pages/products.php">Shop</a></li>
          <li><a href="/pages/about.php">About</a></li>
          <li><a href="/pages/cart.php">Cart</a></li>
        </ul>
      </div>

      <div class="footer-newsletter-block">
        <h3 class="footer-heading">Stay in the Loop</h3>
        <p class="footer-text">Get notified about new drops, exclusive deals, and more.</p>
        <form class="newsletter-form" onsubmit="handleNewsletter(event)">
          <div class="newsletter-input-wrap">
            <input
              type="email"
              class="newsletter-input"
              placeholder="Enter your email"
              aria-label="Email for newsletter"
              required
            >
            <button type="submit" class="newsletter-btn" aria-label="Subscribe to newsletter">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <p class="newsletter-msg" id="newsletterMsg"></p>
        </form>
      </div>

    </div>

    <div class="footer-bottom">
      <p class="footer-copy">© <?php echo date("Y"); ?> QUENCH. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/main.js"></script>
<script src="/js/cart.js"></script>
<script src="/js/landing.js"></script>
</body>
</html>