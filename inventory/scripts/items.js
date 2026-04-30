import { esc } from "./index.js";

const { ajax_url, nonce } = window.ajax_inventory;

// ─── Delete the unit ──────────────────────────────────────────────────────────
document.querySelectorAll(".items-delete-btn").forEach((btn) => {
  btn.addEventListener("click", async () => {
    const id = btn.dataset.id;
    const sku = btn.dataset.sku;
    if (!confirm(`Delete unit ${sku}? This cannot be undone.`)) return;

    const body = new FormData();
    body.append("action", "mji_delete_item");
    body.append("nonce", nonce);
    body.append("id", id);

    const res = await fetch(ajax_url, { method: "POST", body });
    const json = await res.json();

    if (json.success) {
      btn.closest("tr").remove();
    } else {
      alert(json.data?.message ?? "Delete failed.");
    }
  });
});

// ─── Media picker inside edit/add ─────────────────────────────────────────────
const pickBtn = document.getElementById("items-pick-image");
const removeBtn = document.getElementById("items-remove-image");
const imageIdInput = document.getElementById("image_id");
const imagePreview = document.getElementById("items-image-preview");

if (pickBtn) {
  let mediaFrame;

  pickBtn.addEventListener("click", () => {
    if (mediaFrame) {
      mediaFrame.open();
      return;
    }
    // Configuring wordpress media object to use it
    mediaFrame = wp.media({
      title: "Select Unit Image",
      button: { text: "Use this image" },
      multiple: false,
    });

    // What we want to do when the image is selected
    mediaFrame.on("select", () => {
      const attachment = mediaFrame.state().get("selection").first().toJSON();
      // Setting up the image id to be saved on database when save button is clicked
      imageIdInput.value = attachment.id;
      imagePreview.src = attachment.sizes?.thumbnail?.url ?? attachment.url;
      imagePreview.style.display = "block";
      removeBtn.style.display = "inline-block";
    });
    mediaFrame.open();
  });
}

if (removeBtn) {
  removeBtn.addEventListener("click", () => {
    imageIdInput.value = "";
    imagePreview.src = "";
    imagePreview.style.display = "none";
    removeBtn.style.display = "none";
  });
}

// ─── WC product search ───────────────────────────────────────────────────────
const searchInput = document.getElementById("wc_product_search");
const hiddenInput = document.getElementById("wc_product_id");
const resultsBox = document.getElementById("wc-product-results");
const clearBtn = document.getElementById("items-clear-wc");
let debounceTimer;

if (searchInput) {
  searchInput.addEventListener("input", () => {
    clearTimeout(debounceTimer);
    const term = searchInput.value.trim();
    if (term.length < 2) {
      resultsBox.style.display = "none";
      return;
    }
    debounceTimer = setTimeout(() => fetchProducts(term), 300);
  });

  document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
      resultsBox.style.display = "none";
    }
  });
}

async function fetchProducts(term) {
  const url = new URL(ajax_url);
  url.searchParams.set("action", "mji_search_wc_products");
  url.searchParams.set("nonce", nonce);
  url.searchParams.set("term", term);

  const res = await fetch(url);
  const json = await res.json();
  if (!json.success || !json.data.length) {
    resultsBox.style.display = "none";
    return;
  }

  resultsBox.innerHTML = json.data
    .map(
      (p) =>
        `<div class="items-wc-result" data-id="${esc(String(p.id))}" data-text="${esc(p.text)}">${esc(p.text)}</div>`,
    )
    .join("");
  resultsBox.style.display = "block";

  resultsBox.querySelectorAll(".items-wc-result").forEach((row) => {
    row.addEventListener("click", () => {
      hiddenInput.value = row.dataset.id;
      searchInput.value = row.dataset.text;
      resultsBox.style.display = "none";
      if (clearBtn) clearBtn.style.display = "inline-block";
    });
  });
}

if (clearBtn) {
  clearBtn.addEventListener("click", () => {
    hiddenInput.value = "";
    searchInput.value = "";
    clearBtn.style.display = "none";
    resultsBox.style.display = "none";
  });
}

// ─── SELECT2  ─────────────────────────────────────────────────────────────────
jQuery(document).ready(function ($) {
  $(".supplier-select").select2({
    tags: true,
    placeholder: "Select or type supplier name",
    allowClear: true,
  });
  $(".brand-select").select2({
    tags: true,
    placeholder: "Select or type to create brand",
    allowClear: true,
  });
  $(".model-select").select2({
    tags: true,
    placeholder: "Select or type to create model",
    allowClear: true,
  });
  $(".items-select2-multi").select2({
    placeholder: "Select collections",
    allowClear: true,
  });
});

// ─── Status change modal ──────────────────────────────────────────────────────
const changeStatusBtn = document.getElementById("items-change-status-btn");
const statusModal = document.getElementById("items-status-modal");
const modalConfirm = document.getElementById("items-modal-confirm");
const modalCancel = document.getElementById("items-modal-cancel");
const modalError = document.getElementById("items-modal-error");

if (changeStatusBtn) {
  changeStatusBtn.addEventListener("click", () => {
    statusModal.style.display = "flex";
  });

  modalCancel.addEventListener("click", () => {
    statusModal.style.display = "none";
    modalError.style.display = "none";
  });

  modalConfirm.addEventListener("click", async () => {
    const body = new FormData();
    body.append("action", "update_unit_status");
    body.append("nonce", nonce);
    body.append("unit_id", changeStatusBtn.dataset.unitId);
    body.append("status", document.getElementById("items-modal-status").value);
    body.append("date", document.getElementById("items-modal-date").value);
    body.append("notes", document.getElementById("items-modal-notes").value);
    body.append(
      "password",
      document.getElementById("items-modal-password").value,
    );

    const res = await fetch(ajax_url, { method: "POST", body });
    const json = await res.json();

    if (json.success) {
      location.reload();
    } else {
      modalError.textContent = json.data?.message ?? "Failed.";
      modalError.style.display = "block";
    }
  });
}
