import { formatCurrency, formatLabel } from "../index.js";
import { showSelection } from "../sales.js";
import { AppState } from "./state.js";

export const CheckoutSelector = {
  TAX_RATES: { GST: 0.05, PST: 0.07 },

  init() {
    this.details = document.querySelector("#customerDetails");
    this.finalizeSale = document.querySelector(
      "#cart form[name='finalize-sale']",
    );
    this.payment = {
      cash: document.querySelector("#cart #cash"),
      cheque: document.querySelector("#cart #cheque"),
      debit: document.querySelector("#cart #debit"),
      visa: document.querySelector("#cart #visa"),
      mastercard: document.querySelector("#cart #master_card"),
      amex: document.querySelector("#cart #amex"),
      discover: document.querySelector("#cart #discover"),
      bankDraft: document.querySelector("#cart #bank_draft"),
      cup: document.querySelector("#cart #cup"),
      alipay: document.querySelector("#cart #alipay"),
      wire: document.querySelector("#cart #wire"),
    };
    this.layawayContainer = document.querySelector("#cart #layawayContainer");
    this.creditContainer = document.querySelector("#cart #creditContainer");
    this.location = document.querySelector("#cart #location");
    this.subtotal = document.querySelector("#cart #subtotal");
    this.excludeGst = document.querySelector("#cart #exclude-gst");
    this.excludePst = document.querySelector("#cart #exclude-pst");
    this.gst = document.querySelector("#cart #gst");
    this.pst = document.querySelector("#cart #pst");
    this.total = document.querySelector("#cart #total");
    this.saleResult = document.querySelector("#saleResult");
    this.receiptContent = document.querySelector("#receiptContent");
    this.salesPrintReceipt = document.querySelector("#salesPrintReceipt");
    this.bindEvents();
  },

  bindEvents() {
    document.addEventListener("checkout:updateLayaway", async () => {
      try {
        let layawayRes = await fetch(
          `${ajax_inventory.ajax_url}?action=getActiveLayaway&customer_id=${AppState.customer.id}&location_id=${AppState.location.id}`,
        );

        layawayRes = await layawayRes.json();
        const { data } = layawayRes;
        if (data.length > 0) {
          this.renderLayaway(data);
        }

        let creditRes = await fetch(
          `${ajax_inventory.ajax_url}?action=getActiveCredit&customer_id=${AppState.customer.id}&location_id=${AppState.location.id}`,
        );

        creditRes = await creditRes.json();
        const creditData = creditRes.data;

        if (creditData.length > 0) {
          this.renderCredit(creditData);
        }
      } catch (err) {
        console.error("Fetch operation failed:", err);
      }
    });

    this.excludeGst.addEventListener("change", this.calculateTotal.bind(this));
    this.excludePst.addEventListener("change", this.calculateTotal.bind(this));
    this.salesPrintReceipt?.addEventListener("click", () =>
      this.printReceipt(),
    );

    this.finalizeSale.addEventListener("submit", (e) => {
      e.preventDefault();

      const submitBtn = this.finalizeSale.querySelector("button");
      submitBtn.disabled = true;

      if (AppState.cart.length === 0 && AppState.services.length === 0) {
        alert(
          "Cart is empty. Please add items to the cart before finalizing the sale.",
        );
        submitBtn.disabled = false;
        return;
      }
      const validatedForm = this.validateAndSubmitSale();

      if (!validatedForm) {
        submitBtn.disabled = false;
        return;
      }

      const formData = new FormData(this.finalizeSale);

      formData.append("action", "finalizeSale");
      formData.append("location", AppState.location.id);
      formData.append("customer_id", AppState.customer.id);
      formData.append("items", JSON.stringify(AppState.cart));
      formData.append("services", JSON.stringify(AppState.services));

      fetch(`${ajax_inventory.ajax_url}`, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            showSelection(this.saleResult);
            this.details.classList.add("hidden");

            const { data } = result;
            this.displayReceipt(data);
          } else {
            submitBtn.disabled = false;
            alert("Failed to complete sale: " + result.data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          submitBtn.disabled = false;
          alert("An error occurred while processing the sale.");
        });
    });
  },

  renderLayaway(data) {
    const layawayEl = data.map((el) => {
      return `
              <div>
                <label for="layaway-${el.id}">Layaway #${el.reference_num}:</label>
                <input class="layaway" type="number" min="0" step="0.01" value=${el.remaining_amount} max=${el.remaining_amount} id="layaway-${el.id}" name="layaway-${el.id}">
              </div>
      `;
    });

    this.layawayContainer.innerHTML = layawayEl.join("");
  },

  renderCredit(data) {
    const creditEl = data.map((el) => {
      return `
              <div>
                <label for="credit-${el.id}">Credit #${el.reference_num}:</label>
                <input class="credit" type="number" min="0" step="0.01" value=${el.remaining_amount} max=${el.remaining_amount} id="credit-${el.id}" name="credit-${el.id}">
              </div>
      `;
    });

    this.creditContainer.innerHTML = creditEl.join("");
  },

  getTotals() {
    let subtotal = AppState.cart.reduce((sum, item) => {
      return sum + (Number(item.price_after_discount || item.price) || 0);
    }, 0);
    subtotal = AppState.services.reduce((sum, item) => {
      return sum + Number(item.retailPrice);
    }, subtotal);

    const gstRate = this.excludeGst?.checked ? 0 : this.TAX_RATES.GST;
    const pstRate = this.excludePst?.checked ? 0 : this.TAX_RATES.PST;

    const gst = Number((subtotal * gstRate).toFixed(2));
    const pst = Number((subtotal * pstRate).toFixed(2));
    const total = subtotal + gst + pst;

    return {
      subtotal: formatCurrency(subtotal),
      gst: formatCurrency(gst),
      pst: formatCurrency(pst),
      total: formatCurrency(total),
    };
  },

  calculateTotal(checkbox = true) {
    const { subtotal, gst, pst, total } = this.getTotals();

    this.subtotal.value = subtotal;
    this.gst.value = gst;
    this.pst.value = pst;
    this.total.value = total;

    if (AppState.layawayTotal > 0 && !checkbox) {
      const layawayInput = this.layaway;
      if (layawayInput) layawayInput.value = AppState.layawayTotal;
    }
  },

  validateAndSubmitSale() {
    const allLayaway = document.querySelectorAll("#cart .layaway");
    const allCredit = document.querySelectorAll("#cart .credit");
    const { total } = this.getTotals();

    const payments = this.payment;
    let totalPaid = 0;
    let totalLayaway = 0;
    let totalCredit = 0;

    allLayaway.forEach((el) => {
      totalPaid += parseFloat(el.value);
      totalLayaway += parseFloat(el.value);
    });

    if (totalLayaway > 0 && totalLayaway > AppState.layawayTotal) {
      alert("Layaway entered is greater than the amount the customer has.");
      return false;
    }

    allCredit.forEach((el) => {
      totalPaid += parseFloat(el.value);
      totalCredit += parseFloat(el.value);
    });

    if (totalCredit > 0 && totalCredit > AppState.creditTotal) {
      alert("Layaway entered is greater than the amount the customer has.");
      return false;
    }

    for (const payment of Object.values(payments)) {
      totalPaid += Number(payment.value) || 0;
    }

    if (Math.abs(totalPaid - Number(total)) > 0.01) {
      alert(
        `Payment does not match total!\nExpected: $${total}\nReceived: $${formatCurrency(
          totalPaid,
        )}`,
      );
      return false;
    }
    const reference = this.finalizeSale
      .querySelector("#reference")
      .value.trim();
    const salesperson = this.finalizeSale
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
  },

  displayReceipt(data) {
    let itemRows = data.items
      ?.map((item) => {
        return `
                    <tr>
                      <td>
                          <img src="${item.image_url}" /><br />
                          ${item.description.split("•").join("<br />•")}
                          <br>•SKU: ${item.sku} 
                          <br>•Serial: ${item.serial}
                      </td>
                      <td>$${item.price_after_discount}</td>
                    </tr>
                  `;
      })
      .join("");

    itemRows += data.services.map((service) => {
      return `
                <tr>
                    <td>${formatLabel(service.category)} <br /> ${
                      service.description
                    }</td>
                    <td>$${formatCurrency(service.retailPrice)}</td>
                </tr>
              `;
    });

    const paymentLines = data.payments
      .map((payment) => {
        if (payment.method == "layaway" || payment.method == "credit") {
          return `${payment.method} #${
            payment.reference_num
          }: $${formatCurrency(payment.amount)}`;
        } else {
          return `${payment.method}: $${formatCurrency(payment.amount)}`;
        }
      })
      .join(", ");

    this.receiptContent.innerHTML = `
                <header>
                  <div>
                    <h2>Montecristo Jewellers</h2>
                    <p><strong>Receipt</strong></p>
                  </div>
                  <div>
                    <address>
                      <p>${AppState.customer.firstName} ${
                        AppState.customer.lastName
                      }</p>
                      <p>${AppState.customer.address
                        .split(",")
                        .join("<br/>")}</p>
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
                        <td class="payment-summary">
                        ${data.notes ? `<p>${data.notes}</p>` : ""}
                          <p>${
                            data.payments.length == 0
                              ? "This is a gift!!"
                              : `Paid by ${paymentLines}`
                          }</p>
                          <p>Thank you for shopping at Montecristo Jewellers</p>
                        </td>
                        <td>
                          <strong>Subtotal: $${formatCurrency(data.totals.subtotal)}</strong><br />
                          <strong>GST (5%): $${formatCurrency(data.totals.gst)} </strong><br />
                          <strong>PST (8%): $${formatCurrency(data.totals.pst)} </strong><br />
                          <strong>Total: $${formatCurrency(data.totals.total)}</strong>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </main>
              `;
  },

  printReceipt() {
    const cssPath = ajax_inventory.sales_css_url;
    const printWindow = window.open("", "_blank");
    printWindow.document.write(`
        <html>
            <head>
              <title>Sales Receipt</title>
              <link rel="stylesheet" href="${cssPath}" onload="window.__cssLoaded = true;" />
            </head>
            <body>
              ${this.receiptContent.outerHTML}
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
  },
};
