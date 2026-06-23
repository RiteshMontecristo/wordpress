/**
 * Google Places address autocomplete for WooCommerce checkout block.
 *
 * The checkout block is React-rendered, so we use MutationObserver to detect
 * when address fields appear, and native input event dispatching to update
 * React's internal state when a place is selected.
 */

const attached = new Set();

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
