import { formatCurrency, formatLabel } from "../index.js";
import { showSelection } from "../sales.js";
import { AppState } from "./state.js";

export const CartSelector = {
  init() {
    this.viewCart = document.querySelector("#viewCart");
    this.cart = document.querySelector("#cart");
    this.cartItems = document.querySelector("#cart .cart-items");
    this.editItems = document.querySelector("#edit-item-modal");

    this.bindEvents();
  },

  bindEvents() {
    this.viewCart.addEventListener("click", (e) => {
      e.preventDefault();
      showSelection(this.cart);

      this.displayCart();
    });

    this.cartItems.addEventListener("click", this.handleCartClick.bind(this));
  },

  displayCart() {
    document.dispatchEvent(new CustomEvent("call:calculateTotal"));
    showSelection(this.cart);
    let cartHTML = "";

    if (AppState.cart.length > 0) {
      cartHTML += AppState.cart
        .map((item) => {
          return `
                <div class="product-item">
                  <img src="${item.image_url}" alt="${item.title}" />
                  <div data-unitId="${item.unit_id}">
                    <strong>${item.title}</strong><br />
                    ${
                      item.variation_detail &&
                      `<span>Variation: ${item.variation_detail}</span><br />`
                    }
                    <span>SKU: ${item.sku}</span><br />
                    <span>Price: ${item.price} CAD</span><br />
                    <span>Discount: ${item.discount_amount}</span><br />
                    <span>Discounted Price: ${
                      item.price_after_discount
                    }</span><br />
                    <button type="button">Edit</button>
                    <button type="button">Remove from cart</button>
                  </div>
                </div>
              `;
        })
        .join("");
    }

    if (AppState.services.length > 0) {
      cartHTML += AppState.services
        .map((service, index) => {
          return `
          <div class="service-item">
            <strong>${formatLabel(service.category)}</strong> </br>
            ${
              service.description
                ? `<span>Description: ${service.description}</span><br />`
                : ``
            }
            <span>Cost Price: ${formatCurrency(
              service.costPrice
            )} CAD</span><br />
            <span>Retail Price: ${formatCurrency(
              service.retailPrice
            )} CAD</span><br />
            ${
              service.reference
                ? `<span>Reference: ${service.reference}</span><br />`
                : ``
            }
            <button id="${index}" type="button">Remove from cart</button>
            </div>
      `;
        })
        .join("");
    }

    if (AppState.cart.length == 0 && AppState.services.length == 0) {
      cartHTML =
        "<p>No items in the cart!!. Please add by searching the prooducts.</p>";
    }

    this.cartItems.innerHTML = cartHTML;
    // TODO: DISPATCH EVEN AND USE THAT EVENT IN CHECKOUT TO UPDATE LAYAWAY
    document.dispatchEvent(new CustomEvent("checkout:updateLayaway"));
    // DOM.inputs.paymentMethods.layaway.max = state.layawayTotal;
  },

  handleCartClick(e) {
    if (e.target.tagName !== "BUTTON") return;

    const button = e.target;
    const productItem = button.closest(".product-item div[data-unitid]");
    const buttonId = button.id;

    if (buttonId) {
      this.removeFromService(buttonId);
    } else {
      const unitId = productItem.dataset.unitid;
      if (button.textContent === "Remove from cart") {
        this.removeFromCart(unitId);
      } else if (button.textContent === "Edit") {
        this.openEditModal(unitId);
      }
    }
  },

  removeFromService(id) {
    AppState.services.splice(id, 1);
    this.displayCart();
  },

  removeFromCart(unitId) {
    const index = AppState.cart.findIndex((item) => item.unit_id == unitId);
    if (index > -1) {
      AppState.cart.splice(index, 1);
      displayCart();
    }
  },

  openEditModal(unitId) {
    // find the item
    const item = AppState.cart.find((i) => i.unit_id == unitId);

    if (!item) return;

    this.editItems.classList.remove("hidden");

    // grab the modal elements
    const titleEl = this.editItems.querySelector("#edit-item-title");
    const skuEl = this.editItems.querySelector("#edit-item-sku");
    const priceEl = this.editItems.querySelector("#edit-item-price");
    const discountAmtEl = this.editItems.querySelector("#edit-discount-amt");
    const discountPctEl = this.editItems.querySelector("#edit-discount-pct");
    const priceAfterDiscountEl = this.editItems.querySelector(
      "#edit-price-after-discount"
    );
    const saveBtn = this.editItems.querySelector("#save-edit");
    const cancelBtn = this.editItems.querySelector("#cancel-edit");
    const basePrice = Number(item.price);

    // Populate modal fields
    titleEl.textContent = item.title;
    skuEl.textContent = item.sku;
    priceEl.textContent = `${item.price} CAD`;

    discountAmtEl.value = item.discount_amount || 0;
    discountPctEl.value = item.discount_percent || 0;
    priceAfterDiscountEl.value = item.price_after_discount || basePrice;

    // Define event handlers
    const onDiscountAmtInput = () => {
      let amt = Number(discountAmtEl.value) || 0;

      if (amt > basePrice) amt = basePrice;

      discountPctEl.value = ((amt / basePrice) * 100).toFixed(2);
      priceAfterDiscountEl.value = (basePrice - amt).toFixed(2);
    };

    const onDiscountPctInput = () => {
      let pct = Number(discountPctEl.value) || 0;

      if (pct > 100) pct = 100;

      const amt = (pct / 100) * basePrice;
      discountAmtEl.value = amt.toFixed(2);
      priceAfterDiscountEl.value = (basePrice - amt).toFixed(2);
    };

    const onPriceAfterDiscountInput = () => {
      let discounted = Number(priceAfterDiscountEl.value) || 0;

      if (discounted > basePrice) discounted = basePrice;

      const amt = basePrice - discounted;
      discountAmtEl.value = amt.toFixed(2);
      discountPctEl.value = ((amt / basePrice) * 100).toFixed(2);
    };

    const removeModalListeners = () => {
      discountAmtEl.removeEventListener("input", onDiscountAmtInput);
      discountPctEl.removeEventListener("input", onDiscountPctInput);
      priceAfterDiscountEl.removeEventListener(
        "input",
        onPriceAfterDiscountInput
      );
      cancelBtn.removeEventListener("click", onCancelClick);
      saveBtn.removeEventListener("click", onSaveClick);
    };

    const onCancelClick = () => {
      this.editItems.classList.add("hidden");
      removeModalListeners();
    };

    const onSaveClick = () => {
      const updatedAmt = Number(discountAmtEl.value) || 0;
      const updatedPct = Number(discountPctEl.value) || 0;
      const updatedPrice = Number(priceAfterDiscountEl.value) || basePrice;
      item.discount_amount = updatedAmt.toFixed(2);
      item.discount_percent = updatedPct.toFixed(2);
      item.price_after_discount = updatedPrice.toFixed(2);

      this.displayCart();
      this.editItems.classList.add("hidden");
      removeModalListeners();
    };

    // Attach modal listeners
    discountAmtEl.addEventListener("input", onDiscountAmtInput);
    discountPctEl.addEventListener("input", onDiscountPctInput);
    priceAfterDiscountEl.addEventListener("input", onPriceAfterDiscountInput);
    cancelBtn.addEventListener("click", onCancelClick);
    saveBtn.addEventListener("click", onSaveClick);
  },
};
