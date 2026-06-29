(function () {
  const data = window.mjiBlockCartSubBrands;
  if (!data) return;

  const paths = data.paths || {};
  const names = data.names || {};

  function normalizePath(href) {
    try {
      return new URL(href).pathname.replace(/\/$/, "") || "/";
    } catch {
      return (href || "").split("?")[0].replace(/\/$/, "");
    }
  }

  function findSubBrand(el) {
    // Primary: match by product permalink path (cart block)
    if (el.href) {
      const sub = paths[normalizePath(el.href)];
      if (sub) return sub;
    }
    // Fallback: match by product name text (checkout order summary)
    const text = (el.textContent || "").toLowerCase().trim();
    return names[text] || null;
  }

  function inject() {
    document
      .querySelectorAll(".wc-block-components-product-name:not([data-mji-sb])")
      .forEach(function (el) {
        el.dataset.mjiSb = "1";
        const sub = findSubBrand(el);
        if (!sub) return;

        // Insert span + space as first children so they stay inline with the title
        const span = document.createElement("span");
        span.textContent = sub;
        el.insertBefore(document.createTextNode(" "), el.firstChild);
        el.insertBefore(span, el.firstChild);
      });
  }

  inject();

  new MutationObserver(inject).observe(document.body, {
    childList: true,
    subtree: true,
  });
})();
