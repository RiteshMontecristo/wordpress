// store customer info
let customerId, firstNameValue, lastNameValue, addressValue;
const cart = [];
let layawayTotal = 0;

// Main divs
const searchCustomerDiv = document.querySelector("#search-customer");
const customerDetails = document.querySelector("#customerDetails");
const layawayDetails = document.querySelector("#layawayDetails");
const addLayawayDiv = document.querySelector("#addLayawayForm");
const layawayReceipt = document.querySelector("#layawayReceipt");
const searchProductsDiv = document.querySelector("#search-products");
const cartDiv = document.querySelector("#cart");
const cartItemsDiv = cartDiv?.querySelector(".cart-items");
const editItemsModalDiv = document.querySelector("#edit-item-modal");
const saleResult = document.getElementById("saleResult");

const viewProducts = document.querySelector("#viewProducts");
const viewCart = document.querySelector("#viewCart");
const viewLayaway = document.querySelector("#viewLayaway");

// STEP 1: Search the customer by name or email
const searchCustomerForm = document.querySelector(
  "form[name='search-customer']"
);
const searchCustomerInput = searchCustomerForm.querySelector("#search");
const searchCustomerButton = searchCustomerForm.querySelector("#search-btn");
const searchCustomerResult = document.querySelector("#search-customer-results");

// Customer Info
const customerName = document.querySelector("#customer-name");
const customerAddres = document.querySelector("#customer-address");
const layawaySumDiv = document.querySelector("#layawaySum");

// Layaway Selection
const layawayItems = document.querySelector("#layawayItems");
const layawayTotalEl = document.querySelector("#layaway-total");
const addLayawayBtn = layawayDetails?.querySelector("#addLayaway");
const addLayawayform = addLayawayDiv?.querySelector("form[name='add-layaway']");
const layawayRecieptPrint = layawayReceipt?.querySelector(
  "#layawayPrintReceipt"
);

// Sales Receipt
const salesPrintReceipt = document.getElementById("salesPrintReceipt");
const receiptContent = document.getElementById("receiptContent");

viewCart.addEventListener("click", function (e) {
  e.preventDefault();

  cartDiv.classList.remove("hidden");
  searchCustomerDiv.classList.add("hidden");
  searchProductsDiv.classList.add("hidden");
  layawayDetails.classList.add("hidden");
  layawayReceipt.classList.add("hidden");
  addLayawayDiv.classList.add("hidden");

  displayCart();
});

viewProducts.addEventListener("click", function (e) {
  e.preventDefault();

  searchProductsDiv.classList.remove("hidden");
  cartDiv.classList.add("hidden");
  searchCustomerDiv.classList.add("hidden");
  layawayDetails.classList.add("hidden");
  layawayReceipt.classList.add("hidden");
  addLayawayDiv.classList.add("hidden");
});

