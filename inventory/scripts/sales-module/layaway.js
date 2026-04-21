import { formatCurrency, formatLabel } from "../index.js";
import { showSelection } from "../sales.js";
import { AppState } from "./state.js";

export const LayawaySelector = {
  init() {
    this.viewLayaway = document.querySelector("#viewLayaway");

    // Display customer layaway
    this.layawayDetails = document.querySelector("#layawayDetails");
    this.items = document.querySelector("#layawayItems");
    this.layawayTotal = document.querySelector("#layaway-total");
    this.addLayaway = document.querySelector("#addLayaway");

    // Add Customer Layaway
    this.layawayFormDiv = document.querySelector("#addLayawayForm");
    this.layawayForm = document.querySelector("form[name='add-layaway']");
    this.layawayFormSubmitButton = document.querySelector(
      "form[name='add-layaway'] #submit-layaway",
    );

    // Print the custoemr Layaway
    this.layawayReceipt = document.querySelector("#layawayReceipt");
    this.layawayReceiptPrint = document.querySelector("#layawayPrintReceipt");

    this.bindEvents();
  },

  bindEvents() {
    this.viewLayaway.addEventListener("click", (e) => {
      e.preventDefault();
      showSelection(this.layawayDetails);

      if (!AppState.customer.id) {
        this.items.innerHTML = "Please select a customer first.";
        return;
      }

      fetch(
        `${ajax_inventory.ajax_url}?action=getLayaway&customer_id=${AppState.customer.id}&location_id=${AppState.location.id}`,
        {
          method: "GET",
        },
      )
        .then((response) => response.json())
        .then((res) => {
          if (res.success) {
            this.items.innerHTML = "";
            res.data.forEach((item) => {
              const layawayItem = document.createElement("tr");
              layawayItem.classList.add("layaway-item");
              layawayItem.innerHTML = `
            <td>${item.payment_date.split(" ")[0]}</p>
            <td>${item.reference_num}</p>
            <td>${formatLabel(item.transaction_type)}</p>
            <td>${formatLabel(item.method)}</p>
            <td>${item.amount}</p>
            <td>${item.notes}</p>
            <td>${item.salesperson_first_name} ${item.salesperson_last_name}</p>
          `;
              this.items.appendChild(layawayItem);
            });

            this.layawayTotal.innerHTML = formatCurrency(AppState.layawayTotal);
          } else {
            this.items.innerHTML = res.data;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
        });
    });

    this.addLayaway?.addEventListener("click", (e) => {
      e.preventDefault();
      this.layawayDetails.classList.add("hidden");
      this.layawayFormDiv.classList.remove("hidden");
    });

    this.layawayForm?.addEventListener("submit", (e) => {
      e.preventDefault();
      this.layawayFormSubmitButton.setAttribute("disabled", true);
      const cash = this.layawayForm.querySelector("#cash").value;
      const cheque = this.layawayForm.querySelector("#cheque").value;
      const debit = this.layawayForm.querySelector("#debit").value;
      const visa = this.layawayForm.querySelector("#visa").value;
      const master_card = this.layawayForm.querySelector("#master_card").value;
      const amex = this.layawayForm.querySelector("#amex").value;
      const bank_draft = this.layawayForm.querySelector("#bank_draft").value;
      const cup = this.layawayForm.querySelector("#cup").value;
      const alipay = this.layawayForm.querySelector("#alipay").value;
      const wire = this.layawayForm.querySelector("#wire").value;
      const trade_in = this.layawayForm.querySelector("#trade_in").value;
      const credit = this.layawayForm.querySelector("#credit").value;
      const layawayReference =
        this.layawayForm.querySelector("#layaway-reference").value;
      const salesperson = this.layawayForm.querySelector("#salesperson").value;
      const layawayDate = this.layawayForm.querySelector("#layaway-date").value;

      if (
        cash === "" &&
        cheque === "" &&
        debit === "" &&
        visa === "" &&
        master_card === "" &&
        amex === "" &&
        bank_draft === "" &&
        cup === "" &&
        alipay === "" &&
        wire === "" &&
        trade_in === "" &&
        credit === ""
      ) {
        alert("Please enter at least one payment method.");
        return;
      }

      if (!layawayReference || !salesperson || !layawayDate) {
        alert("Please fill in reference and salesperson.");
        return;
      }

      const formData = new FormData(this.layawayForm);
      formData.append("action", "addLayaway");
      formData.append("customer_id", AppState.customer.id);
      formData.append("location_id", AppState.location.id);

      fetch(`${ajax_inventory.ajax_url}`, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            this.layawayReceipt.classList.remove("hidden");
            this.layawayReceiptPrint.classList.remove("hidden");
            this.layawayFormDiv.classList.add("hidden");

            this.displayReceipt(result.data, layawayReference);

            AppState.layawayTotal = result.data.layaway_sum.layaway;
            AppState.creditTotal = result.data.layaway_sum.credit;
            document.dispatchEvent(new CustomEvent("layaway:added"));
            this.layawayForm.reset();
          } else {
            alert("Failed to process payment: " + result.data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred: " + error);
        })
        .finally(() => {
          this.layawayFormSubmitButton.removeAttribute("disabled");
        });
    });

    // STEP 3.3: Print Layaway Receipt
    this.layawayReceiptPrint?.addEventListener("click", (e) => {
      e.preventDefault();
      const cssPath = ajax_inventory.sales_css_url;
      // Create a new window for printing
      const printWindow = window.open("", "_blank");
      printWindow.document.write(`
        <html>
            <head>
              <title>Layaway Receipt</title>
              <link rel="stylesheet" href="${cssPath}" onload="window.__cssLoaded = true;" />
            </head>
            <body>
              ${this.layawayReceipt.outerHTML}
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
    });
  },

  displayReceipt(data, reference) {
    const paymentLines = data.payments
      .map((payment) => `${payment.method}: $${formatCurrency(payment.amount)}`)
      .join(", ");

    const paymentTotal = data.payments.reduce(
      (sum, p) => sum + Number(p.amount),
      0,
    );

    this.layawayReceipt.innerHTML = `
      <header>
        <div>
          <h2>Montecristo Jewellers</h2>
          <p><strong>Layaway Receipt</strong></p>
        </div>
        <div>
          <address>
            <p>${AppState.customer.firstName} ${AppState.customer.lastName}</p>
            <p>${AppState.customer.address.split(",").join("<br/>")}</p>
          </address>
        </div>
        <div>
          <p>Reference # ${reference}</p>
          <p>Payment on <time datetime="${data.payment_date}">${data.payment_date}</time></p>
          <p>Served by ${data.salesperson}</p>
        </div>
      </header>

      <main>
        <table>
          <tfoot>
            <tr>
              <td class="payment-summary">
                ${data.notes ? `<p>${data.notes}</p>` : ""}
                <p>Paid by ${paymentLines}</p>
                <p>Thank you for shopping at Montecristo Jewellers</p>
              </td>
              <td>
                <strong>Payment Amount: $${formatCurrency(paymentTotal)}</strong><br />
                <strong>Layaway Total: $${formatCurrency(data.layaway_sum.layaway)}</strong><br />
                <strong>Credit Total: $${formatCurrency(data.layaway_sum.credit)}</strong>
              </td>
            </tr>
          </tfoot>
        </table>
      </main>
    `;
  },
};
