document.addEventListener("DOMContentLoaded", function () {
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) {
      if (device && device.name) {
        window.printerDevice = device;
      } else {
        console.error("No default printer found.");
      }
    },
    function (error) {
      console.error("Error detecting printer:", error);
    },
  );
});

// ZPL coordinates assume 203 DPI.
// ^LH sets a global label-home offset so all fields clear the non-printable edge.
// Left column (xL): SKU, Serial, Price.
// Right column (xR): Model, Spec1, Spec2.
// ^CI28 removed — it corrupts rendering on some firmware versions.
// ^FB removed from right column — caused overlapping artifacts.
function buildZpl(data) {
  const priceStr = "$" + parseFloat(data.price || 0).toFixed(2);
  const lines = [];
  const xL = 0;
  const xR = 170;  // moved left
  const step = 26;

  lines.push("^LH60,22");

  // Left column
  let yL = 0;
  lines.push(`^FO${xL},${yL}^CF0,22^FB160,1,0,L^FD${data.sku}^FS`); yL += step;
  if (data.serial) {
    lines.push(`^FO${xL},${yL}^CF0,22^FB160,1,0,L^FD${data.serial}^FS`);
    yL += step;
  }
  lines.push(`^FO${xL},${yL}^CF0,22^FD${priceStr}^FS`);

  // Right column — clipped in JS to prevent line-wrap overlap
  const clip = (s) => s && s.length > 18 ? s.slice(0, 18) : s;
  let yR = 0;
  if (data.model) {
    lines.push(`^FO${xR},${yR}^CF0,22^FD${clip(data.model)}^FS`); yR += step;
  }
  if (data.spec1) {
    lines.push(`^FO${xR},${yR}^CF0,22^FD${clip(data.spec1)}^FS`); yR += step;
  }
  if (data.spec2) {
    lines.push(`^FO${xR},${yR}^CF0,22^FD${clip(data.spec2)}^FS`);
  }

  return ["^XA", ...lines, "^XZ"].join("\n");
}

// Close any open print dropdowns when clicking outside
document.addEventListener("click", (e) => {
  if (!e.target.closest(".print-dropdown")) {
    document.querySelectorAll(".print-dropdown-menu").forEach((m) => { m.hidden = true; });
  }
});

// Shared handler — works for any table row or wrapper div that carries data-sku
function handlePrintClick(e) {
  // Toggle dropdown
  if (e.target.closest(".print-dropdown-toggle")) {
    const menu = e.target.closest(".print-dropdown").querySelector(".print-dropdown-menu");
    menu.hidden = !menu.hidden;
    return;
  }

  // Zebra Tag
  const zebraBtn = e.target.closest(".print-zebra-tag");
  if (zebraBtn) {
    if (!window.printerDevice) {
      alert("No Zebra printer found. Make sure the Zebra BrowserPrint desktop app is running.");
      return;
    }
    const d = zebraBtn.closest("[data-sku]").dataset;
    window.printerDevice.send(
      buildZpl({
        sku:    d.sku,
        serial: d.serial,
        price:  d.retailPrice,
        model:  d.modelName,
        spec1:  d['spec-1'],
        spec2:  d['spec-2'],
      }),
      function () {},
      function (err) { alert("Print error: " + err); },
    );
    zebraBtn.closest(".print-dropdown-menu").hidden = true;
    return;
  }

  // Card print
  const cardBtn = e.target.closest(".print-card");
  if (cardBtn) {
    const d = cardBtn.closest("[data-sku]").dataset;
    const price = "$" + parseFloat(d.retailPrice || 0).toFixed(2);
    const printWindow = window.open("", "", "width=500,height=700");
    printWindow.document.open();
    printWindow.document.write(`<html>
<head>
  <title>Print Product Card</title>
  <style>
    @media print {
      @page { margin: 0; size: auto; }
      body { padding: 0; margin: 0; }
    }
    body { font-family: Arial, sans-serif; padding: 10px; }
    #card { width: 384px; }
    img { max-width: 100%; max-height: 200px; object-fit: cover; display: block; margin-bottom: 8px; }
    p { margin: 4px 0; font-size: 13px; }
  </style>
</head>
<body>
  <div id="card">
    ${d.imageUrl ? `<img src="${d.imageUrl}" />` : ""}
    <p>${d.description || ""}</p>
    <p>SKU: ${d.sku || ""}</p>
    ${d.serial ? `<p>Serial: ${d.serial}</p>` : ""}
    <p>${price}</p>
  </div>
  <script>window.addEventListener('load', function() { window.print(); window.close(); });<\/script>
</body>
</html>`);
    printWindow.document.close();
    cardBtn.closest(".print-dropdown-menu").hidden = true;
    return;
  }
}

// Inventory units metabox table (WC product edit page)
document.querySelector("#inventory-units-table")?.addEventListener("click", handlePrintClick);

// Items management list table
document.querySelector("#items-list-table")?.addEventListener("click", handlePrintClick);

// Items management view / edit page (single-unit print wrapper)
document.querySelector("#items-unit-print")?.addEventListener("click", handlePrintClick);