// STEP 1: Search the customer
searchCustomerForm.addEventListener("submit", function (event) {
  event.preventDefault();
  const searchValue = searchCustomerInput.value.trim();
  searchCustomerButton.setAttribute("disabled", true);
  searchCustomerButton.innerHTML = "Searching...";

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
          searchCustomerResult.innerHTML = "";
          searchCustomerResult.innerHTML = res.data;
        }
        searchCustomerButton.removeAttribute("disabled");
        searchCustomerButton.innerHTML = "Search";
        setupSelectCustomerButtons();
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  } else {
    searchCustomerButton.removeAttribute("disabled");
    searchCustomerButton.innerHTML = "Search";

    searchCustomerResult.innerHTML = "";
    searchCustomerResult.innerHTML = "Please enter a search term.";
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
      searchCustomerDiv.classList.add("hidden");
      searchProductsDiv.classList.remove("hidden");
      customerDetails.classList.remove("hidden");

      customerName.textContent = `${firstNameValue} ${lastNameValue}`;
      customerAddres.textContent = addressValue;

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
            layawaySumDiv.innerHTML = `Layaway Total: <span>${parseFloat(
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
viewLayaway?.addEventListener("click", function (e) {
  e.preventDefault();
  layawayDetails.classList.remove("hidden");
  searchProductsDiv.classList.add("hidden");
  cartDiv.classList.add("hidden");
  searchCustomerDiv.classList.add("hidden");
  layawayReceipt.classList.add("hidden");
  addLayawayDiv.classList.add("hidden");

  if (!customerId) {
    layawayItems.innerHTML = "Please select a customer first.";
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
        layawayItems.innerHTML = "";
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
          layawayItems.appendChild(layawayItem);
        });
        layawayTotalEl.innerHTML = Number(layawayTotal).toFixed(2);
      } else {
        layawayItems.innerHTML = "No layaway items found for this customer.";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
});

// STEP 3.1: Add Layaway
addLayawayBtn?.addEventListener("click", function (e) {
  e.preventDefault();
  addLayawayDiv.classList.remove("hidden");
  layawayDetails.classList.add("hidden");
});

// STEP 3.2: Submit Layaway Form
addLayawayform?.addEventListener("submit", function (e) {
  e.preventDefault();

  const cash = addLayawayform.querySelector("#cash").value;
  const cheque = addLayawayform.querySelector("#cheque").value;
  const debit = addLayawayform.querySelector("#debit").value;
  const visa = addLayawayform.querySelector("#visa").value;
  const master_card = addLayawayform.querySelector("#master_card").value;
  const amex = addLayawayform.querySelector("#amex").value;
  const discover = addLayawayform.querySelector("#discover").value;
  const travel_cheque = addLayawayform.querySelector("#travel_cheque").value;
  const cup = addLayawayform.querySelector("#cup").value;
  const alipay = addLayawayform.querySelector("#alipay").value;
  const layawayReference =
    addLayawayform.querySelector("#layaway-reference").value;
  const salesperson = addLayawayform.querySelector("#salesperson").value;
  const layawayDate = addLayawayform.querySelector("#layaway-date").value;

  const receiptCustomerName = layawayReceipt.querySelector(
    "#receiptCustomerName"
  );
  const receiptCustomerAddress = layawayReceipt.querySelector(
    "#receiptCustomerAddress"
  );
  const layawayTotalDiv = layawayReceipt.querySelector("#layawayTotal");
  const paymentAmount = layawayReceipt.querySelector("#paymentAmount");
  const paymentMethod = layawayReceipt.querySelector("#paymentMode");
  const receiptDate = layawayReceipt.querySelector("#receiptDate");
  const salesmanName = layawayReceipt.querySelector("#salesmanName");

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

  const formData = new FormData(addLayawayform);
  formData.append("action", "addLayaway");
  formData.append("customer_id", customerId);

  fetch(`${ajax_inventory.ajax_url}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        layawayReceipt.classList.remove("hidden");
        addLayawayDiv.classList.add("hidden");

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
        addLayawayform.reset();
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
layawayRecieptPrint?.addEventListener("click", function (e) {
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
              ${layawayReceipt.outerHTML}
            </body>
        </html>
    `);
  printWindow.focus();
  printWindow.print();
  printWindow.close();
});

// STEP 3: Search the products

// grbbing the elements
const searchProductsForm = document.querySelector(
  "form[name='search-products']"
);
const searchproductInput = searchProductsForm.querySelector("#search-products");
const searchProductsButton = searchProductsForm.querySelector(
  "#search-product-btn"
);
const searchProductsResult = document.querySelector("#search-product-results");

// Searching the products
searchProductsForm.addEventListener("submit", function (event) {
  event.preventDefault();
  const searchValue = searchproductInput.value.trim();
  searchProductsButton.setAttribute("disabled", true);
  searchProductsButton.innerHTML = "Searching...";
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
        searchProductsResult.innerHTML = "";
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
          searchProductsResult.innerHTML = `
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
            searchProductsResult.querySelector(".add-to-cart");
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
            searchProductsResult.innerHTML = "";
            searchproductInput.value = "";
            searchProductsDiv.classList.add("hidden");
          });
        } else {
          searchProductsResult.innerHTML = `No products found for "${searchValue}".`;
        }
        searchProductsButton.removeAttribute("disabled");
        searchProductsButton.innerHTML = "Search";
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  } else {
    searchProductsResult.innerHTML = "";
    searchProductsResult.innerHTML = "Please enter a search term.";
    searchProductsButton.removeAttribute("disabled");
    searchProductsButton.innerHTML = "Search";
  }
});

// FINAL SALES/CART SECTION
const finalizeSale = cartDiv.querySelector("form[name='finalize-sale']");
const finalizeSubTotal = finalizeSale.querySelector("#subtotal");
const excludeGst = finalizeSale.querySelector("#exclude-gst");
const excludePst = finalizeSale.querySelector("#exclude-pst");
const finalizeGst = finalizeSale.querySelector("#gst");
const finalizePst = finalizeSale.querySelector("#pst");
const finalizeTotal = finalizeSale.querySelector("#total");
cartItemsDiv.addEventListener("click", handleCartClick);

