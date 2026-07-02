/**
 * Google Places address autocomplete for WooCommerce checkout block.
 *
 * The checkout block is React-rendered, so we use MutationObserver to detect
 * when address fields appear, and native input event dispatching to update
 * React's internal state when a place is selected.
 */

const attached = new Set();

// ── Estimated delivery dates ──────────────────────────────────────────────────
// window.mjiShippingEstimates is set by PHP in wp_footer, e.g.:
// { flat_rate: "July 4–8", free_shipping: "July 4–8" }
//
// The checkout block renders shipping options as radio inputs whose `value`
// attribute is the full rate ID, e.g. "flat_rate:1". We watch for those
// inputs, match them to our estimates map, and append the date below the label.

function injectDeliveryDates() {
  const estimates = window.mjiShippingEstimates;
  if (!estimates) return;

  // Search within the shipping rates section only
  const shippingSection = document.querySelector(
    ".wc-block-components-shipping-rates-control, " +
      '[data-block-name="woocommerce/checkout-shipping-methods-block"]',
  );
  if (!shippingSection) return;

  shippingSection.querySelectorAll('input[type="radio"]').forEach((input) => {
    if (input.dataset.mjDeliveryInjected) return;

    // Rate value is e.g. "flat_rate:1" — match on prefix
    const methodType = (input.value || "").split(":")[0];
    const dateRange = estimates[methodType];
    if (!dateRange) return;

    // Use label[for=id] — works regardless of block markup changes
    const label =
      (input.id &&
        document.querySelector(`label[for="${CSS.escape(input.id)}"]`)) ||
      input.closest("li, div")?.querySelector("label");
    if (!label) return;

    const dateEl = document.createElement("span");
    dateEl.className = "mji-delivery-estimate";
    dateEl.textContent = "Estimated delivery: " + dateRange;
    dateEl.style.cssText =
      "display:block;font-size:0.8em;color:#666;margin-top:2px;font-weight:400";
    label.appendChild(dateEl);

    input.dataset.mjDeliveryInjected = "1";
  });
}

// ── Pickup location hours + ready date ───────────────────────────────────────
// window.mjiPickupData is set by PHP, keyed by the exact location name:
// { "Montecristo Richmond": { hours: "...", ready: "Tuesday, July 1" }, ... }

function injectPickupInfo() {
  const data = window.mjiPickupData;
  if (!data) return;

  document
    .querySelectorAll(
      ".wc-block-checkout__pickup-options .wc-block-components-radio-control__option",
    )
    .forEach((option) => {
      if (option.dataset.mjPickupInjected) return;

      // Location name is in the __label span
      const nameEl = option.querySelector(
        ".wc-block-components-radio-control__label",
      );
      if (!nameEl) return;

      const locationName = Object.keys(data).find(
        (key) => nameEl.textContent.trim() === key,
      );
      if (!locationName) return;

      const { hours, ready } = data[locationName];

      // Append after the address description group
      const descGroup = option.querySelector(
        ".wc-block-components-radio-control__description-group",
      );
      if (!descGroup) return;

      const clockSvg =
        `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="13" height="13" ` +
        `fill="none" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ` +
        `style="vertical-align:-2px;margin-right:4px">` +
        `<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`;

      const info = document.createElement("div");
      info.className = "mji-pickup-info";
      info.style.cssText = "margin-top:6px;";
      info.innerHTML =
        `<span style="display:block;font-size:0.8em;color:#666">` +
        `${clockSvg}${hours}</span>` +
        `<span style="display:block;font-size:0.8em;color:#21453a;font-weight:500;margin-top:2px">` +
        `Ready for pickup: ${ready}</span>`;

      descGroup.after(info);
      option.dataset.mjPickupInjected = "1";
    });
}

