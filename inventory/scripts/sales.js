// store customer info
let customerId, firstNameValue, lastNameValue, addressValue;
const cart = [];
let layawayTotal = 0;

const DOM = {
  // Main divs
  divs: {
    searchCustomer: document.querySelector("#search-customer"),
    customerDetails: document.querySelector("#customerDetails"),
    layawayDetails: document.querySelector("#layawayDetails"),
    addLayaway: document.querySelector("#addLayawayForm"),
    layawayReceipt: document.querySelector("#layawayReceipt"),
    searchProducts: document.querySelector("#search-products"),
    cart: document.querySelector("#cart"),
    cartItems: document.querySelector("#cart .cart-items"),
    editItemsModal: document.querySelector("#edit-item-modal"),
    saleResult: document.querySelector("#saleResult"),
  },

  buttons: {
    searchCustomer: document.querySelector("#search-btn"),
    searchProducts: document.querySelector("#search-product-btn"),
    viewProducts: document.querySelector("#viewProducts"),
    viewCart: document.querySelector("#viewCart"),
    viewLayaway: document.querySelector("#viewLayaway"),
    addLayaway: document.querySelector("#addLayaway"),
    layawayReceiptPrint: document.querySelector("#layawayPrintReceipt"),
    salesPrintReceipt: document.getElementById("salesPrintReceipt"),
  },

  forms: {
    searchCustomer: document.querySelector("form[name='search-customer']"),
    addLayaway: document.querySelector("form[name='add-layaway']"),
    searchProducts: document.querySelector("form[name='search-products']"),
    finalizeSale: document.querySelector("#cart form[name='finalize-sale']"),
  },

  inputs: {
    searchCustomer: document.querySelector("#search-customer #search"),
    searchProducts: document.querySelector("#search-products #search-products"),
    layawayTotal: document.querySelector("#layaway-total"),

    paymentMethods: {
      cash: document.querySelector("#cart #cash"),
      cheque: document.querySelector("#cart #cheque"),
      debit: document.querySelector("#cart #debit"),
      visa: document.querySelector("#cart #visa"),
      mastercard: document.querySelector("#cart #master_card"),
      amex: document.querySelector("#cart #amex"),
      discover: document.querySelector("#cart #discover"),
      travelCheque: document.querySelector("#cart #travel_cheque"),
      cup: document.querySelector("#cart #cup"),
      alipay: document.querySelector("#cart #alipay"),
      layaway: document.querySelector("#cart #layaway"),
    },
    subtotal: document.querySelector("#cart #subtotal"),
    excludeGst: document.querySelector("#cart #exclude-gst"),
    excludePst: document.querySelector("#cart #exclude-pst"),
    gst: document.querySelector("#cart #gst"),
    pst: document.querySelector("#cart #pst"),
    total: document.querySelector("#cart #total"),
  },

  customer: {
    name: document.querySelector("#customer-name"),
    address: document.querySelector("#customer-address"),
    layawaySum: document.querySelector("#layawaySum"),
  },

  layaway: {
    items: document.querySelector("#layawayItems"),
  },

  receipts: {
    content: document.getElementById("receiptContent"),
  },

  results: {
    searchCustomer: document.querySelector("#search-customer-results"),
    searchProducts: document.querySelector("#search-product-results"),
  },
};

DOM.buttons.viewCart.addEventListener("click", function (e) {
  e.preventDefault();

  DOM.divs.cart.classList.remove("hidden");
  DOM.divs.searchCustomer.classList.add("hidden");
  DOM.divs.searchProducts.classList.add("hidden");
  DOM.divs.layawayDetails.classList.add("hidden");
  DOM.divs.layawayReceipt.classList.add("hidden");
  DOM.divs.addLayaway.classList.add("hidden");

  displayCart();
});

DOM.buttons.viewProducts.addEventListener("click", function (e) {
  e.preventDefault();

  DOM.divs.searchProducts.classList.remove("hidden");
  DOM.divs.cart.classList.add("hidden");
  DOM.divs.searchCustomer.classList.add("hidden");
  DOM.divs.layawayDetails.classList.add("hidden");
  DOM.divs.layawayReceipt.classList.add("hidden");
  DOM.divs.addLayaway.classList.add("hidden");
});

