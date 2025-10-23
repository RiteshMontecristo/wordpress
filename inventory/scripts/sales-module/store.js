import { AppState } from "./state.js";

export const StoreSelector = {
  STORAGE_KEY: "selectedStore",
  EXPIRY_HOURS: 2, // in hours

  init() {
    this.modal = document.querySelector("#store-modal");
    this.topStore = document.querySelector("#top-store");
    this.topStoreName = document.querySelector("#store-name");
    this.changeBtn = document.querySelector("#change-store-btn");

    this.loadFromLocalStorage();
    this.bindEvents();
    this.updateUI();
  },

  bindEvents() {
    this.modal.querySelectorAll(".store-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const storeId = button.dataset.id;
        const storeName = button.textContent;
        this.setStore(storeId, storeName);
      });
    });

    // Change store button
    this.changeBtn.addEventListener("click", () => {
      this.clearStore();
      this.updateUI();
    });
  },

  loadFromLocalStorage() {
    const saved = localStorage.getItem(this.STORAGE_KEY);
    if (!saved) return;

    const { id, name, expiry } = JSON.parse(saved);
    if (expiry && Date.now() > expiry) {
      this.clearStore();
      return;
    }
    this.state = { id, name, expiry };
    AppState.location = {
      id,
      name,
    };
  },

  setStore(id, name) {
    const expiry = Date.now() + this.EXPIRY_HOURS * 60 * 60 * 1000;
    this.state = { id, name, expiry };
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(this.state));
    this.updateUI();

    window.location.reload();
  },

  clearStore() {
    this.state = { id: null, name: null, expiry: null };
    localStorage.removeItem(this.STORAGE_KEY);

    AppState.location = {};
  },

  updateUI() {
    if (this.state?.id) {
      this.topStore.style.display = "block";
      this.topStoreName.textContent = this.state.name;
      this.changeBtn.style.display = "inline-block";
      this.modal.style.display = "none";
    } else {
      this.topStore.style.display = "none";
      this.changeBtn.style.display = "none";
      this.modal.style.display = "flex";
    }
  },
};
