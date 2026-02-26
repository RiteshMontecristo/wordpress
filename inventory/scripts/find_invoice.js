import { formatCurrency } from "./index.js";

const issueRefundBtn = document.querySelector("#issue_refund");
const refundInvoice = document.querySelector('form[name="refund_invoice"]');
const submitReturnBtn = document.querySelector("#submit_return");
const allItemsCheckbox = document.querySelectorAll(".return-item-checkbox");
const refundEl = document.querySelector("#refund");
const refundContainer = refundEl.querySelector(".refund-container");
const cancelBtn = refundEl.querySelector(".button.cancel");

const GST = 0.05,
  PST = 0.07;

let subtotal, gst, pst, total;

issueRefundBtn.addEventListener("click", (e) => {
  e.preventDefault();
  refundEl.classList.add("refund");
});

refundInvoice.addEventListener("submit", async (e) => {
  e.preventDefault();

  submitReturnBtn.disabled = true;
  const formData = new FormData(refundInvoice);
  formData.append("gst", gst);
  formData.append("pst", pst);
  formData.append("subtotal", subtotal);
  formData.append("total", total);

  try {
    const response = await fetch(ajax_inventory.ajax_url, {
      method: "POST",
      body: formData,
    });
    const data = await response.json();

    if (data.success) {
      alert("Successfully created");
      createRefundReceipt(data.data);
    } else {
      alert(data.data.message);
    }
  } catch (error) {
    console.error("AJAX request failed:", error);
  } finally {
    submitReturnBtn.disabled = false;
  }
});

allItemsCheckbox.forEach((checkbox) => {
  checkbox.addEventListener("change", updateTotals);
});

function updateTotals() {
  [gst, pst, subtotal, total] = [0, 0, 0];
  allItemsCheckbox.forEach((cb) => {
    if (cb.checked) {
      const itemsSubtotal = parseFloat(cb.dataset.subtotal || 0);
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

  document.getElementById("display-subtotal").textContent = subtotal.toFixed(2);
  document.getElementById("display-gst").textContent = gst.toFixed(2);
  document.getElementById("display-pst").textContent = pst.toFixed(2);
  document.getElementById("display-total").textContent = total.toFixed(2);
}

cancelBtn.addEventListener("click", (e) => {
  e.preventDefault();
  refundEl.classList.remove("refund");
});

function createRefundReceipt(data) {
  refundContainer.innerHTML = `
    <header class="receipt-header">
      <div class="company">
        <h2 class="title">Montecristo Jewellers</h2>
        <p class="subtitle"><strong>Return Receipt</strong></p>
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
            <th>Item</th>
            <th>Price</th>
          </tr>
        </thead>
        <tbody>
          ${data.items
            .map(
              (el) => `
            <tr>
              <td class="item">
                <img class="item-image" src="${el.image_url}" />
                <br />
                ${el.description.split("•").join("<br />•")}
                <br />
                SKU: ${el.sku}
                <br />
                Serial: ${el.serial ? el.serial : ""}
              </td>
              <td class="item-price">${el.price}</td>
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

  const printBtn = refundEl.querySelector("#print-receipt");
  const closeReceiptBtn = refundEl.querySelector("#close-reciept");
  printBtn?.addEventListener("click", printReceipt);
  closeReceiptBtn.addEventListener("click", closeReceipt);
}

function printReceipt(e) {
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
              ${refundContainer.outerHTML}
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

function closeReceipt(e) {
  e.preventDefault();

  refundEl.classList.remove("refund");
}
