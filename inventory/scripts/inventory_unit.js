const inventoryTable = document.querySelector("#inventory-units-table");

const inventoryUnitModal = document?.querySelector("#inventory_unit_modal");
const addUnitModal = document?.querySelector("#open_add_modal");
const editUnitModal = inventoryTable?.querySelectorAll(".edit-unit");
const saveModal = inventoryUnitModal?.querySelector("#modal_save");
const cancelModal = inventoryUnitModal?.querySelector("#modal_cancel");

const unitIdModal = inventoryUnitModal?.querySelector("#modal_unit_id");
const productIdModal = inventoryUnitModal?.querySelector("#modal_product_id");
const skuModal = inventoryUnitModal?.querySelector("#modal_sku");
const variantModal = inventoryUnitModal?.querySelector("#variationID");
const serialModal = inventoryUnitModal?.querySelector("#modal_serial");
const locationModal = inventoryUnitModal?.querySelector("#location");
const supplierModal = inventoryUnitModal?.querySelector("#supplierID");
const invoiceNumberModal = inventoryUnitModal?.querySelector(
  "#modal_invoice_number"
);
const invoiceDateModal = inventoryUnitModal?.querySelector(
  "#modal_invoice_date"
);
const costPriceModal = inventoryUnitModal?.querySelector("#modal_cost_price");
const trueCostModal = inventoryUnitModal?.querySelector("#modal_true_cost");
const retailPriceModal = inventoryUnitModal?.querySelector(
  "#modal_retail_price"
);
const notesModal = inventoryUnitModal?.querySelector("#modal_notes");

// Edit status
const editStatusModal = document?.querySelector("#edit-status-modal");
const editStatusButtons = inventoryTable?.querySelectorAll(".edit-status");
const editStatusProductID = editStatusModal?.querySelector("#product-id");
const editStatusUnitID = editStatusModal?.querySelector("#unit-id");
const editStatusHeading = editStatusModal?.querySelector("#sku");
const editStatusEl = editStatusModal?.querySelector("#status");
const editStatusDate = editStatusModal?.querySelector("#updateDate");
const editStatusNote = editStatusModal?.querySelector("#notes");
const editStatusPassword = editStatusModal?.querySelector("#password");
const editStatusForm = editStatusModal?.querySelector("form#statusForm");
const editStatusUpdateBtn = editStatusModal?.querySelector("#confirm");
const editStatusCancelBtn = editStatusModal?.querySelector("#cancel");
const editStatusError = editStatusModal?.querySelector("#error");

addUnitModal?.addEventListener("click", (e) => {
  e.preventDefault();
  resetModal();
  inventoryUnitModal.style.display = "block";
});

cancelModal?.addEventListener("click", (e) => {
  e.preventDefault();
  inventoryUnitModal.style.display = "none";
});

