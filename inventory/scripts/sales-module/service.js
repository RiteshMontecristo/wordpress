import { AppState } from "./state.js";

export const ServiceSelector = {
  init() {
    this.editingId = null;
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

    this.serviceForm.addEventListener("submit", (e) =>
      this.submitServiceForm(e)
    );
  },

  resetServiceInuts() {
    this.description.value = "";
    this.costPrice.value = "";
    this.retailPrice.value = "";
    this.reference.value = "";
    this.category.value = "watch_service";
    this.editingId = null;

    const addButton = this.serviceForm.querySelector('button[type="submit"]');
    addButton.textContent = "Add";

    this.serviceModal.classList.add("hidden");
  },

  openEditModal(serviceId) {
    const service = AppState.services.find((s) => s.id == serviceId);
    if (!service) return;

    this.category.value = service.category;
    this.description.value = service.description || "";
    this.costPrice.value = service.costPrice;
    this.retailPrice.value = service.retailPrice;
    this.reference.value = service.reference || "";

    this.editingId = serviceId;

    const addButton = this.serviceForm.querySelector('button[type="submit"]');
    addButton.textContent = "Update";

    this.serviceModal.classList.remove("hidden");
    this.category.focus();
  },

  submitServiceForm(e) {
    e.preventDefault();

    const id = this.editingId;

    if (
      this.costPrice.value.trim() === "" ||
      this.retailPrice.value.trim() === ""
    ) {
      alert("Please fill in all the required fields.");
      return;
    }

    const data = {
      id: id ? id : Date.now(),
      category: this.category.value,
      description: this.description.value,
      costPrice: Number(this.costPrice.value),
      retailPrice: Number(this.retailPrice.value),
      reference: this.reference.value,
    };

    if (id) {
      const index = AppState.services.findIndex((s) => s.id == id);
      if (index !== -1) {
        AppState.services[index] = data;
      }
    } else {
      AppState.services.push(data);
    }

    this.resetServiceInuts();

    document.dispatchEvent(new CustomEvent("displayCart"));
  },
};
