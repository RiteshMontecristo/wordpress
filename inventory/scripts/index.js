// Cache-busting query string, shared with every dynamically imported module below
// (and with sales.js/sales-module's own imports — see mji_inventory_scripts_version()
// in inventory/functions.php). Every relative import anywhere in inventory/scripts/
// must use this same value, or a file reachable via two different paths ends up as
// two separate module instances (e.g. one initialized, one not).
const V = window.ajax_inventory?.asset_version ?? "";
const qs = V ? `?v=${V}` : "";

import(`./print.js${qs}`);
import(`./inventory_unit.js${qs}`);

if (window.location.href.includes("/admin.php?page=inventory-management")) {
  import(`./sales.js${qs}`).catch((error) =>
    console.error("Error loading inventory module:", error),
  );
}

if (window.location.href.includes("/admin.php?page=customer-management")) {
  import(`./customer.js${qs}`).catch((error) =>
    console.error("Error loading inventory module:", error),
  );
}
if (window.location.href.includes("/admin.php?page=reports-management")) {
  import(`./report.js${qs}`).catch((error) =>
    console.error("Error loading inventory module:", error),
  );
}
if (window.location.href.includes("/admin.php?page=invoice-management")) {
  import(`./find_invoice.js${qs}`).catch((error) =>
    console.error("Error loading invoice module:", error),
  );
}
if (window.location.href.includes("/admin.php?page=items-management")) {
  import(`./items.js${qs}`).catch((error) =>
    console.error("Error loading items module:", error),
  );
}

document.addEventListener(
  "wheel",
  function (event) {
    if (document.activeElement.type === "number") {
      document.activeElement.blur(); // Or preventDefault() on the event target
    }
  },
  { passive: false },
);
