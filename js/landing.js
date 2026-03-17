/**
 * landing.js — Custom JavaScript for QUENCH
 * Features: theme toggle, scroll reveals, navbar scroll, parallax,
 *           stat counters, hamburger menu, scroll-to-top, skeleton loader
 */

document.addEventListener("DOMContentLoaded", function () {

  /* ========== Theme Toggle ========== */
  var toggle = document.getElementById("themeToggle");
  var html = document.documentElement;

  if (toggle) {
    toggle.addEventListener("click", function () {
      var current = html.getAttribute("data-theme");
      if (current === "dark") {
        html.removeAttribute("data-theme");
        localStorage.setItem("quench-theme", "light");
      } else {
        html.setAttribute("data-theme", "dark");
        localStorage.setItem("quench-theme", "dark");
      }
    });
  }

  /* ========== Hamburger Menu ========== */
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

    // Close menu on link click
    var mobileLinks = mobileMenu.querySelectorAll("a");
    mobileLinks.forEach(function (link) {
      link.addEventListener("click", closeMenu);
    });

    // Close on Escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && mobileMenu.classList.contains("is-open")) {
        closeMenu();
      }
    });
  }

  /* ========== Scroll Reveal (IntersectionObserver) ========== */
  var revealElements = document.querySelectorAll(".reveal");

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
      { threshold: 0.15, rootMargin: "0px 0px -40px 0px" }
    );

    revealElements.forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    revealElements.forEach(function (el) {
      el.classList.add("visible");
    });
  }

  /* ========== Navbar darken on scroll ========== */
  var navbar = document.querySelector(".navbar");
  var scrollTopBtn = document.getElementById("scrollTopBtn");

  if (navbar || scrollTopBtn) {
    window.addEventListener("scroll", function () {
      var y = window.scrollY;

      // Navbar
      if (navbar) {
        if (y > 80) {
          navbar.classList.add("scrolled");
        } else {
          navbar.classList.remove("scrolled");
        }
      }

      // Scroll-to-top button
      if (scrollTopBtn) {
        if (y > 400) {
          scrollTopBtn.classList.add("is-visible");
        } else {
          scrollTopBtn.classList.remove("is-visible");
        }
      }
    }, { passive: true });
  }

  /* ========== Scroll-to-top click ========== */
  if (scrollTopBtn) {
    scrollTopBtn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  /* ========== Parallax Hero (about page) ========== */
  var parallaxEl = document.querySelector("[data-parallax]");

  if (parallaxEl) {
    window.addEventListener("scroll", function () {
      parallaxEl.style.backgroundPositionY = (window.scrollY * 0.4) + "px";
    }, { passive: true });
  }

  /* ========== Skeleton Loader ========== */
  var skeletonGrid = document.getElementById("skeletonGrid");
  var productList = document.getElementById("productList");

  if (skeletonGrid && productList) {
    // Short delay to show skeleton, then fade in real content
    setTimeout(function () {
      skeletonGrid.style.display = "none";
      productList.style.display = "";
      productList.style.opacity = "0";
      productList.style.transition = "opacity 0.4s ease";

      // Trigger reflow then fade in
      requestAnimationFrame(function () {
        productList.style.opacity = "1";
      });
    }, 600);
  }

  /* ========== Animated Stat Counters ========== */
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

});

/* ========== Newsletter form (UI only) ========== */
function handleNewsletter(e) {
  e.preventDefault();

  var form = e.target;
  var input = form.querySelector(".newsletter-input");
  var msg = document.getElementById("newsletterMsg");

  if (input && input.value) {
    msg.textContent = "Thanks for subscribing!";
    input.value = "";

    setTimeout(function () {
      msg.textContent = "";
    }, 4000);
  }
}