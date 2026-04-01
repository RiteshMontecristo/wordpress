import "./print.js";
import "./inventory_unit.js";

if (window.location.href.includes("/admin.php?page=inventory-management")) {
  import("./sales.js").catch((error) =>
    console.error("Error loading inventory module:", error),
  );
}

if (window.location.href.includes("/admin.php?page=customer-management")) {
  import("./customer.js").catch((error) =>
    console.error("Error loading inventory module:", error),
  );
}
if (window.location.href.includes("/admin.php?page=reports-management")) {
  import("./report.js").catch((error) =>
    console.error("Error loading inventory module:", error),
  );
}
if (window.location.href.includes("/admin.php?page=invoice-management")) {
  import("./find_invoice.js").catch((error) =>
    console.error("Error loading invoice module:", error),
  );
}

export function formatCurrency(amount) {
  return amount.toFixed(2);
}

export function formatLabel(input) {
  return input
    .split(/[^a-zA-Z0-9]+/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
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
