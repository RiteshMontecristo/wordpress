const inventoryTable = document.querySelector("#inventory-units-table");
const addInventoryUnit = document.querySelector("#addNewInventoryUnit");
const addUnitButton = addInventoryUnit?.querySelector("#addUnit");
const editUnitButton = inventoryTable?.querySelectorAll(".edit-unit");
const saveUnitButton = inventoryTable?.querySelectorAll(".save-unit");

addUnitButton?.addEventListener("click", function (event) {
  event.preventDefault();
  const productID = addInventoryUnit.dataset.productId;
  const sku = addInventoryUnit.querySelector("#sku").value;
  const status = addInventoryUnit.querySelector("#status").value;
  const variationID = addInventoryUnit.querySelector("#variationID")?.value;
  const locationID = addInventoryUnit.querySelector("#locationID").value;

  if (!sku) {
    alert("Please enter SKU as it is a required fields.");
    return;
  }

  const formData = new FormData();
  formData.append("action", "create_inventory_units");
  formData.append("product_id", productID);
  formData.append("sku", sku);
  formData.append("status", status);
  formData.append("locationID", locationID);
  variationID && formData.append("variationID", variationID);

  fetch(`${ajax_inventory.ajax_url}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((res) => {
      if (res.success) {
        alert("Unit updated successfully!");
        location.reload();
      } else {
        alert(res.data);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
});

editUnitButton?.forEach((button) => {
  button.addEventListener("click", function (event) {
    event.preventDefault();
    const tr = this.closest("tr");
    tr.querySelector(".edit-unit").style.display = "none";
    tr.querySelector(".save-unit").style.display = "inline-block";

    tr.querySelectorAll(".editable-cell").forEach((cell) => {
      const fieldType = cell.getAttribute("data-field");
      const currentValue = cell.dataset.value.trim();

      let newElement, cloneNode;

      switch (fieldType) {
        case "sku":
          newElement = document.createElement("input");
          newElement.type = "text";
          newElement.id = "sku";
          newElement.value = currentValue;
          break;

        case "status":
          const addStatusHTML = addInventoryUnit.querySelector("#status");
          cloneNode = addStatusHTML.cloneNode(true);
          break;

        case "location":
          const addLocationHTML = addInventoryUnit.querySelector("#locationID");
          cloneNode = addLocationHTML.cloneNode(true);
          break;

        case "variant":
          const addVariationtionHTML =
            addInventoryUnit.querySelector("#variationID");
          cloneNode = addVariationtionHTML.cloneNode(true);
          break;

        default:
          newElement = null;
      }

      if (newElement) {
        cell.textContent = "";
        cell.appendChild(newElement);
      }
      if (cloneNode) {
        cell.textContent = "";
        cloneNode.value = currentValue;
        cell.appendChild(cloneNode);
      }
    });
  });
});

saveUnitButton?.forEach((button) => {
  button.addEventListener("click", function (e) {
    e.preventDefault();
    const tr = this.closest("tr");

    const unitId = tr.dataset.unitId;
    const productId = tr.dataset.productId;
    const sku = tr.querySelector("#sku").value;
    const status = tr.querySelector("#status").value;
    const variationID = tr.querySelector("#variationID")?.value;
    const locationID = tr.querySelector("#locationID").value;

    const formData = new FormData();
    formData.append("action", "update_inventory_units");
    formData.append("unitId", unitId);
    formData.append("productId", productId);
    formData.append("sku", sku);
    formData.append("status", status);
    formData.append("variationID", variationID);
    formData.append("locationID", locationID);

    fetch(`${ajax_inventory.ajax_url}`, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          alert("Unit updated successfully!");
          location.reload();
        } else {
          alert(res.data);
        }
        console.log(res);
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  });
});
