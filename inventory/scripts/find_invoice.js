import { formatCurrency } from "./index.js";

const issueCreditBtn = document.querySelector("#issue_credit");
const creditEl = document.querySelector("#credit");
const cancelCreditBtn = creditEl?.querySelector(".button.cancel");
const creditContainer = creditEl?.querySelector(".credit-container");
const creditForm = document?.querySelector('form[name="credit_invoice"]');
const submitCreditReturnBtn = creditForm?.querySelector("#submit_return");
const allItemsCheckbox = creditForm?.querySelectorAll(".return-item-checkbox");
const allCreditItemsPrice = creditForm?.querySelectorAll(".refund_price");

const issueRefundBtn = document?.querySelector("#issue_refund");
const refundEl = document?.querySelector("#refund");
const cancelRefundBtn = refundEl?.querySelector(".button.cancel");
const refundContainer = refundEl?.querySelector(".refund-container");
const refundForm = document?.querySelector('form[name="refund_invoice"]');
const submitRefundReturnBtn = refundForm?.querySelector("#submit_return");
const allRefundItemsCheckbox = refundForm?.querySelectorAll(
  ".return-item-checkbox",
);
const allRefundItemsPrice = refundForm?.querySelectorAll(".refund_price");
const allPaymentsMethod = refundForm?.querySelectorAll(".payment-item");
const mainPrintBtn = document.querySelector("#main-print-btn");

const GST = 0.05,
  PST = 0.07;

let subtotal, gst, pst, total;

// Credit Section
issueCreditBtn?.addEventListener("click", (e) => toggleDisplayEl(e, creditEl));
cancelCreditBtn?.addEventListener("click", (e) => toggleDisplayEl(e, creditEl));
allItemsCheckbox?.forEach((checkbox) => {
  checkbox.addEventListener("change", () =>
    updateTotals(allItemsCheckbox, creditForm),
  );
});
allCreditItemsPrice?.forEach((inputPrice) => {
  inputPrice?.addEventListener("change", () =>
    updateTotals(allItemsCheckbox, creditForm),
  );
});
creditForm?.addEventListener("submit", (e) =>
  sumbitForm(e, creditForm, "credit"),
);

// Refund Section
issueRefundBtn?.addEventListener("click", (e) => toggleDisplayEl(e, refundEl));
cancelRefundBtn?.addEventListener("click", (e) => toggleDisplayEl(e, refundEl));
allRefundItemsCheckbox?.forEach((checkbox) => {
  checkbox.addEventListener("change", () =>
    updateTotals(allRefundItemsCheckbox, refundForm),
  );
});
allRefundItemsPrice?.forEach((inputPrice) => {
  inputPrice?.addEventListener("change", () =>
    updateTotals(allRefundItemsCheckbox, refundForm),
  );
});
refundForm?.addEventListener("submit", (e) =>
  sumbitForm(e, refundForm, "refund"),
);

// Reusable functions by both
function toggleDisplayEl(e, el) {
  e.preventDefault();
  el.classList.toggle("refund");
}

function updateTotals(checkboxEl, formEl) {
  [gst, pst, subtotal, total] = [0, 0, 0];
  checkboxEl.forEach((cb) => {
    if (cb.checked) {
      const returnItemDiv = cb.closest("div.return-item");
      const itemsSubtotal = parseFloat(
        returnItemDiv.querySelector("input[type='number']").value,
      );
      subtotal += itemsSubtotal;

      if (cb.dataset.gst) {
        gst += itemsSubtotal * GST;
      }

      if (cb.dataset.pst) {
        pst += itemsSubtotal * PST;
      }
    }
  });

  gst = Math.round(gst * 100) / 100;
  pst = Math.round(pst * 100) / 100;
  subtotal = Math.round(subtotal * 100) / 100;
  total = subtotal + gst + pst;
  total = Math.round(total * 100) / 100;

  formEl.querySelector("#display-subtotal").textContent = subtotal.toFixed(2);
  formEl.querySelector("#display-gst").textContent = gst.toFixed(2);
  formEl.querySelector("#display-pst").textContent = pst.toFixed(2);
  formEl.querySelector("#display-total").textContent = total.toFixed(2);
}

async function sumbitForm(e, form, type) {
  e.preventDefault();

  const formData = new FormData(form);
  formData.append("gst", gst);
  formData.append("pst", pst);
  formData.append("subtotal", subtotal);
  formData.append("total", total);

  let errors = 0;

  if (type == "credit") {
    if (!formData.has("return_items[]") && !formData.has("return_services[]")) {
      errors++;
      alert("Please select at least one item!!");
    }
    submitCreditReturnBtn.disabled = true;
  } else {
    submitRefundReturnBtn.disabled = true;
    if (!formData.has("refund_items[]") && !formData.has("refund_services[]")) {
      errors++;
      alert("Please select at least one item!!");
    }
    let paymentTotal = 0;
    allPaymentsMethod.forEach((el) => {
      let val = el.querySelector("input").value;
      paymentTotal += parseFloat(val || 0);
    });

    if (paymentTotal !== total) {
      errors++;
      alert("Throw payment not equal!!");
    }
  }

  if (errors > 0) {
    submitCreditReturnBtn.disabled = false;
    submitRefundReturnBtn.disabled = false;
    return;
  }

  try {
    const response = await fetch(ajax_inventory.ajax_url, {
      method: "POST",
      body: formData,
    });
    const data = await response.json();

    if (data.success) {
      alert("Successfully created");
      createRefundReceipt(data.data, type);
    } else {
      alert(data.data.message);
    }
  } catch (error) {
    console.error("AJAX request failed:", error);
  } finally {
    submitCreditReturnBtn.disabled = false;
    submitRefundReturnBtn.disabled = false;
  }
}

