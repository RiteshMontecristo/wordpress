import { formatCurrency } from "../index.js";
import { AppState } from "./state.js";

export const CustomerSelector = {
  init() {
    this.searchCustomer = document.querySelector("#search-customer");
    this.searchForm = document.querySelector("form[name='search-customer']");
    this.searchInput = document.querySelector("#search-customer #search");
    this.searchBtn = document.querySelector("#search-btn");
    this.name = document.querySelector("#customer-name");
    this.address = document.querySelector("#customer-address");
    this.layawaySum = document.querySelector("#layawaySum");
    this.details = document.querySelector("#customerDetails");
    this.searchResults = document.querySelector("#search-customer-results");

    this.bindEvents();
  },

  bindEvents() {
    this.searchForm.addEventListener("submit", (event) => {
      event.preventDefault();
      const searchValue = this.searchInput.value.trim();
      this.searchBtn.setAttribute("disabled", true);
      this.searchBtn.innerHTML = "Searching...";

      if (searchValue) {
        fetch(
          `${ajax_inventory.ajax_url}?action=search_customer&location_id=${
            AppState.location.id
          }&search_value=${encodeURIComponent(searchValue)}`,
          {
            method: "GET",
          }
        )
          .then((response) => response.json())
          .then((res) => {
            if (res.success) {
              this.searchResults.innerHTML = "";
              this.searchResults.innerHTML = res.data;
            }
            this.searchBtn.removeAttribute("disabled");
            this.searchBtn.innerHTML = "Search";
            this.setupSelectCustomerButtons();
          })
          .catch((error) => {
            console.error("Error:", error);
          });
      } else {
        this.searchBtn.removeAttribute("disabled");
        this.searchBtn.innerHTML = "Search";

        this.searchResults.innerHTML = "";
        this.searchResults.innerHTML = "Please enter a search term.";
      }
    });
  },

  setupSelectCustomerButtons() {
    const selectCustomerButtons = document.querySelectorAll(".select-customer");
    selectCustomerButtons?.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();
        const buttonParent = button.closest("tr");

        // Grabbing the selected customer's details
        const customerData = {
          id: button.dataset.customerid,
          firstName: buttonParent.querySelector("#firstName").innerText.trim(),
          lastName: buttonParent.querySelector("#lastName").innerText.trim(),
          address: buttonParent.querySelector("#address").innerText.trim(),
        };

        this.searchCustomer.classList.add("hidden");
        this.details.classList.remove("hidden");

        document.dispatchEvent(new CustomEvent("customer:selected"));

        AppState.customer = customerData;

        this.name.textContent = `${customerData.firstName} ${customerData.lastName}`;
        this.address.textContent = customerData.address;
        this.updateLayaway();
      });
    });
  },

  updateLayaway() {
    fetch(
      `${ajax_inventory.ajax_url}?action=getLayawaySum&customer_id=${AppState.customer.id}&location_id=${AppState.location.id}`,
      {
        method: "GET",
      }
    )
      .then((response) => response.json())
      .then((res) => {
        if (res.data) {
          AppState.layawayTotal = Number(res.data.layaway);
          AppState.creditTotal = Number(res.data.credit);
          this.layawaySum.textContent = `Layaway Total: ${formatCurrency(
            res.data.layaway
          )} CAD`;
        }
      });
  },
};
