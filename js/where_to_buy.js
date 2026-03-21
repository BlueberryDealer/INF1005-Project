/**
 * where_to_buy.js — Google Maps + Places API for QUENCH Store Locator
 */

var wtbMap, wtbService, wtbInfoWindow;
var wtbMarkers = [];
var wtbAllPlaces = [];
var wtbCurrentPlaces = [];
var wtbBounds;
var wtbFilterOpen = false;

/* ========== Init ========== */
function initWtbMap() {
  var singapore = { lat: 1.3521, lng: 103.8198 };

  wtbMap = new google.maps.Map(document.getElementById("wtbMap"), {
    center: singapore, zoom: 12,
    styles: getMapStyles(),
    disableDefaultUI: false,
    zoomControl: true,
    streetViewControl: false,
    mapTypeControl: false,
    fullscreenControl: true
  });

  wtbInfoWindow = new google.maps.InfoWindow();
  wtbService = new google.maps.places.PlacesService(wtbMap);

  searchFairPrice(singapore);

  // Search input
  var searchInput = document.getElementById("wtbSearchInput");
  if (searchInput) {
    var debounceTimer;
    searchInput.addEventListener("input", function () {
      clearTimeout(debounceTimer);
      var query = searchInput.value.trim();
      debounceTimer = setTimeout(function () {
        if (query.length > 0) {
          searchFairPriceByArea(query);
        } else {
          wtbCurrentPlaces = wtbAllPlaces.slice();
          applyFilter();
        }
      }, 400);
    });
  }

  // Open Now filter toggle
  var filterOpenBtn = document.getElementById("wtbFilterOpen");
  if (filterOpenBtn) {
    filterOpenBtn.addEventListener("click", function () {
      wtbFilterOpen = !wtbFilterOpen;
      filterOpenBtn.classList.toggle("is-active", wtbFilterOpen);
      applyFilter();
    });
  }
}

/* ========== Filter ========== */
function applyFilter() {
  var results = wtbCurrentPlaces.slice();

  if (wtbFilterOpen) {
    results = results.filter(function (place) {
      return place.opening_hours && place.opening_hours.isOpen();
    });
  }

  displayResults(results);

  var total = wtbCurrentPlaces.length;
  var shown = results.length;
  if (wtbFilterOpen && shown < total) {
    updateStatus(shown + " open now (of " + total + " found)");
  } else {
    updateStatus(shown + " FairPrice outlet" + (shown !== 1 ? "s" : "") + " found");
  }
}

/* ========== Initial Search ========== */
function searchFairPrice(center) {
  updateStatus("Searching for FairPrice outlets...");
  var request = { query: "FairPrice Singapore", type: "supermarket" };

  wtbService.textSearch(request, function (results, status) {
    if (status === google.maps.places.PlacesServiceStatus.OK && results) {
      wtbAllPlaces = results;
      wtbCurrentPlaces = results.slice();
      applyFilter();
    } else {
      var fallback = { location: center, radius: 25000, keyword: "FairPrice", type: "supermarket" };
      wtbService.nearbySearch(fallback, function (results2, status2) {
        if (status2 === google.maps.places.PlacesServiceStatus.OK && results2) {
          wtbAllPlaces = results2;
          wtbCurrentPlaces = results2.slice();
          applyFilter();
        } else {
          updateStatus("Could not load stores. Ensure Places API is enabled.");
        }
      });
    }
  });
}

/* ========== Search by area ========== */
function searchFairPriceByArea(area) {
  updateStatus('Searching near "' + area + '"...');
  var request = { query: "FairPrice " + area + " Singapore", type: "supermarket" };

  wtbService.textSearch(request, function (results, status) {
    if (status === google.maps.places.PlacesServiceStatus.OK && results) {
      wtbCurrentPlaces = results;
      applyFilter();
    } else {
      clearMarkers();
      clearResultsList();
      updateStatus('No outlets found near "' + area + '". Try a different area.');
    }
  });
}

