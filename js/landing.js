/**
 * landing.js — Custom JavaScript for QUENCH
 * Handles: theme toggle, scroll reveals, navbar scroll, parallax, stat counters
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

  if (navbar) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 80) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
    }, { passive: true });
  }

  /* ========== Parallax Hero (about page) ========== */
  var parallaxEl = document.querySelector("[data-parallax]");

  if (parallaxEl) {
    window.addEventListener("scroll", function () {
      var scrolled = window.scrollY;
      parallaxEl.style.backgroundPositionY = (scrolled * 0.4) + "px";
    }, { passive: true });
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