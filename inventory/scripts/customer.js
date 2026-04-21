// ── Store selector (same localStorage key as sales.php so selection persists) ──

const CustomerStoreSelector = {
  STORAGE_KEY: "selectedStore",
  EXPIRY_HOURS: 2,

  init() {
    this.modal   = document.querySelector("#store-modal");
    this.topBar  = document.querySelector("#top-store");
    this.nameEl  = document.querySelector("#store-name");
    this.changeBtn = document.querySelector("#change-store-btn");

    if (!this.modal) return; // not on the list page (e.g. view/edit/add)

    const loaded = this.loadFromStorage();

    if (loaded) {
      // Ensure the URL has the correct location param
      const url = new URL(window.location.href);
      if (url.searchParams.get("location") !== String(this.state.id)) {
        url.searchParams.set("location", this.state.id);
        url.searchParams.delete("paged");
        window.location.replace(url.toString());
        return;
      }
    }

    this.bindEvents();
    this.updateUI();
  },

  loadFromStorage() {
    const raw = localStorage.getItem(this.STORAGE_KEY);
    if (!raw) return false;
    try {
      const { id, name, expiry } = JSON.parse(raw);
      if (expiry && Date.now() > expiry) {
        this.clearStore();
        return false;
      }
      this.state = { id, name, expiry };
      return true;
    } catch {
      return false;
    }
  },

  bindEvents() {
    this.modal.querySelectorAll(".store-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        this.setStore(btn.dataset.id, btn.dataset.name);
      });
    });

    this.changeBtn?.addEventListener("click", () => {
      this.clearStore();
    });
  },

  setStore(id, name) {
    const expiry = Date.now() + this.EXPIRY_HOURS * 60 * 60 * 1000;
    this.state = { id, name, expiry };
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(this.state));

    const url = new URL(window.location.href);
    url.searchParams.set("location", id);
    url.searchParams.delete("paged");
    url.searchParams.delete("search");
    window.location.href = url.toString();
  },

  clearStore() {
    this.state = null;
    localStorage.removeItem(this.STORAGE_KEY);

    const url = new URL(window.location.href);
    url.searchParams.delete("location");
    url.searchParams.delete("paged");
    window.location.href = url.toString();
  },

  updateUI() {
    if (this.state?.id) {
      this.topBar.style.display  = "flex";
      this.nameEl.textContent    = this.state.name;
      this.modal.style.display   = "none";
    } else {
      this.topBar.style.display  = "none";
      this.modal.style.display   = "flex";
    }
  },
};

CustomerStoreSelector.init();

// ── Customer add / edit form validation ──────────────────────────────────────

const customerForm = document.querySelector("form[name='customer']");
const customerCta  = customerForm?.querySelector("#customer_cta");

customerForm?.addEventListener("submit", (e) => {
  const firstName = customerForm.querySelector("#firstName")?.value.trim();
  const lastName  = customerForm.querySelector("#lastName")?.value.trim();
  if (!firstName || !lastName) {
    e.preventDefault();
    alert("Please fill in all required fields");
    return;
  }
  if (customerCta) {
    customerCta.disabled    = true;
    customerCta.textContent = "Submitting…";
  }
});

// ── Tab switcher (view page) ─────────────────────────────────────────────────

document.querySelectorAll(".nav-tab").forEach((tab) => {
  tab.addEventListener("click", (e) => {
    e.preventDefault();
    document.querySelectorAll(".nav-tab").forEach((t) => t.classList.remove("nav-tab-active"));
    document.querySelectorAll(".tab-content").forEach((c) => (c.style.display = "none"));
    tab.classList.add("nav-tab-active");
    document.querySelector(tab.getAttribute("href")).style.display = "block";
  });
});

// ── Notes save (view page) ───────────────────────────────────────────────────

const saveNotesBtn   = document.querySelector("#save-customer-notes");
const notesTextarea  = document.querySelector("#customer-notes");
const notesSaveStatus = document.querySelector("#notes-save-status");

saveNotesBtn?.addEventListener("click", async () => {
  const customerId = saveNotesBtn.dataset.customerId;
  const nonce      = saveNotesBtn.dataset.nonce;

  saveNotesBtn.disabled    = true;
  saveNotesBtn.textContent = "Saving…";
  if (notesSaveStatus) notesSaveStatus.textContent = "";

  const body = new FormData();
  body.append("action",      "save_customer_notes");
  body.append("nonce",       nonce);
  body.append("customer_id", customerId);
  body.append("notes",       notesTextarea.value);

  try {
    const res  = await fetch(ajax_inventory.ajax_url, { method: "POST", body });
    const data = await res.json();
    if (data.success) {
      if (notesSaveStatus) {
        notesSaveStatus.textContent = "Saved!";
        notesSaveStatus.style.color = "green";
      }
    } else {
      if (notesSaveStatus) {
        notesSaveStatus.textContent = "Error: " + (data.data?.message ?? "Unknown error");
        notesSaveStatus.style.color = "red";
      }
    }
  } catch {
    if (notesSaveStatus) {
      notesSaveStatus.textContent = "Network error";
      notesSaveStatus.style.color = "red";
    }
  } finally {
    saveNotesBtn.disabled    = false;
    saveNotesBtn.textContent = "Save Notes";
    setTimeout(() => {
      if (notesSaveStatus) notesSaveStatus.textContent = "";
    }, 3000);
  }
});
