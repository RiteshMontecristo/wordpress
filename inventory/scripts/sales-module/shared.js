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
