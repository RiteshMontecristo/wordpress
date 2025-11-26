import { showSelection } from "../sales.js";
import { AppState } from "./state.js";

export const ProductSelector = {
  init() {
    this.view = document.querySelector("#viewProducts");
    this.searchDiv = document.querySelector("#search-products");
    this.searchForm = document.querySelector("form[name='search-products']");
    this.searchInput = document.querySelector(
      "#search-products #search-products"
    );
    this.searchBtn = document.querySelector("#search-product-btn");
    this.searchResult = document.querySelector("#search-product-results");

    this.bindEvents();
    document.addEventListener("customer:selected", () =>
      showSelection(this.searchDiv)
    );
  },

  bindEvents() {
    this.view.addEventListener("click", (e) => {
      e.preventDefault();
      showSelection(this.searchDiv);
    });

    this.searchForm.addEventListener("submit", (event) => {
      event.preventDefault();
      const searchValue = this.searchInput.value.trim();
      this.searchBtn.setAttribute("disabled", true);
      this.searchBtn.innerHTML = "Searching...";
      const url = `${
        ajax_inventory.ajax_url
      }?action=searchProducts&search_product=${encodeURIComponent(
        searchValue
      )}`;
      if (searchValue) {
        fetch(url, {
          method: "GET",
        })
          .then((response) => response.json())
          .then((res) => {
            this.searchResult.innerHTML = "";
            if (res.success) {
              const {
                image_url,
                sku,
                status,
                location_id,
                price,
                title,
                unit_id,
                variation_detail,
                product_id,
                product_variant_id,
              } = res.data;

              let ctaBtn =
                location_id === AppState.location.id
                  ? `<button class="add-to-cart">Add to Cart</button>`
                  : `<span>Item in different store</span>`;
              ctaBtn =
                status == "in_stock"
                  ? ctaBtn
                  : "<span>Item not in stock</span>";

              this.searchResult.innerHTML = `
            <div class="product-item">
              <img src="${image_url}" alt="${title}" />
              <div>
                <strong>${title}</strong><br />
                ${
                  variation_detail &&
                  `<span>Variation: ${variation_detail}</span><br />`
                }
                <span>SKU: ${sku}</span><br />
                <span>Price: ${price} CAD</span><br />
                ${ctaBtn}
              </div>
            </div>
          `;

              const addToCartButton =
                this.searchResult.querySelector(".add-to-cart");

              const product = {
                unit_id,
                location_id,
                product_id,
                product_variant_id,
                title,
                price,
                image_url,
                sku,
                variation_detail,
                discount_amount: 0,
                discount_percent: 0,
                price_after_discount: price,
              };
              addToCartButton?.addEventListener("click", () =>
                this.addToCart(product)
              );
            } else {
              this.searchResult.textContent = `No products found for "${searchValue}".`;
            }
            this.searchBtn.removeAttribute("disabled");
            this.searchBtn.textContent = "Search";
            this.searchInput.value = "";
          })
          .catch((error) => {
            console.error("Error:", error);
          });
      } else {
        this.searchResult.textContent = "";
        this.searchResult.textContent = "Please enter a search term.";
        this.searchBtn.removeAttribute("disabled");
        this.searchBtn.textContent = "Search";
      }
    });
  },

  addToCart(product) {
    if (AppState.cart.find((item) => item.unit_id === unit_id)) {
      alert("This product is already in the cart.");
      return;
    }

    AppState.cart.push(product);

    // Create a dispatchevent
    document.dispatchEvent(new CustomEvent("displayCart"));
    this.searchResult.innerHTML = "";
    this.searchInput.value = "";
  },
};
