import { StoreSelector } from "./sales-module/store.js";
import { CustomerSelector } from "./sales-module/customer.js";
import { LayawaySelector } from "./sales-module/layaway.js";
import { ServiceSelector } from "./sales-module/service.js";
import { ProductSelector } from "./sales-module/product.js";
import { CartSelector } from "./sales-module/cart.js";
import { CheckoutSelector } from "./sales-module/checkout.js";

StoreSelector.init();
CustomerSelector.init();
LayawaySelector.init();
ServiceSelector.init();
ProductSelector.init();
CartSelector.init();
CheckoutSelector.init();

document.addEventListener("layaway:added", () => {
  CustomerSelector.updateLayaway();
});

document.addEventListener("displayCart", () => {
  CartSelector.displayCart();
});

document.addEventListener("call:calculateTotal", () => {
  CheckoutSelector.calculateTotal();
});

const DOM = {
  divs: {
    searchCustomer: document.querySelector("#search-customer"),
    layawayDetails: document.querySelector("#layawayDetails"),
    addLayaway: document.querySelector("#addLayawayForm"),
    layawayReceipt: document.querySelector("#layawayReceipt"),
    searchProducts: document.querySelector("#search-products"),
    cart: document.querySelector("#cart"),
  },
};

export function showSelection(selection) {
  Object.values(DOM.divs).forEach((div) => div.classList.add("hidden"));
  selection.classList.remove("hidden");
}