/* ========== Display Results ========== */
function displayResults(places) {
  clearMarkers();
  clearResultsList();

  if (places.length === 0) {
    var listEl = document.getElementById("wtbResultsList");
    listEl.innerHTML =
      '<li class="wtb-empty-state">' +
      '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
      '<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5"/>' +
      '<line x1="16.65" y1="16.65" x2="21" y2="21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>' +
      '</svg>' +
      '<span>No outlets match your filters</span>' +
      '</li>';
    return;
  }

  wtbBounds = new google.maps.LatLngBounds();
  var listEl = document.getElementById("wtbResultsList");

  places.forEach(function (place, index) {
    if (!place.geometry || !place.geometry.location) return;

    var marker = new google.maps.Marker({
      map: wtbMap, position: place.geometry.location,
      title: place.name,
      label: { text: String(index + 1), color: "#fff", fontWeight: "700", fontSize: "12px" },
      animation: google.maps.Animation.DROP
    });

    wtbMarkers.push(marker);
    wtbBounds.extend(place.geometry.location);
    marker.addListener("click", function () { showInfoWindow(place, marker); highlightCardByIndex(index); });

    var li = document.createElement("li");
    li.className = "wtb-result-card";
    li.setAttribute("role", "listitem");
    li.setAttribute("tabindex", "0");
    li.setAttribute("data-index", index);

    var isOpen = place.opening_hours ? place.opening_hours.isOpen() : null;
    var statusHtml = isOpen !== null
      ? '<span class="wtb-result-status ' + (isOpen ? "wtb-open" : "wtb-closed") + '">' +
        (isOpen ? "Open Now" : "Closed") + "</span>"
      : "";

    var ratingHtml = place.rating
      ? '<div class="wtb-result-rating">' +
        '<span class="wtb-stars" aria-label="Rating: ' + place.rating + ' out of 5">' + renderStars(place.rating) + "</span>" +
        '<span class="wtb-rating-num">' + place.rating + "</span>" +
        "</div>"
      : "";

    li.innerHTML =
      '<div class="wtb-result-top">' +
      '<span class="wtb-result-num">' + (index + 1) + "</span>" +
      '<div class="wtb-result-info">' +
      '<div class="wtb-result-name">' + escapeHtml(place.name) + "</div>" +
      '<div class="wtb-result-addr">' + escapeHtml(place.formatted_address || place.vicinity || "") + "</div>" +
      ratingHtml +
      "</div>" +
      statusHtml +
      "</div>";

    li.addEventListener("click", function () {
      wtbMap.panTo(place.geometry.location);
      wtbMap.setZoom(16);
      showInfoWindow(place, marker);
      highlightCard(li);
    });

    li.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") { e.preventDefault(); li.click(); }
    });

    listEl.appendChild(li);
  });

  fitMapToMarkers();
}

/* ========== Info Window ========== */
function showInfoWindow(place, marker) {
  var lat = place.geometry.location.lat();
  var lng = place.geometry.location.lng();
  var isOpen = place.opening_hours ? place.opening_hours.isOpen() : null;

  var content =
    '<div class="wtb-iw">' +
    '<div class="wtb-iw-name">' + escapeHtml(place.name) + "</div>" +
    '<div class="wtb-iw-addr">' + escapeHtml(place.formatted_address || place.vicinity || "") + "</div>" +
    (place.rating ? '<div class="wtb-iw-rating">' + renderStars(place.rating) + " " + place.rating + "/5</div>" : "") +
    (isOpen !== null ? '<div class="wtb-iw-status ' + (isOpen ? "wtb-open" : "wtb-closed") + '">' + (isOpen ? "Open Now" : "Currently Closed") + "</div>" : "") +
    '<a class="wtb-iw-dir" href="https://www.google.com/maps/dir/?api=1&destination=' + lat + "," + lng + '" target="_blank" rel="noopener noreferrer">Get Directions →</a>' +
    "</div>";

  wtbInfoWindow.setContent(content);
  wtbInfoWindow.open(wtbMap, marker);
}

/* ========== Helpers ========== */
function clearMarkers() {
  wtbMarkers.forEach(function (m) { m.setMap(null); });
  wtbMarkers = [];
}

function clearResultsList() {
  var listEl = document.getElementById("wtbResultsList");
  if (listEl) listEl.innerHTML = "";
}

function fitMapToMarkers() {
  if (wtbMarkers.length > 0 && wtbBounds) {
    wtbMap.fitBounds(wtbBounds);
    var listener = google.maps.event.addListener(wtbMap, "idle", function () {
      if (wtbMap.getZoom() > 16) wtbMap.setZoom(16);
      google.maps.event.removeListener(listener);
    });
  }
}

function highlightCard(activeCard) {
  document.querySelectorAll(".wtb-result-card").forEach(function (c) { c.classList.remove("is-active"); });
  activeCard.classList.add("is-active");
  activeCard.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function highlightCardByIndex(index) {
  var card = document.querySelector('.wtb-result-card[data-index="' + index + '"]');
  if (card) highlightCard(card);
}

function updateStatus(text) {
  var el = document.getElementById("wtbStatus");
  if (el) el.querySelector(".wtb-status-text").textContent = text;
}

function renderStars(rating) {
  var html = "";
  for (var i = 1; i <= 5; i++) {
    html += (rating >= i || rating >= i - 0.5) ? "★" : "☆";
  }
  return html;
}

function escapeHtml(str) {
  var div = document.createElement("div");
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
}

function getMapStyles() {
  return [
    { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] },
    { featureType: "transit", elementType: "labels", stylers: [{ visibility: "simplified" }] }
  ];
}