// STEP 1: Search the customer
DOM.forms.searchCustomer.addEventListener("submit", function (event) {
  event.preventDefault();
  const searchValue = DOM.inputs.searchCustomer.value.trim();
  DOM.buttons.searchCustomer.setAttribute("disabled", true);
  DOM.buttons.searchCustomer.innerHTML = "Searching...";

  if (searchValue) {
    fetch(
      `${
        ajax_inventory.ajax_url
      }?action=search_customer&search_value=${encodeURIComponent(searchValue)}`,
      {
        method: "GET",
      }
    )
      .then((response) => response.json())
      .then((res) => {
        if (res.success) {
          DOM.results.searchCustomer.innerHTML = "";
          DOM.results.searchCustomer.innerHTML = res.data;
        }
        DOM.buttons.searchCustomer.removeAttribute("disabled");
        DOM.buttons.searchCustomer.innerHTML = "Search";
        setupSelectCustomerButtons();
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  } else {
    DOM.buttons.searchCustomer.removeAttribute("disabled");
    DOM.buttons.searchCustomer.innerHTML = "Search";

    DOM.results.searchCustomer.innerHTML = "";
    DOM.results.searchCustomer.innerHTML = "Please enter a search term.";
  }
});

// STEP 2: SELECT the customer
function setupSelectCustomerButtons() {
  const selectCustomerButtons = document.querySelectorAll(".select-customer");
  selectCustomerButtons?.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();

      // Grabbing the selected customer's details
      const buttonParent = button.parentNode.parentNode;
      firstNameValue = buttonParent.querySelector("#firstName").innerText;
      lastNameValue = buttonParent.querySelector("#lastName").innerText;
      addressValue = buttonParent.querySelector("#address").innerText;

      customerId = button.dataset.customerid;
      DOM.divs.searchCustomer.classList.add("hidden");
      DOM.divs.searchProducts.classList.remove("hidden");
      DOM.divs.customerDetails.classList.remove("hidden");

      DOM.customer.name.textContent = `${firstNameValue} ${lastNameValue}`;
      DOM.customer.address.textContent = addressValue;

      // Fetching the layaway sum for the selected customer
      fetch(
        `${ajax_inventory.ajax_url}?action=getLayawaySum&customer_id=${customerId}`,
        {
          method: "GET",
        }
      )
        .then((response) => response.json())
        .then((res) => {
          if (res.data > 0) {
            layawayTotal = parseFloat(res.data).toFixed(2);
            DOM.customer.layawaySum.innerHTML = `Layaway Total: <span>${parseFloat(
              res.data
            ).toFixed(2)} CAD</span>`;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
        });
    });
  });
}