saveModal?.addEventListener("click", (e) => {
  e.preventDefault();

  saveModal.setAttribute("disabled", true);
  const errorDiv = document.querySelector("#modal_error_message");
  errorDiv.style.display = "none"; // Reset

  const unitId = unitIdModal?.value || "";
  const productId = productIdModal?.value || "";
  const sku = skuModal?.value || "";
  const variant = variantModal?.value || "";
  const serial = serialModal?.value || "";
  const location = locationModal?.value || "";
  const supplier = supplierModal?.value || "";
  const invoiceNumber = invoiceNumberModal?.value || "";
  const invoiceDate = invoiceDateModal?.value || "";
  const trueCost = trueCostModal?.value || "";
  const costPrice = costPriceModal?.value || "";
  const retailPrice = retailPriceModal?.value || "";
  const notes = notesModal?.value || "";

  let errors = [];
  if (!productId) errors.push("Product ID is required.");
  if (!sku) errors.push("SKU is required.");
  if (!location) errors.push("Location is required.");
  if (!supplier) errors.push("Supplier is required.");
  if (!invoiceNumber) errors.push("Invoice Number is required.");
  if (!invoiceDate) errors.push("Invoice Date is required.");
  if (!costPrice) errors.push("Cost Price is required.");
  if (!trueCost) errors.push("True Cost is required.");
  if (!retailPrice) errors.push("Retail Price is required.");

  if (errors.length > 0) {
    errorDiv.innerHTML = errors.join("<br>");
    errorDiv.style.display = "block";
    saveModal.removeAttribute("disabled");
    return;
  }

  const formData = new FormData();
  formData.append("product_id", productId);
  formData.append("sku", sku);
  formData.append("location", location);
  formData.append("supplier", supplier);
  formData.append("invoice_number", invoiceNumber);
  formData.append("invoice_date", invoiceDate);
  formData.append("true_cost", trueCost);
  formData.append("cost_price", costPrice);
  formData.append("retail_price", retailPrice);
  serial && formData.append("serial", serial);
  notes && formData.append("notes", notes);
  variant && formData.append("variationID", variant);
  formData.append("action", "create_inventory_units");

  if (unitId) {
    formData.append("unit_id", unitId);
  }

  fetch(`${ajax_inventory.ajax_url}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((res) => {
      if (res.success) {
        if (unitId) {
          alert("Unit updated successfully!");
        } else {
          alert("Unit added successfully!");
        }
        window.location.reload();
      } else {
        if (Array.isArray(res.data.errors)) {
          errorDiv.innerHTML = res.data.errors.join("<br>");
        } else {
          errorDiv.innerHTML = res.data.errors;
        }
        errorDiv.style.display = "block";
        saveModal.removeAttribute("disabled");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
});

// Select searchbox
jQuery(document).ready(function ($) {
  $(".supplier-select").select2({
    tags: true, // allows user to add new supplier
    placeholder: "Select or type supplier name",
    allowClear: true,
  });
});

// Change the price when different variation selected due to different cost and retail price
function updatePricesFromVariation() {
  const selectedOption = variantModal.options[variantModal.selectedIndex];
  const retail = selectedOption.dataset.retail;
  const cost = selectedOption.dataset.cost;

  if (retail) retailPriceModal.value = retail;
  if (cost) {
    trueCostModal.value = cost;
    costPriceModal.value = cost;
  }
}
variantModal?.addEventListener("change", updatePricesFromVariation);

editUnitModal?.forEach((button) => {
  button.addEventListener("click", function (event) {
    event.preventDefault();
    inventoryUnitModal.style.display = "block";
    const tr = this.closest("tr");
    const unitData = {
      productId: tr.dataset.productId,
      unitId: tr.dataset.unitId,
      sku: tr.dataset.sku,
      variant: tr.dataset.variant,
      serial: tr.dataset.serial,
      location: tr.dataset.location,
      supplier: tr.dataset.supplier,
      invoiceNumber: tr.dataset.invoiceNumber,
      invoiceDate: tr.dataset.invoiceDate,
      costPrice: tr.dataset.costPrice,
      trueCost: tr.dataset.trueCost,
      retailPrice: tr.dataset.retailPrice,
      notes: tr.dataset.notes,
    };

    // Populate modal inputs
    unitIdModal.value = unitData.unitId || "";
    productIdModal.value = unitData.productId || "";
    skuModal.value = unitData.sku || "";
    if (variantModal) {
      variantModal.value = unitData.variant || "";
    }
    serialModal.value = unitData.serial || "";
    locationModal.value = unitData.location || "";
    supplierModal.value = unitData.supplier || "";
    jQuery(supplierModal).trigger("change"); // need to use jquery to change the modal value
    invoiceNumberModal.value = unitData.invoiceNumber || "";
    invoiceDateModal.value = unitData.invoiceDate || "";
    costPriceModal.value = unitData.costPrice || "";
    trueCostModal.value = unitData.trueCost || "";
    retailPriceModal.value = unitData.retailPrice || "";
    notesModal.value = unitData.notes || "";
  });
});

function resetModal() {
  unitIdModal.value = "";
  skuModal.value = "";
  serialModal.value = "";
  locationModal.value = "1";
  supplierModal.value = "";
  invoiceNumberModal.value = "";
  notesModal.value = "";
}

// Edit status
editStatusButtons?.forEach((button) => {
  button.addEventListener("click", (e) => {
    e.preventDefault();
    const tr = button.closest("tr");
    const unitId = tr.dataset.unitId;
    const sku = tr.dataset.sku;
    const status = tr.dataset.status;
    const notes = tr.dataset.notes;
    const product_id = tr.dataset.variant
      ? tr.dataset.variant
      : tr.dataset.productId;

    editStatusModal.classList.remove("hidden");

    editStatusProductID.value = product_id;
    editStatusUnitID.value = unitId;
    editStatusHeading.textContent = sku;
    editStatusEl.value = status;
    editStatusDate.valueAsDate = new Date();
    editStatusNote.value = notes;
  });
});

editStatusCancelBtn?.addEventListener("click", (e) => {
  e.preventDefault();
  editStatusModal.classList.add("hidden");
});

editStatusForm?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(editStatusForm);
  editStatusUpdateBtn.setAttribute("disabled", true);
  editStatusError.classList.add("hidden");
  try {
    const data = await fetch(`${ajax_inventory.ajax_url}`, {
      method: "POST",
      body: formData,
    });
    const resData = await data.json();
    if (resData.success) {
      alert("Status updated successfully!");
      window.location.reload();
    } else {
      editStatusError.innerText = resData.data;
      editStatusError.classList.remove("hidden");
    }
    editStatusUpdateBtn.removeAttribute("disabled");
  } catch (err) {
    console.log(err);
    editStatusError.innerText = err;
    editStatusError.classList.remove("hidden");
    editStatusUpdateBtn.removeAttribute("disabled");
  }
});
