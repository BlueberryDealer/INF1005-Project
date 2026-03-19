<?php
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

include __DIR__ . "/../components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="wtb-page">

  <!-- ===== PAGE HEADER ===== -->
  <section class="wtb-header" aria-label="Where to buy header">
    <div class="container">
      <h1 class="wtb-title reveal">Where to Buy</h1>
      <p class="wtb-subtitle reveal">
        QUENCH products are available at <strong>FairPrice</strong> outlets across Singapore.
        Find your nearest store below.
      </p>
    </div>
  </section>

  <!-- ===== MAP + SIDEBAR LAYOUT ===== -->
  <section class="wtb-content" aria-label="Store locator">
    <div class="container">
      <div class="wtb-layout">

        <!-- Sidebar: search + results list -->
        <div class="wtb-sidebar" id="wtbSidebar">

          <div class="wtb-search-box">
            <label for="wtbSearchInput" class="visually-hidden">Search by area or postal code</label>
            <div class="wtb-search-wrap">
              <svg class="wtb-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                <line x1="16.65" y1="16.65" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <input
                type="text"
                id="wtbSearchInput"
                class="wtb-search-input"
                placeholder="Search by area (e.g. Tampines, Jurong)"
                autocomplete="off"
              >
            </div>
            <button type="button" class="wtb-locate-btn" id="wtbLocateBtn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                <path d="M12 2v4M12 18v4M2 12h4M18 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Use My Location
            </button>
          </div>

          <!-- Filter bar -->
          <div class="wtb-filter-bar">
            <button type="button" class="wtb-filter-btn" id="wtbFilterOpen">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                <path d="M8 12l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Open Now
            </button>
            <button type="button" class="wtb-filter-btn" id="wtbSortDistance" disabled title="Use My Location first">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="2"/>
                <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/>
              </svg>
              Nearest First
            </button>
          </div>

          <div class="wtb-results-status" id="wtbStatus">
            <span class="wtb-status-text">Searching for FairPrice outlets...</span>
          </div>

          <ul class="wtb-results-list" id="wtbResultsList" role="list" aria-label="Store results">
            <!-- Populated by JS -->
          </ul>
        </div>

        <!-- Map -->
        <div class="wtb-map-wrap">
          <div id="wtbMap" class="wtb-map" role="application" aria-label="Map showing FairPrice store locations"></div>
        </div>

      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . "/../components/footer.php"; ?>

<!-- Google Maps JS API with Places library -->
<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB_bwlsDlOr1emKMP6oTB9pjywGAkLNnjE&libraries=places&callback=initWtbMap">
</script>
<script src="/js/where_to_buy.js"></script>