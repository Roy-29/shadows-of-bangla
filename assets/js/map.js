/**
 * SHADOWS OF BANGLA — Interactive Leaflet Map Controller
 * Real Verified Coordinates, Custom Ghostly Markers & Dark Styling
 */

// Default center and zoom covering Bangladesh
const BD_CENTER = [23.6850, 90.3563];
const BD_ZOOM = 7;
let globalMap = null;
let markerLayer = null;
let allPlacesData = [];

document.addEventListener('DOMContentLoaded', () => {
  initFullMap();
  initMiniMap();
});

/**
 * Initializes the full interactive map on map.php or index.php preview
 */
function initFullMap() {
  const mapElement = document.getElementById('bangladesh-map');
  if (!mapElement) return;

  // Retrieve initial places data passed via JSON script tag or global variable
  const dataScript = document.getElementById('map-places-data');
  if (dataScript) {
    try {
      allPlacesData = JSON.parse(dataScript.textContent);
    } catch (e) {
      console.error('Error parsing places JSON:', e);
      allPlacesData = [];
    }
  }

  // Initialize Leaflet map
  globalMap = L.map('bangladesh-map', {
    center: BD_CENTER,
    zoom: BD_ZOOM,
    minZoom: 6,
    maxZoom: 18,
    zoomControl: false,
    attributionControl: true
  });

  // Zoom control in bottom-right
  L.control.zoom({ position: 'bottomright' }).addTo(globalMap);

  // Dark Map Tiles (CartoDB Dark Matter)
  const darkTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19
  });

  darkTiles.addTo(globalMap);

  // Initialize Marker Layer Group
  markerLayer = L.layerGroup().addTo(globalMap);

  // Render markers
  renderMapMarkers(allPlacesData);

  // Set up filter and control listeners
  setupMapControls();
}

/**
 * Creates custom pulsing red SVG marker
 */
function createCustomMarkerIcon(categorySlug) {
  return L.divIcon({
    className: 'custom-ghost-marker',
    html: `
      <div class="marker-pulse"></div>
      <div class="marker-core">
        <i class="fa-solid fa-location-dot"></i>
      </div>
    `,
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -18]
  });
}

/**
 * Render markers on Leaflet map
 */
function renderMapMarkers(places) {
  if (!markerLayer || !globalMap) return;
  markerLayer.clearLayers();

  if (!places || places.length === 0) return;

  const bounds = [];

  places.forEach(place => {
    const lat = parseFloat(place.latitude);
    const lng = parseFloat(place.longitude);

    if (isNaN(lat) || isNaN(lng)) return;

    bounds.push([lat, lng]);

    const marker = L.marker([lat, lng], {
      icon: createCustomMarkerIcon(place.category_slug)
    });

    const popupHtml = `
      <div class="map-popup-card">
        <img src="${place.cover_image || 'assets/images/panam-city.svg'}" class="map-popup-img" alt="${place.name}">
        <div class="map-popup-content">
          <div class="map-popup-location">${place.district}, ${place.division}</div>
          <h4 class="map-popup-title">${place.name}</h4>
          <a href="place.php?slug=${place.slug}" class="map-popup-link">
            Explore Mystery <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>
    `;

    marker.bindPopup(popupHtml, { maxWidth: 260, className: 'dark-popup' });
    markerLayer.addLayer(marker);
  });
}

/**
 * Setup toolbar controls: Search, Division filter, Category filter, Reset View
 */
function setupMapControls() {
  const divisionSelect = document.getElementById('map-filter-division');
  const categorySelect = document.getElementById('map-filter-category');
  const searchInput = document.getElementById('map-search-input');
  const resetBtn = document.getElementById('map-reset-btn');

  function applyFilters() {
    const divisionVal = divisionSelect ? divisionSelect.value.toLowerCase() : '';
    const categoryVal = categorySelect ? categorySelect.value.toLowerCase() : '';
    const searchVal = searchInput ? searchInput.value.trim().toLowerCase() : '';

    const filtered = allPlacesData.filter(place => {
      const matchDivision = !divisionVal || place.division.toLowerCase() === divisionVal;
      const matchCategory = !categoryVal || (place.category_slug && place.category_slug.toLowerCase() === categoryVal);
      const matchSearch = !searchVal || 
        place.name.toLowerCase().includes(searchVal) ||
        place.district.toLowerCase().includes(searchVal) ||
        place.summary.toLowerCase().includes(searchVal);

      return matchDivision && matchCategory && matchSearch;
    });

    renderMapMarkers(filtered);

    // Update result count if indicator exists
    const countEl = document.getElementById('map-markers-count');
    if (countEl) {
      countEl.textContent = `${filtered.length} locations found`;
    }
  }

  if (divisionSelect) divisionSelect.addEventListener('change', applyFilters);
  if (categorySelect) categorySelect.addEventListener('change', applyFilters);
  if (searchInput) searchInput.addEventListener('input', applyFilters);

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      if (divisionSelect) divisionSelect.value = '';
      if (categorySelect) categorySelect.value = '';
      if (searchInput) searchInput.value = '';
      applyFilters();
      globalMap.setView(BD_CENTER, BD_ZOOM);
    });
  }
}

/**
 * Initializes a single-location mini map on place.php
 */
function initMiniMap() {
  const miniMapEl = document.getElementById('place-mini-map');
  if (!miniMapEl) return;

  const lat = parseFloat(miniMapEl.dataset.lat);
  const lng = parseFloat(miniMapEl.dataset.lng);
  const name = miniMapEl.dataset.name || 'Location';

  if (isNaN(lat) || isNaN(lng)) return;

  const miniMap = L.map('place-mini-map', {
    center: [lat, lng],
    zoom: 13,
    zoomControl: true,
    scrollWheelZoom: false
  });

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
    subdomains: 'abcd',
    maxZoom: 18
  }).addTo(miniMap);

  const marker = L.marker([lat, lng], {
    icon: createCustomMarkerIcon('place')
  }).addTo(miniMap);

  marker.bindPopup(`<b>${name}</b><br><small>${lat.toFixed(4)}, ${lng.toFixed(4)}</small>`).openPopup();
}

