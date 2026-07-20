const bindings = {};

function injectDeliveryDates() {
  const estimates = window.mjiShippingEstimates;
  if (!estimates) return;

  const shippingSection = document.querySelector(
    ".wc-block-components-shipping-rates-control, " +
      '[data-block-name="woocommerce/checkout-shipping-methods-block"]',
  );
  if (!shippingSection) return;

  shippingSection.querySelectorAll('input[type="radio"]').forEach((input) => {
    if (input.dataset.mjDeliveryInjected) return;

    const methodType = (input.value || "").split(":")[0];
    const dateRange = estimates[methodType];
    if (!dateRange) return;

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

function injectPickupInfo() {
  const data = window.mjiPickupData;
  if (!data) return;

  document
    .querySelectorAll(
      ".wc-block-checkout__pickup-options .wc-block-components-radio-control__option",
    )
    .forEach((option) => {
      if (option.dataset.mjPickupInjected) return;

      const nameEl = option.querySelector(
        ".wc-block-components-radio-control__label",
      );
      if (!nameEl) return;

      const locationName = Object.keys(data).find(
        (key) => nameEl.textContent.trim() === key,
      );
      if (!locationName) return;

      const { hours, ready } = data[locationName];

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

function injectOrderDisclaimer() {
  const orderSummary = document.querySelector(
    ".wp-block-woocommerce-checkout-order-summary-block",
  );
  if (!orderSummary) return;

  const next = orderSummary.nextElementSibling;
  if (next && next.classList.contains("mji-order-disclaimer")) return;

  document
    .querySelectorAll(".mji-order-disclaimer")
    .forEach((el) => el.remove());

  const notice = document.createElement("p");
  notice.className = "mji-order-disclaimer";
  notice.style.cssText =
    "margin-top:12px;padding:10px 12px;font-size:0.85em;color:#444;" +
    "background:#f7f7f5;border-left:3px solid #21453a;";
  notice.textContent =
    "This is an estimated delivery date only. Once your order is with the courier, we're unable to guarantee delivery on this exact date.";
  orderSummary.after(notice);
}

const deliveryObserver = new MutationObserver(() => {
  injectDeliveryDates();
  injectPickupInfo();
  injectOrderDisclaimer();
});
deliveryObserver.observe(document.body, { childList: true, subtree: true });
injectDeliveryDates();
injectPickupInfo();
injectOrderDisclaimer();

window.initMJIAutocomplete = function () {
  tryAttach();

  const observer = new MutationObserver(tryAttach);
  observer.observe(document.body, { childList: true, subtree: true });
};

function tryAttach() {
  attachAutocomplete("shipping");
  attachAutocomplete("billing");
}

function trackListeners(input) {
  if (input._mjiListeners) return;
  input._mjiListeners = [];
  const originalAdd = input.addEventListener.bind(input);
  input.addEventListener = function (evType, listener, options) {
    input._mjiListeners.push([evType, listener, options]);
    return originalAdd(evType, listener, options);
  };
}

function stripGoogleListeners(input) {
  (input._mjiListeners || []).forEach(([evType, listener, options]) => {
    input.removeEventListener(evType, listener, options);
  });
  if (input._mjiListeners) input._mjiListeners.length = 0;
}

function attachAutocomplete(type) {
  if (
    typeof google === "undefined" ||
    !google.maps ||
    !google.maps.places
  ) {
    return;
  }

  const input =
    document.querySelector(`#${type}-address_1`) ||
    document.querySelector(`input[autocomplete="${type} address-line1"]`);

  if (!input) return;

  const existing = bindings[type];
  if (existing && existing.input === input && document.body.contains(input)) {
    return;
  }

  if (existing) {
    stripGoogleListeners(existing.input);
    google.maps.event.clearInstanceListeners(existing.autocomplete);
    existing.pacContainer?.remove();
  }

  const pacContainersBefore = new Set(document.querySelectorAll(".pac-container"));

  trackListeners(input);
  const ac = new google.maps.places.Autocomplete(input, {
    types: ["address"],
    fields: ["address_components"],
  });

  ac.addListener("place_changed", function () {
    const place = ac.getPlace();
    if (!place.address_components) return;

    stripGoogleListeners(input);
    google.maps.event.clearInstanceListeners(ac);
    bindings[type]?.pacContainer?.remove();
    delete bindings[type];

    fillFields(type, place.address_components, input);

    attachAutocomplete(type);
  });

  const pacContainer = Array.from(
    document.querySelectorAll(".pac-container"),
  ).find((el) => !pacContainersBefore.has(el));

  bindings[type] = { input, autocomplete: ac, pacContainer };
}

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
  const country = get(components, "country", true);
  const state = get(components, "administrative_area_level_1", true);

  setInput(addressInput, address1);

  setInput(
    document.querySelector(`#${type}-city`) ||
      document.querySelector(`input[autocomplete="${type} address-level2"]`),
    city,
  );

  const countryEl =
    document.querySelector(`#${type}-country`) ||
    document.querySelector(`select[autocomplete="${type} country"]`);
  if (countryEl) setSelect(countryEl, country);

  setTimeout(() => {
    const postcodeEl =
      document.querySelector(`#${type}-postcode`) ||
      document.querySelector(`input[autocomplete="${type} postal-code"]`);
    setInput(postcodeEl, postcode);

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
