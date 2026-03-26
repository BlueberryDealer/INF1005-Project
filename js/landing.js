/**
 * landing.js — Premium JavaScript for QUENCH
 * 
 * Custom JS features:
 *   1. Theme toggle (dark/light mode persistence)
 *   2. Scroll-triggered reveal animations (IntersectionObserver)
 *   3. Parallax scrolling on sections
 *   4. 3D tilt cards with shine overlay (mouse-tracking)
 *   5. Magnetic button hover effect (cursor-following)
 *   6. Hero cursor glow (radial gradient follows mouse)
 *   7. Hero slide zoom on scroll
 *   8. Navbar scroll state
 *   9. Hamburger mobile menu
 *  10. Scroll-to-top button
 *  11. Animated stat counters
 *  12. Skeleton loader (product page)
 *  13. Signup popup (scroll-triggered, guests only)
 *  14. Infinite marquee (CSS-driven, JS fallback for clone)
 *  15. Newsletter subscription (AJAX)
 */

document.addEventListener("DOMContentLoaded", function () {

  // Check reduced motion preference once
  var prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ==========================================================
     1. THEME TOGGLE
     ========================================================== */
  var toggle = document.getElementById("themeToggle");
  var html = document.documentElement;

  if (toggle) {
    toggle.addEventListener("click", function () {
      var current = html.getAttribute("data-theme");
      if (current === "dark") {
    html.setAttribute("data-theme", "light");
    localStorage.setItem("quench-theme", "light");
    } else {
        html.setAttribute("data-theme", "dark");
        localStorage.setItem("quench-theme", "dark");
      }
    });
  }


  /* ==========================================================
     2. SCROLL-TRIGGERED REVEAL ANIMATIONS (IntersectionObserver)
     ========================================================== */
  // Supports both .reveal (legacy) and .reveal-up (new premium)
  var revealElements = document.querySelectorAll(".reveal, .reveal-up");

  if (revealElements.length > 0 && "IntersectionObserver" in window) {
    var revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -60px 0px" }
    );

    revealElements.forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    // Fallback: show everything immediately
    revealElements.forEach(function (el) {
      el.classList.add("visible");
    });
  }


  /* ==========================================================
     3. PARALLAX SCROLLING ON SECTIONS
     ========================================================== */
  var parallaxSections = document.querySelectorAll(".parallax-section");
  var aboutParallax = document.querySelector("[data-parallax]");

  if (!prefersReducedMotion && (parallaxSections.length > 0 || aboutParallax)) {
    var lastScrollY = 0;
    var ticking = false;

    function updateParallax() {
      var scrollY = window.scrollY;
      var windowHeight = window.innerHeight;

      // Parallax sections — subtle vertical shift based on scroll position
      parallaxSections.forEach(function (section) {
        var rect = section.getBoundingClientRect();
        var speed = parseFloat(section.getAttribute("data-speed")) || 0.03;

        // Only apply when section is in view
        if (rect.bottom > 0 && rect.top < windowHeight) {
          var offset = (rect.top - windowHeight / 2) * speed;
          section.style.transform = "translateY(" + offset + "px) translateZ(0)";
        }
      });

      // About page background parallax
      if (aboutParallax) {
        aboutParallax.style.backgroundPositionY = (scrollY * 0.4) + "px";
      }

      ticking = false;
    }

    window.addEventListener("scroll", function () {
      if (!ticking) {
        requestAnimationFrame(updateParallax);
        ticking = true;
      }
    }, { passive: true });
  }


  /* ==========================================================
     4. 3D TILT CARDS WITH SHINE OVERLAY
     ========================================================== */
  var tiltCards = document.querySelectorAll(".tilt-card");

  if (!prefersReducedMotion && tiltCards.length > 0 && window.innerWidth > 768) {
    tiltCards.forEach(function (card) {
      // Inject shine overlay element
      var shine = document.createElement("div");
      shine.className = "tilt-shine";
      card.appendChild(shine);

      card.addEventListener("mousemove", function (e) {
        var rect = card.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var centerX = rect.width / 2;
        var centerY = rect.height / 2;

        // Calculate tilt angles (max ±8 degrees)
        var rotateX = ((y - centerY) / centerY) * -8;
        var rotateY = ((x - centerX) / centerX) * 8;

        card.style.transform =
          "perspective(800px) rotateX(" + rotateX + "deg) rotateY(" + rotateY + "deg) scale3d(1.02, 1.02, 1.02)";

        // Update shine position
        var shineX = (x / rect.width) * 100;
        var shineY = (y / rect.height) * 100;
        card.style.setProperty("--shine-x", shineX + "%");
        card.style.setProperty("--shine-y", shineY + "%");
      });

      card.addEventListener("mouseleave", function () {
        card.style.transform =
          "perspective(800px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)";
      });
    });
  }


  /* ==========================================================
     5. MAGNETIC BUTTON HOVER EFFECT
     ========================================================== */
  var magneticBtns = document.querySelectorAll(".magnetic-btn");

  if (!prefersReducedMotion && magneticBtns.length > 0 && window.innerWidth > 768) {
    magneticBtns.forEach(function (btn) {
      btn.addEventListener("mousemove", function (e) {
        var rect = btn.getBoundingClientRect();
        var x = e.clientX - rect.left - rect.width / 2;
        var y = e.clientY - rect.top - rect.height / 2;

        // Subtle pull toward cursor (max ~6px shift)
        var moveX = x * 0.15;
        var moveY = y * 0.15;

        btn.style.transform = "translate(" + moveX + "px, " + moveY + "px)";
      });

      btn.addEventListener("mouseleave", function () {
        btn.style.transform = "translate(0, 0)";
      });
    });
  }


  /* ==========================================================
     6. HERO CURSOR GLOW (radial gradient follows mouse)
     ========================================================== */
  var heroSection = document.querySelector(".hero");
  var heroGlow = document.getElementById("heroGlow");

  if (!prefersReducedMotion && heroSection && heroGlow && window.innerWidth > 768) {
    heroSection.addEventListener("mousemove", function (e) {
      var rect = heroSection.getBoundingClientRect();
      var x = e.clientX - rect.left;
      var y = e.clientY - rect.top;

      heroGlow.style.setProperty("--glow-x", x + "px");
      heroGlow.style.setProperty("--glow-y", y + "px");
    });
  }


  /* ==========================================================
     7. HERO SLIDE ZOOM ON SCROLL
     ========================================================== */
  if (heroSection) {
    var heroZoomApplied = false;

    window.addEventListener("scroll", function () {
      if (window.scrollY > 50 && !heroZoomApplied) {
        heroSection.classList.add("is-scrolled");
        heroZoomApplied = true;
      } else if (window.scrollY <= 50 && heroZoomApplied) {
        heroSection.classList.remove("is-scrolled");
        heroZoomApplied = false;
      }
    }, { passive: true });
  }


  /* ==========================================================
     8. NAVBAR SCROLL STATE + SCROLL-TO-TOP
     ========================================================== */
  var navbar = document.querySelector(".navbar");
  var scrollTopBtn = document.getElementById("scrollTopBtn");

  if (navbar || scrollTopBtn) {
    window.addEventListener("scroll", function () {
      var y = window.scrollY;

      if (navbar) {
        if (y > 80) {
          navbar.classList.add("scrolled");
        } else {
          navbar.classList.remove("scrolled");
        }
      }

      if (scrollTopBtn) {
        if (y > 400) {
          scrollTopBtn.classList.add("is-visible");
        } else {
          scrollTopBtn.classList.remove("is-visible");
        }
      }
    }, { passive: true });
  }

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }


  /* ==========================================================
     9. HAMBURGER MOBILE MENU
     ========================================================== */
  var hamburger = document.getElementById("hamburgerBtn");
  var mobileMenu = document.getElementById("mobileMenu");
  var overlay = document.getElementById("mobileOverlay");

  function openMenu() {
    hamburger.classList.add("is-active");
    hamburger.setAttribute("aria-expanded", "true");
    mobileMenu.classList.add("is-open");
    mobileMenu.setAttribute("aria-hidden", "false");
    overlay.classList.add("is-visible");
    document.body.style.overflow = "hidden";
  }

  function closeMenu() {
    hamburger.classList.remove("is-active");
    hamburger.setAttribute("aria-expanded", "false");
    mobileMenu.classList.remove("is-open");
    mobileMenu.setAttribute("aria-hidden", "true");
    overlay.classList.remove("is-visible");
    document.body.style.overflow = "";
  }

  if (hamburger && mobileMenu && overlay) {
    hamburger.addEventListener("click", function () {
      if (mobileMenu.classList.contains("is-open")) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    overlay.addEventListener("click", closeMenu);

    var mobileLinks = mobileMenu.querySelectorAll("a");
    mobileLinks.forEach(function (link) {
      link.addEventListener("click", closeMenu);
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && mobileMenu.classList.contains("is-open")) {
        closeMenu();
      }
    });
  }


  /* ==========================================================
     10. ANIMATED STAT COUNTERS (eased count-up on scroll)
     ========================================================== */
  var statNumbers = document.querySelectorAll(".stat-number[data-count]");

  if (statNumbers.length > 0 && "IntersectionObserver" in window) {
    var countersStarted = false;

    var counterObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting && !countersStarted) {
            countersStarted = true;
            animateCounters();
            counterObserver.disconnect();
          }
        });
      },
      { threshold: 0.3 }
    );

    counterObserver.observe(statNumbers[0]);
  }

  function animateCounters() {
    var duration = 1800;

    statNumbers.forEach(function (el) {
      var target = parseInt(el.getAttribute("data-count"), 10);
      var startTime = null;

      function step(timestamp) {
        if (!startTime) startTime = timestamp;
        var progress = Math.min((timestamp - startTime) / duration, 1);
        // Cubic ease-out for smooth deceleration
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(eased * target).toLocaleString();

        if (progress < 1) {
          requestAnimationFrame(step);
        } else {
          el.textContent = target.toLocaleString();
        }
      }

      requestAnimationFrame(step);
    });
  }


  /* ==========================================================
     11. SKELETON LOADER (products page)
     ========================================================== */
  var skeletonGrid = document.getElementById("skeletonGrid");
  var productList = document.getElementById("productList");

  if (skeletonGrid && productList) {
    setTimeout(function () {
      skeletonGrid.style.display = "none";
      productList.style.display = "";
      productList.style.opacity = "0";
      productList.style.transition = "opacity 0.4s ease";

      requestAnimationFrame(function () {
        productList.style.opacity = "1";
      });
    }, 600);
  }


  /* ==========================================================
     12. SIGNUP POPUP (scroll-triggered, guests only)
     ========================================================== */
  var popupOverlay = document.getElementById("signupPopupOverlay");
  var popupCloseBtn = document.getElementById("popupCloseBtn");

  if (popupOverlay && popupCloseBtn) {
    var popupShown = false;
    var popupDismissed = sessionStorage.getItem("quench-popup-dismissed") === "true";

    function showPopup() {
      if (popupShown || popupDismissed) return;
      popupShown = true;
      popupOverlay.classList.add("is-visible");
      popupOverlay.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
      popupCloseBtn.focus();
    }

    function hidePopup() {
      popupOverlay.classList.remove("is-visible");
      popupOverlay.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
      sessionStorage.setItem("quench-popup-dismissed", "true");
      popupDismissed = true;
    }

    if (!popupDismissed) {
      window.addEventListener("scroll", function onScrollPopup() {
        var scrollPercent = window.scrollY / (document.body.scrollHeight - window.innerHeight);
        if (scrollPercent > 0.35) {
          showPopup();
          window.removeEventListener("scroll", onScrollPopup);
        }
      }, { passive: true });
    }

    popupCloseBtn.addEventListener("click", hidePopup);

    popupOverlay.addEventListener("click", function (e) {
      if (e.target === popupOverlay) {
        hidePopup();
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && popupOverlay.classList.contains("is-visible")) {
        hidePopup();
      }
    });
  }

});


/* ==========================================================
   13. NEWSLETTER FORM (AJAX submission)
   ========================================================== */
function handleNewsletter(e) {
  e.preventDefault();

  var form = e.target;
  var input = form.querySelector(".newsletter-input");
  var msg = document.getElementById("newsletterMsg");

  if (!input || !input.value) return;

  msg.textContent = "Subscribing...";

  var formData = new FormData();
  formData.append("email", input.value);

  fetch("/pages/newsletter_subscribe.php", {
    method: "POST",
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    msg.textContent = data.message;
    if (data.success) {
      input.value = "";
    }
    setTimeout(function() { msg.textContent = ""; }, 4000);
  })
  .catch(function() {
    msg.textContent = "Something went wrong. Try again.";
    setTimeout(function() { msg.textContent = ""; }, 4000);
  });
}