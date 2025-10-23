import { AppState } from "./state.js";

export const ServiceSelector = {
  init() {
    this.addServiceBtn = document.querySelector("#addService");
    this.serviceModal = document.querySelector("#service-modal");

    this.serviceForm = document.querySelector("form[name='add-service']");
    this.category = this.serviceForm.querySelector("#category");
    this.description = this.serviceForm.querySelector("#description");
    this.costPrice = this.serviceForm.querySelector("#costPrice");
    this.retailPrice = this.serviceForm.querySelector("#retailPrice");
    this.reference = this.serviceForm.querySelector("#reference");

    this.bindEvents();
  },

  bindEvents() {
    this.addServiceBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      this.serviceModal.classList.remove("hidden");
      this.category.focus();
    });

    this.serviceModal.addEventListener("click", (e) => {
      if (e.target.id === "cancel") {
        e.preventDefault();
        this.resetServiceInuts();
      }
    });

    this.serviceForm.addEventListener("submit", (e) => {
      e.preventDefault();

      if (
        this.costPrice.value.trim() === "" ||
        this.retailPrice.value.trim() === ""
      ) {
        alert("Please fill in all the required fields.");
        return;
      }

      AppState.services.push({
        category: this.category.value,
        description: this.description.value,
        costPrice: Number(this.costPrice.value),
        retailPrice: Number(this.retailPrice.value),
        reference: this.reference.value,
      });

      this.resetServiceInuts();

      document.dispatchEvent(new CustomEvent("displayCart"));
    });
  },

  resetServiceInuts() {
    this.description.value = "";
    this.costPrice.value = "";
    this.retailPrice.value = "";
    this.reference.value = "";
    this.serviceModal.classList.add("hidden");
  },
};