// Watch for the shipping/pickup blocks to render (React mounts them asynchronously).
const deliveryObserver = new MutationObserver(() => {
  injectDeliveryDates();
  injectPickupInfo();
});
deliveryObserver.observe(document.body, { childList: true, subtree: true });
injectDeliveryDates();
injectPickupInfo();

// Called by the Google Maps JS API once it has finished loading.
window.initMJIAutocomplete = function () {
  tryAttach();

  // Watch for React re-renders (e.g. switching between shipping/billing tabs).
  const observer = new MutationObserver(tryAttach);
  observer.observe(document.body, { childList: true, subtree: true });
};

function tryAttach() {
  attachAutocomplete("shipping");
  attachAutocomplete("billing");
}

function attachAutocomplete(type) {
  // WooCommerce checkout block renders address_1 as: #shipping-address_1 / #billing-address_1
  const input =
    document.querySelector(`#${type}-address_1`) ||
    document.querySelector(`input[autocomplete="${type} address-line1"]`);

  if (!input || attached.has(input)) return;
  attached.add(input);

  const ac = new google.maps.places.Autocomplete(input, {
    types: ["address"],
    fields: ["address_components"],
  });

  ac.addListener("place_changed", function () {
    const place = ac.getPlace();
    if (!place.address_components) return;
    fillFields(type, place.address_components, input);
  });
}

// React tracks input values through its own synthetic system.
// Setting .value directly doesn't trigger a re-render — we must use the
// native setter + dispatch an input event so React picks up the change.
function setInput(el, value) {
  if (!el) return;
  const setter = Object.getOwnPropertyDescriptor(
    window.HTMLInputElement.prototype,
    "value",
  ).set;
  setter.call(el, value);
  el.dispatchEvent(new Event("input", { bubbles: true }));
  el.dispatchEvent(new Event("change", { bubbles: true }));
}

function setSelect(el, value) {
  if (!el) return;
  const setter = Object.getOwnPropertyDescriptor(
    window.HTMLSelectElement.prototype,
    "value",
  ).set;
  setter.call(el, value);
  el.dispatchEvent(new Event("change", { bubbles: true }));
}

function get(components, type, short = false) {
  const c = components.find((c) => c.types.includes(type));
  return c ? (short ? c.short_name : c.long_name) : "";
}

function fillFields(type, components, addressInput) {
  const streetNumber = get(components, "street_number");
  const route = get(components, "route");
  const address1 = [streetNumber, route].filter(Boolean).join(" ");
  const city =
    get(components, "locality") ||
    get(components, "postal_town") ||
    get(components, "administrative_area_level_2");
  const postcode = get(components, "postal_code");
  const country = get(components, "country", true); // ISO 2-letter code
  const state = get(components, "administrative_area_level_1", true);

  // Address line 1
  setInput(addressInput, address1);

  // City
  setInput(
    document.querySelector(`#${type}-city`) ||
      document.querySelector(`input[autocomplete="${type} address-level2"]`),
    city,
  );

  // Postcode
  setInput(
    document.querySelector(`#${type}-postcode`) ||
      document.querySelector(`input[autocomplete="${type} postal-code"]`),
    postcode,
  );

  // Country — WooCommerce renders this as a <select>
  const countryEl =
    document.querySelector(`#${type}-country`) ||
    document.querySelector(`select[autocomplete="${type} country"]`);
  if (countryEl) setSelect(countryEl, country);

  // State/Province — can be <input> or <select> depending on country
  // WooCommerce re-renders the state field after country changes, so we
  // wait a tick before filling it in.
  setTimeout(() => {
    const stateEl =
      document.querySelector(`#${type}-state`) ||
      document.querySelector(`input[autocomplete="${type} address-level1"]`) ||
      document.querySelector(`select[autocomplete="${type} address-level1"]`);
    if (!stateEl) return;
    if (stateEl.tagName === "SELECT") {
      setSelect(stateEl, state);
    } else {
      setInput(stateEl, state);
    }
  }, 300);
}
