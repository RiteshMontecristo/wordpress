// Live product search dropdown in the header search form.
const searchField = document.querySelector("#woocommerce-product-search-field-0");
const searchResults = document.querySelector("#searchResults");

const MIN_TERM_LENGTH = 2;
const DEBOUNCE_MS = 300;

if (searchField && searchResults) {
  let debounceTimer;
  let abortController;

  searchField.addEventListener("input", () => {
    clearTimeout(debounceTimer);
    const term = searchField.value.trim();

    if (term.length < MIN_TERM_LENGTH) {
      hideResults();
      return;
    }

    debounceTimer = setTimeout(() => runSearch(term), DEBOUNCE_MS);
  });

  searchField.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      hideResults();
    }
  });

  async function runSearch(term) {
    if (!ajax_object_another?.ajax_url) return;

    abortController?.abort();
    abortController = new AbortController();

    showMessage("Searching…");

    try {
      const res = await fetch(ajax_object_another.ajax_url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "mji_live_product_search",
          nonce: ajax_object_another.search_nonce,
          term,
        }),
        signal: abortController.signal,
      });
      const json = await res.json();

      if (json.success) {
        renderResults(json.data.products, term);
      } else {
        showMessage(json.data?.message || "Something went wrong.");
      }
    } catch (err) {
      if (err.name !== "AbortError") {
        showMessage("Something went wrong.");
      }
    }
  }

  function showMessage(text) {
    searchResults.innerHTML = "";
    const msg = document.createElement("div");
    msg.className = "search-results-message";
    msg.textContent = text;
    searchResults.appendChild(msg);
    searchResults.classList.remove("hidden");
  }

  function renderResults(products, term) {
    searchResults.innerHTML = "";

    if (!products.length) {
      showMessage(`No products found for "${term}"`);
      return;
    }

    products.forEach((product) => {
      const link = document.createElement("a");
      link.className = "search-result-item";
      link.href = product.url;

      const img = document.createElement("img");
      img.src = product.image;
      img.alt = "";
      img.loading = "lazy";
      link.appendChild(img);

      const info = document.createElement("div");
      info.className = "search-result-info";

      const name = document.createElement("span");
      name.className = "search-result-name";
      name.textContent = product.name;
      info.appendChild(name);

      if (product.priceHtml) {
        const price = document.createElement("span");
        price.className = "search-result-price";
        price.innerHTML = product.priceHtml;
        info.appendChild(price);
      }

      link.appendChild(info);
      searchResults.appendChild(link);
    });

    searchResults.classList.remove("hidden");
  }

  function hideResults() {
    searchResults.classList.add("hidden");
    searchResults.innerHTML = "";
  }

  document.addEventListener("click", (e) => {
    if (!searchResults.contains(e.target) && e.target !== searchField) {
      hideResults();
    }
  });
}
