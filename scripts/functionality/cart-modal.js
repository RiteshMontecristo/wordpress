(function () {
  const overlay = document.getElementById("mc-cart-modal-overlay");
  if (!overlay) return;

  const heading    = document.getElementById("mc-cart-modal-heading");
  const messageEl  = document.getElementById("mc-cart-modal-message");
  const closeBtn   = document.getElementById("mc-cart-modal-close");
  const continueBtn = document.getElementById("mc-cart-modal-continue");

  function openModal(data, title) {
    if (heading) heading.textContent = title || "Added to cart";

    const img = document.getElementById("mc-cart-modal-img");
    if (img) { img.src = data.image || ""; img.alt = data.name || ""; }

    const nameEl = document.getElementById("mc-cart-modal-name");
    if (nameEl) nameEl.textContent = data.name || "";

    const attrsEl = document.getElementById("mc-cart-modal-attrs");
    if (attrsEl) attrsEl.textContent = data.attributes || "";

    const priceEl = document.getElementById("mc-cart-modal-price");
    if (priceEl) priceEl.textContent = data.price || "";

    const subtotalEl = document.getElementById("mc-cart-modal-subtotal");
    if (subtotalEl) subtotalEl.textContent = data.subtotal || "";

    const qtyEl = document.getElementById("mc-cart-modal-qty");
    if (qtyEl) qtyEl.textContent = data.qty ? `Qty : ${data.qty}` : "";

    const brandEl = document.getElementById("mc-cart-modal-brand");
    if (brandEl) {
      brandEl.innerHTML = data.brand ? `<strong>Brand:</strong> ${data.brand}` : "";
    }

    if (messageEl) {
      messageEl.textContent   = data.message || "";
      messageEl.style.display = data.message ? "block" : "none";
    }

    const viewCartLink = document.getElementById("mc-cart-modal-view-cart");
    if (viewCartLink && data.cart_url) viewCartLink.href = data.cart_url;

    overlay.setAttribute("aria-hidden", "false");
    overlay.classList.add("is-open");
    document.body.classList.add("mc-modal-open");
  }

  function closeModal() {
    overlay.setAttribute("aria-hidden", "true");
    overlay.classList.remove("is-open");
    document.body.classList.remove("mc-modal-open");
  }

  // Refresh WooCommerce cart fragments (header count, mini-cart, etc.)
  function refreshCartFragments() {
    fetch("/?wc-ajax=get_refreshed_fragments")
      .then((r) => r.json())
      .then((data) => {
        if (!data?.fragments) return;
        Object.entries(data.fragments).forEach(([sel, html]) => {
          document.querySelectorAll(sel).forEach((el) => {
            el.outerHTML = html;
          });
        });
      })
      .catch(() => {});
  }

  // Intercept every add-to-cart form submit on the page
  document.addEventListener("submit", async function (e) {
    const form = e.target;
    if (
      !form.classList.contains("cart") ||
      !form.querySelector('[name="add-to-cart"]')
    )
      return;

    e.preventDefault();

    const btn         = form.querySelector('[name="add-to-cart"]');
    const productId   = btn?.value;
    const qty         = parseInt(form.querySelector('[name="quantity"]')?.value) || 1;
    const variationId = parseInt(form.querySelector('[name="variation_id"]')?.value) || 0;

    if (!productId || !window.mcCart?.ajax_url) {
      form.submit(); // no config — fall back to normal POST
      return;
    }

    if (btn) btn.disabled = true;

    try {
      const res  = await fetch(window.mcCart.ajax_url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action:       "mc_add_to_cart",
          nonce:        window.mcCart.nonce,
          product_id:   productId,
          quantity:     qty,
          variation_id: variationId,
        }),
      });
      const json = await res.json();

      if (json.success) {
        openModal(json.data || {}, "Added to cart");
        refreshCartFragments();
      } else {
        openModal(json.data || {}, (json.data && json.data.title) || "Unable to add");
      }
    } catch {
      form.submit(); // network error — fall back to page reload
    } finally {
      if (btn) btn.disabled = false;
    }
  });

  closeBtn?.addEventListener("click", closeModal);
  continueBtn?.addEventListener("click", closeModal);
  document.getElementById("mc-cart-modal-view-cart")?.addEventListener("click", closeModal);
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && overlay.classList.contains("is-open")) closeModal();
  });

  // Page-reload fallback (JS was disabled on the previous request)
  if (window.mcCartModalData?.name) {
    openModal(window.mcCartModalData, "Added to cart");
  } else if (window.mcAlreadyInCartData?.name) {
    openModal(window.mcAlreadyInCartData, window.mcAlreadyInCartData.title || "Already in your cart");
  }
})();