// STEP 3: Layaway of the selected customer
DOM.buttons.viewLayaway?.addEventListener("click", function (e) {
  e.preventDefault();
  DOM.divs.layawayDetails.classList.remove("hidden");
  DOM.divs.searchProducts.classList.add("hidden");
  DOM.divs.cart.classList.add("hidden");
  DOM.divs.searchCustomer.classList.add("hidden");
  DOM.divs.layawayReceipt.classList.add("hidden");
  DOM.divs.addLayaway.classList.add("hidden");

  if (!customerId) {
    DOM.layaway.items.innerHTML = "Please select a customer first.";
    return;
  }

  fetch(
    `${ajax_inventory.ajax_url}?action=getLayaway&customer_id=${customerId}`,
    {
      method: "GET",
    }
  )
    .then((response) => response.json())
    .then((res) => {
      if (res.success) {
        DOM.layaway.items.innerHTML = "";
        res.data.forEach((item) => {
          const layawayItem = document.createElement("tr");
          layawayItem.classList.add("layaway-item");
          layawayItem.innerHTML = `
            <td>${item.payment_date.split(" ")[0]}</p>
            <td>${item.reference_num}</p>
            <td>${item.transaction_type}</p>
            <td>${item.method}</p>
            <td>${item.amount}</p>
          `;
          DOM.layaway.items.appendChild(layawayItem);
        });
        DOM.inputs.layawayTotal.innerHTML = Number(layawayTotal).toFixed(2);
      } else {
        DOM.layaway.items.innerHTML =
          "No layaway items found for this customer.";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
});

// STEP 3.1: Add Layaway
DOM.buttons.addLayaway?.addEventListener("click", function (e) {
  e.preventDefault();
  DOM.divs.addLayaway.classList.remove("hidden");
  DOM.divs.layawayDetails.classList.add("hidden");
});

const { addLayaway } = DOM.forms;
// STEP 3.2: Submit Layaway Form
addLayaway?.addEventListener("submit", function (e) {
  e.preventDefault();

  const cash = addLayaway.querySelector("#cash").value;
  const cheque = addLayaway.querySelector("#cheque").value;
  const debit = addLayaway.querySelector("#debit").value;
  const visa = addLayaway.querySelector("#visa").value;
  const master_card = addLayaway.querySelector("#master_card").value;
  const amex = addLayaway.querySelector("#amex").value;
  const discover = addLayaway.querySelector("#discover").value;
  const travel_cheque = addLayaway.querySelector("#travel_cheque").value;
  const cup = addLayaway.querySelector("#cup").value;
  const alipay = addLayaway.querySelector("#alipay").value;
  const layawayReference = addLayaway.querySelector("#layaway-reference").value;
  const salesperson = addLayaway.querySelector("#salesperson").value;
  const layawayDate = addLayaway.querySelector("#layaway-date").value;

  const receiptCustomerName = DOM.divs.layawayReceipt.querySelector(
    "#receiptCustomerName"
  );
  const receiptCustomerAddress = DOM.divs.layawayReceipt.querySelector(
    "#receiptCustomerAddress"
  );
  const layawayTotalDiv =
    DOM.divs.layawayReceipt.querySelector("#layawayTotal");
  const paymentAmount = DOM.divs.layawayReceipt.querySelector("#paymentAmount");
  const paymentMethod = DOM.divs.layawayReceipt.querySelector("#paymentMode");
  const receiptDate = DOM.divs.layawayReceipt.querySelector("#receiptDate");
  const salesmanName = DOM.divs.layawayReceipt.querySelector("#salesmanName");

  if (
    cash === "" &&
    cheque === "" &&
    debit === "" &&
    visa === "" &&
    master_card === "" &&
    amex === "" &&
    discover === "" &&
    travel_cheque === "" &&
    cup === "" &&
    alipay === ""
  ) {
    alert("Please enter at least one payment method.");
    return;
  }

  if (!layawayReference || !salesperson || !layawayDate) {
    alert("Please fill in reference and salesperosn.");
    return;
  }

  const formData = new FormData(addLayaway);
  formData.append("action", "addLayaway");
  formData.append("customer_id", customerId);

  fetch(`${ajax_inventory.ajax_url}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        DOM.divs.layawayReceipt.classList.remove("hidden");
        DOM.divs.addLayaway.classList.add("hidden");

        receiptCustomerName.innerHTML = `${firstNameValue} ${lastNameValue}`;
        receiptCustomerAddress.innerHTML = addressValue;

        result.data.payments.forEach((el) => {
          layawayTotal = parseFloat(layawayTotal) + parseFloat(el.amount);
        });

        paymentAmount.innerHTML = result.data.payments
          .map((payment) => `${parseFloat(payment.amount).toFixed(2)} CAD`)
          .join("<br>");

        paymentMethod.innerHTML = result.data.payments
          .map((payment) => `${payment.method}`)
          .join("<br>");

        receiptDate.innerHTML = result.data.payment_date;
        salesmanName.innerHTML = result.data.salesperson;
        addLayaway.reset();
      } else {
        alert("Failed to process payment: " + result.data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred.");
    });
});

// STEP 3.3: Print Layaway Receipt
DOM.buttons.layawayReceiptPrint?.addEventListener("click", function (e) {
  e.preventDefault();
  // Create a new window for printing
  const printWindow = window.open("", "_blank");
  printWindow.document.write(`
        <html>
            <head>
              <title>Layaway Receipt</title>
              <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 20px;
                        margin: auto;
                    }
                    h2, h3 {
                        margin: 0;
                        padding: 0;
                    }
                    .layaway-receipt {
                        border: 1px solid #ccc;
                        background: white;
                    }
                    #printReceipt {
                        display: none;
                    }
                    footer {
                        margin-top: 20px;
                        font-style: italic;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
              ${DOM.divs.layawayReceipt.outerHTML}
            </body>
        </html>
    `);
  printWindow.focus();
  printWindow.print();
  printWindow.close();
});

// STEP 3: Search the products
const { searchProducts } = DOM.forms;

// Searching the products
searchProducts.addEventListener("submit", function (event) {
  event.preventDefault();
  const searchValue = DOM.inputs.searchProducts.value.trim();
  DOM.buttons.searchProducts.setAttribute("disabled", true);
  DOM.buttons.searchProducts.innerHTML = "Searching...";
  if (searchValue) {
    fetch(
      `${
        ajax_inventory.ajax_url
      }?action=searchProducts&search_product=${encodeURIComponent(
        searchValue
      )}`,
      {
        method: "GET",
      }
    )
      .then((response) => response.json())
      .then((res) => {
        DOM.results.searchProducts.innerHTML = "";
        if (res.success) {
          const {
            image_url,
            sku,
            price,
            title,
            unit_id,
            variation_detail,
            product_id,
            product_variant_id,
          } = res.data;
          DOM.results.searchProducts.innerHTML = `
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
                <button class="add-to-cart">Add to Cart</button>
              </div>
            </div>
          `;

          const addToCartButton =
            DOM.results.searchProducts.querySelector(".add-to-cart");
          addToCartButton.addEventListener("click", function () {
            const product = {
              unit_id,
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

            if (cart.find((item) => item.unit_id === unit_id)) {
              alert("This product is already in the cart.");
              return;
            }

            cart.push(product);
            displayCart();
            DOM.results.searchProducts.innerHTML = "";
            DOM.inputs.searchProducts.value = "";
            DOM.divs.searchProducts.classList.add("hidden");
          });
        } else {
          DOM.results.searchProducts.innerHTML = `No products found for "${searchValue}".`;
        }
        DOM.buttons.searchProducts.removeAttribute("disabled");
        DOM.buttons.searchProducts.innerHTML = "Search";
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  } else {
    DOM.results.searchProducts.innerHTML = "";
    DOM.results.searchProducts.innerHTML = "Please enter a search term.";
    DOM.buttons.searchProducts.removeAttribute("disabled");
    DOM.buttons.searchProducts.innerHTML = "Search";
  }
});

// FINAL SALES/CART SECTION
DOM.divs.cartItems.addEventListener("click", handleCartClick);

function getTotals() {
  const subtotal = cart.reduce((sum, item) => {
    return sum + (parseFloat(item.price_after_discount || item.price) || 0);
  }, 0);

  const gstRate = DOM.inputs.excludeGst?.checked ? 0 : 0.05;
  const pstRate = DOM.inputs.excludePst?.checked ? 0 : 0.08;

  const gst = subtotal * gstRate;
  const pst = subtotal * pstRate;
  const total = subtotal + gst + pst;

  return {
    subtotal: parseFloat(subtotal.toFixed(2)),
    gst: parseFloat(gst.toFixed(2)),
    pst: parseFloat(pst.toFixed(2)),
    total: parseFloat(total.toFixed(2)),
  };
}

function calculateTotal(checkbox = true) {
  const { subtotal, gst, pst, total } = getTotals();

  DOM.inputs.subtotal.value = subtotal.toFixed(2);
  DOM.inputs.gst.value = gst.toFixed(2);
  DOM.inputs.pst.value = pst.toFixed(2);
  DOM.inputs.total.value = total.toFixed(2);

  if (layawayTotal > 0 && !checkbox) {
    const layawayInput = DOM.inputs.paymentMethods.layaway;
    if (layawayInput) layawayInput.value = layawayTotal;
  }
}

function displayCart() {
  calculateTotal(false);
  let cartHTML;
  if (cart.length > 0) {
    cartHTML = cart
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
                    <span>Discounted Price: ${item.price_after_discount}</span>
                    <button type="button">Edit</button>
                    <button type="button">Remove from cart</button>
                  </div>
                </div>
              `;
      })
      .join("");
  } else {
    cartHTML =
      "<p>No items in the cart!!. Please add by searching the prooducts.</p>";
  }

  DOM.divs.cartItems.innerHTML = cartHTML;
  DOM.inputs.paymentMethods.layaway.max = layawayTotal;
  DOM.divs.cart.classList.remove("hidden");
}

function handleCartClick(e) {
  if (e.target.tagName !== "BUTTON") return;

  const button = e.target;
  const productItem = button.closest("div");
  const unitId = productItem.dataset.unitid;

  if (button.textContent === "Remove from cart") {
    removeFromCart(unitId);
  } else if (button.textContent === "Edit") {
    openEditModal(unitId);
  }
}

function removeFromCart(unitId) {
  const index = cart.findIndex((item) => item.unit_id == unitId);
  if (index > -1) {
    cart.splice(index, 1);
    displayCart();
  }
}

function openEditModal(unitId) {
  // find the item
  const item = cart.find((i) => i.unit_id == unitId);

  if (!item) return;

  const { editItemsModal } = DOM.divs;
  editItemsModal.classList.remove("hidden");

  // grab the modal elements
  const titleEl = editItemsModal.querySelector("#edit-item-title");
  const skuEl = editItemsModal.querySelector("#edit-item-sku");
  const priceEl = editItemsModal.querySelector("#edit-item-price");
  const discountAmtEl = editItemsModal.querySelector("#edit-discount-amt");
  const discountPctEl = editItemsModal.querySelector("#edit-discount-pct");
  const priceAfterDiscountEl = editItemsModal.querySelector(
    "#edit-price-after-discount"
  );
  const saveBtn = editItemsModal.querySelector("#save-edit");
  const cancelBtn = editItemsModal.querySelector("#cancel-edit");
  const basePrice = parseFloat(item.price);

  // Populate modal fields
  titleEl.textContent = item.title;
  skuEl.textContent = item.sku;
  priceEl.textContent = `${item.price} CAD`;

  discountAmtEl.value = item.discount_amount || 0;
  discountPctEl.value = item.discount_percent || 0;
  priceAfterDiscountEl.value = item.price_after_discount || basePrice;

  // Define event handlers
  function onDiscountAmtInput() {
    let amt = parseFloat(discountAmtEl.value) || 0;

    if (amt > basePrice) amt = basePrice;

    discountPctEl.value = ((amt / basePrice) * 100).toFixed(2);
    priceAfterDiscountEl.value = (basePrice - amt).toFixed(2);
  }
  function onDiscountPctInput() {
    let pct = parseFloat(discountPctEl.value) || 0;

    if (pct > 100) pct = 100;

    const amt = (pct / 100) * basePrice;
    discountAmtEl.value = amt.toFixed(2);
    priceAfterDiscountEl.value = (basePrice - amt).toFixed(2);
  }

  function onPriceAfterDiscountInput() {
    let discounted = parseFloat(priceAfterDiscountEl.value) || 0;

    if (discounted > basePrice) discounted = basePrice;

    const amt = basePrice - discounted;
    discountAmtEl.value = amt.toFixed(2);
    discountPctEl.value = ((amt / basePrice) * 100).toFixed(2);
  }

  function removeModalListeners() {
    discountAmtEl.removeEventListener("input", onDiscountAmtInput);
    discountPctEl.removeEventListener("input", onDiscountPctInput);
    priceAfterDiscountEl.removeEventListener(
      "input",
      onPriceAfterDiscountInput
    );
    cancelBtn.removeEventListener("click", onCancelClick);
    saveBtn.removeEventListener("click", onSaveClick);
  }

  function onCancelClick() {
    editItemsModal.classList.add("hidden");
    removeModalListeners();
  }

  function onSaveClick() {
    const updatedAmt = parseFloat(discountAmtEl.value) || 0;
    const updatedPct = parseFloat(discountPctEl.value) || 0;
    const updatedPrice = parseFloat(priceAfterDiscountEl.value) || basePrice;
    item.discount_amount = updatedAmt.toFixed(2);
    item.discount_percent = updatedPct.toFixed(2);
    item.price_after_discount = updatedPrice.toFixed(2);

    displayCart();
    editItemsModal.classList.add("hidden");
    removeModalListeners();
  }
  // Attach modal listeners
  discountAmtEl.addEventListener("input", onDiscountAmtInput);
  discountPctEl.addEventListener("input", onDiscountPctInput);
  priceAfterDiscountEl.addEventListener("input", onPriceAfterDiscountInput);
  cancelBtn.addEventListener("click", onCancelClick);
  saveBtn.addEventListener("click", onSaveClick);
}

function validateAndSubmitSale() {
  const { subtotal, gst, pst, total } = getTotals();

  const payments = DOM.inputs.paymentMethods;
  let totalPaid = 0;

  for (const method in payments) {
    if (
      method === "subtotal" ||
      method === "gst" ||
      method === "pst" ||
      method === "total"
    )
      continue;
    totalPaid += parseFloat(payments[method].value) || 0;
  }
  totalPaid = parseFloat(totalPaid.toFixed(2));

  if (totalPaid !== total) {
    alert(
      `Payment does not match total!\nExpected: $${total.toFixed(
        2
      )}\nReceived: $${totalPaid.toFixed(2)}`
    );
    return false;
  }
  const reference = DOM.forms.finalizeSale
    .querySelector("#reference")
    .value.trim();
  const salesperson = DOM.forms.finalizeSale
    .querySelector("#salesperson")
    .value.trim();

  if (!reference) {
    alert("Reference number is required.");
    return false;
  }
  if (!salesperson) {
    alert("Salesperson name is required.");
    return false;
  }

  return true;
}

DOM.inputs.excludeGst.addEventListener("change", calculateTotal);
DOM.inputs.excludePst.addEventListener("change", calculateTotal);

DOM.forms.finalizeSale.addEventListener("submit", function (e) {
  e.preventDefault();

  if (cart.length === 0) {
    alert(
      "Cart is empty. Please add items to the cart before finalizing the sale."
    );
    return;
  }
  const validatedForm = validateAndSubmitSale();

  if (!validatedForm) return;

  const formData = new FormData(DOM.forms.finalizeSale);

  formData.append("action", "finalizeSale");
  formData.append("customer_id", customerId);
  formData.append("items", JSON.stringify(cart));

  fetch(`${ajax_inventory.ajax_url}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        console.log(result.data);

        DOM.divs.saleResult.classList.remove("hidden");
        DOM.divs.cart.classList.add("hidden");
        DOM.divs.customerDetails.classList.add("hidden");

        const { data } = result;

        const itemRows = data.items
          .map((item) => {
            const attributePairs =
              item.attributes?.map((attr) => {
                // Get the first (and only) key-value pair in each object
                const key = Object.keys(attr)[0];
                const value = attr[key];
                return `${key}: ${value}`;
              }) || [];

            const attributesString =
              attributePairs.length > 0 ? `${attributePairs.join("<br>")}` : "";
            return `
                <tr>
                  <td>${item.title}${
              item.variation_detail ? ` (${item.variation_detail})` : ""
            } <br>${attributesString}</td>
                  <td>$${item.price_after_discount}</td>
                </tr>
              `;
          })
          .join("");

        const paymentLines = data.payments
          .map((payment) => {
            return `${payment.method}: $${parseFloat(payment.amount).toFixed(
              2
            )}`;
          })
          .join(", ");

        DOM.receipts.content.innerHTML = `
            <header>
              <div>
                <h2>Montecristo Jewellers</h2>
                <p><strong>Receipt</strong></p>
              </div>
              <div>
                <address>
                  <p>${firstNameValue} ${lastNameValue}</p>
                  <p>${addressValue.split(",").join("<br/>")}</p>
                  <p>${data.customer?.phone ?? ""}</p>
                  <p>${data.customer?.email ?? ""}</p>
                </address>
              </div>
              <div>
                  <p>Reference # ${data.reference_num}</p>
                  <p>Sold on <time datetime="${data.date}">${
          data.date
        }</time></p>
                  <p>Served by ${data.salesperson_name}</p>
              </div>
            </header>

            <main>
              <table>
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Price</th>
                  </tr>
                </thead>
                <tbody>
                  ${itemRows}
                </tbody>
                <tfoot>
                  <tr>
                    <td>Paid by ${paymentLines} <br /> Thank you for shopping at Montecristo Jewellers</td>
                    <td>
                      <strong>Subtotal: $${data.totals.subtotal.toFixed(
                        2
                      )}</strong><br />
                      <strong>GST (5%): $${data.totals.gst.toFixed(
                        2
                      )} </strong><br />
                      <strong>PST (8%): $${data.totals.pst.toFixed(
                        2
                      )} </strong><br />
                      <strong>Total: $${data.totals.total.toFixed(2)}
                    </strong></td>
                  </tr>
                  </tfoot>
              </table>
            </main>
          `;
      } else {
        alert("Failed to complete sale: " + result.data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while processing the sale.");
    });
});

DOM.buttons.salesPrintReceipt?.addEventListener("click", function (e) {
  e.preventDefault();
  const printWindow = window.open("", "_blank");
  printWindow.document.write(`
        <html>
            <head>
              <title>Sales Receipt</title>
              <style>
                  .receipt-content {
                    max-width: 700px;

                    p {
                      margin: 0;
                    }

                    header {
                      display: grid;
                      grid-template-columns: 1fr 1fr;

                      div:first-child {
                        text-align: center;
                        grid-column: 1/-1;
                      }

                      div:last-child {
                        text-align: end;
                      }
                    }

                    main {
                      table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                      }

                      th,
                      td {
                        border: 1px solid #000;
                      }

                      tr {
                        td:first-child {
                          border-right: none;
                        }

                        td:last-child {
                          border-left: none;
                        }
                      }
                    }
                  }
              </style>
            </head>
            <body>
              ${DOM.receipts.content.outerHTML}
            </body>
        </html>
    `);
  printWindow.focus();
  printWindow.print();
  printWindow.close();
});