function createRefundReceipt(data, type) {
  const el = type == "credit" ? creditContainer : refundContainer;
  type = type.charAt(0).toUpperCase() + type.slice(1);

  el.innerHTML = `
    <header class="receipt-header">
      <div class="company">
        <h2 class="title">Montecristo Jewellers</h2>
        <p class="subtitle"><strong>${type} Receipt</strong></p>
      </div>
      <div class="customer">
        <address>
          <p class="customer-name">${data.customer_info.prefix} ${data.customer_info.first_name} ${data.customer_info.last_name}</p>
          <p class="customer-address">${data.customer_info.street_address}, ${data.customer_info.city}, ${data.customer_info.province}, ${data.customer_info.postal_code}, ${data.customer_info.country}</p>
          ${data.customer_info?.phone ? `<p class="customer-phone">${data.customer_info.phone}</p>` : ""}
          <p class="customer-email">${data.customer_info?.email ?? ""}</p>
        </address>
      </div>
      <div class="details">
        <p class="reference-num">Reference # ${data.reference_num}</p>
        <p class="sale-date">Returned on <time datetime="${data.date}">${data.date}</time></p>
        <p class="salesperson">Served by ${data.salesperson.first_name} ${data.salesperson.last_name}</p>
      </div>
    </header>

    <main class="receipt-main">
      <table class="receipt-table">
        <thead>
          <tr>
            <th colspan="2">Item</th>
          </tr>
        </thead>
        <tbody>
          ${data.items
            .map(
              (el) => `
            <tr>
              <td colspan="2" class="item">
                <img class="item-image" src="${el.image_url}" />
                <br />
                ${el.description.split("•").join("<br />•")}
                <br />
                SKU: ${el.sku}
                <br />
                ${el.serial ? "Serial: " + el.serial + "<br />" : ""}
                Sold Price: ${el.price}
                <br />
                Returned Price: ${el.returned_price}
              </td>
            </tr>
          `,
            )
            .join("")}
          ${data.services
            .map(
              (el) => `
            <tr>
              <td colspan="2" class="item">
              ${el.category} <br />
              ${el.description ? "Description: " + el.description + "<br />" : ""}
              Sold Price: ${el.sold_price}
              <br />
              Returned Price: ${el.returned_price}
              </td>
            </tr>
          `,
            )
            .join("")}
        </tbody>
        <tfoot>
          <tr>
            <td class="summary">
              ${data.reason && `<p>${data.reason}</p>`}
              <p>Return for purchase #${data.original_reference}</p>
              <p>Thank you for shopping at Montecristo Jewellers</p>
            </td>
            <td class="totals">
              <strong>Subtotal: $${formatCurrency(data.totals.subtotal)}</strong><br />
              <strong>GST (5%): $${formatCurrency(data.totals.gst)}</strong><br />
              <strong>PST (8%): $${formatCurrency(data.totals.pst)}</strong><br />
              <strong>Total: $${formatCurrency(data.totals.total)}</strong>
            </td>
          </tr>
        </tfoot>
      </table>
    </main>

    <button class="button button-primary" id="print-receipt">Print</button>
    <button class="button" id="close-reciept">Close</button>
  `;

  const printBtn = el.querySelector("#print-receipt");
  const closeReceiptBtn = el.querySelector("#close-reciept");
  printBtn?.addEventListener("click", (e) => printReceipt(e, el.outerHTML));
  closeReceiptBtn.addEventListener("click", (e) => closeReceipt(e, type));
}

function printReceipt(e, el) {
  e.preventDefault();

  const printWindow = window.open("", "_blank");
  const cssPath = ajax_inventory.find_invoice_css_url;

  printWindow.document.write(`
        <html>
            <head>
              <title>Refund Receipt</title>
              <link rel="stylesheet" href="${cssPath}" onload="window.__cssLoaded = true;" />
            </head>
            <body>
              ${el}
            </body>
        </html>
    `);
  // Wait for CSS to load
  const checkCSS = setInterval(() => {
    if (printWindow.__cssLoaded) {
      clearInterval(checkCSS);
      printWindow.document.body.classList.add("css-ready");
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }
  }, 50);

  // Safety timeout (in case CSS fails to load)
  setTimeout(() => {
    clearInterval(checkCSS);
    if (!printWindow.__cssLoaded) {
      console.warn("CSS failed to load for print receipt");
      printWindow.document.body.classList.add("css-ready"); // force show
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }
  }, 3000);
}

function closeReceipt(e, type) {
  e.preventDefault();

  if (type == "Credit") {
    creditEl.classList.remove("refund");
  } else {
    refundEl.classList.remove("refund");
  }
}

mainPrintBtn?.addEventListener("click", (e) => {
  const invoice = document.querySelector(".invoice");

  printReceipt(e, invoice.outerHTML);
});