// grabbing payments methods elemenets
const cashEl = finalizeSale.querySelector("#cash");
const chequeEl = finalizeSale.querySelector("#cheque");
const debitEl = finalizeSale.querySelector("#debit");
const visaEl = finalizeSale.querySelector("#visa");
const mastercardEl = finalizeSale.querySelector("#master_card");
const amexEl = finalizeSale.querySelector("#amex");
const discoverEl = finalizeSale.querySelector("#discover");
const travelChequeEl = finalizeSale.querySelector("#travel_cheque");
const cupEl = finalizeSale.querySelector("#cup");
const alipayEl = finalizeSale.querySelector("#alipay");
const layawayEl = finalizeSale.querySelector("#layaway");

function getTotals() {
  const subtotal = cart.reduce((sum, item) => {
    return sum + (parseFloat(item.price_after_discount || item.price) || 0);
  }, 0);

  const gstRate = excludeGst?.checked ? 0 : 0.05;
  const pstRate = excludePst?.checked ? 0 : 0.08;

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

  finalizeSubTotal.value = subtotal.toFixed(2);
  finalizeGst.value = gst.toFixed(2);
  finalizePst.value = pst.toFixed(2);
  finalizeTotal.value = total.toFixed(2);

  if (layawayTotal > 0 && !checkbox) {
    const layawayInput = finalizeSale.querySelector("#layaway");
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

  cartItemsDiv.innerHTML = cartHTML;
  layawayEl.max = layawayTotal;
  cartDiv.classList.remove("hidden");
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

  editItemsModalDiv.classList.remove("hidden");

  // grab the modal elements
  const titleEl = editItemsModalDiv.querySelector("#edit-item-title");
  const skuEl = editItemsModalDiv.querySelector("#edit-item-sku");
  const priceEl = editItemsModalDiv.querySelector("#edit-item-price");
  const discountAmtEl = editItemsModalDiv.querySelector("#edit-discount-amt");
  const discountPctEl = editItemsModalDiv.querySelector("#edit-discount-pct");
  const priceAfterDiscountEl = editItemsModalDiv.querySelector(
    "#edit-price-after-discount"
  );
  const saveBtn = editItemsModalDiv.querySelector("#save-edit");
  const cancelBtn = editItemsModalDiv.querySelector("#cancel-edit");
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
    editItemsModalDiv.classList.add("hidden");
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
    editItemsModalDiv.classList.add("hidden");
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
  // grabbing the values
  const cash = parseFloat(cashEl.value) || 0;
  const cheque = parseFloat(chequeEl.value) || 0;
  const debit = parseFloat(debitEl.value) || 0;
  const visa = parseFloat(visaEl.value) || 0;
  const mastercard = parseFloat(mastercardEl.value) || 0;
  const amex = parseFloat(amexEl.value) || 0;
  const discover = parseFloat(discoverEl.value) || 0;
  const travelCheque = parseFloat(travelChequeEl.value) || 0;
  const cup = parseFloat(cupEl.value) || 0;
  const alipay = parseFloat(alipayEl.value) || 0;
  const layaway = parseFloat(layawayEl.value) || 0;

  const totalPaid = parseFloat(
    (
      cash +
      cheque +
      debit +
      visa +
      mastercard +
      amex +
      discover +
      travelCheque +
      cup +
      alipay +
      layaway
    ).toFixed(2)
  );

  if (totalPaid !== total) {
    alert(
      `Payment does not match total!\nExpected: $${total.toFixed(
        2
      )}\nReceived: $${totalPaid.toFixed(2)}`
    );
    return false;
  }
  const reference = finalizeSale.querySelector("#reference").value.trim();
  const salesperson = finalizeSale.querySelector("#salesperson").value.trim();

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

excludeGst.addEventListener("change", calculateTotal);
excludePst.addEventListener("change", calculateTotal);

finalizeSale.addEventListener("submit", function (e) {
  e.preventDefault();

  if (cart.length === 0) {
    alert(
      "Cart is empty. Please add items to the cart before finalizing the sale."
    );
    return;
  }
  const validatedForm = validateAndSubmitSale();

  if (!validatedForm) return;

  const formData = new FormData(finalizeSale);

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

        saleResult.classList.remove("hidden");
        cartDiv.classList.add("hidden");
        customerDetails.classList.add("hidden");

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

        receiptContent.innerHTML = `
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

salesPrintReceipt?.addEventListener("click", function (e) {
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
              ${receiptContent.outerHTML}
            </body>
        </html>
    `);
  printWindow.focus();
  printWindow.print();
  printWindow.close();
});
