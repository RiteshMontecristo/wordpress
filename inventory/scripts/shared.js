export function formatCurrency(amount) {
  return amount.toFixed(2);
}

export function formatLabel(input) {
  return input
    .split(/[^a-zA-Z0-9]+/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

export function esc(str) {
  return String(str ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

export function formatBulletedDescription(str) {
  const cleaned = String(str ?? "").replace(/<br\s*\/?>/gi, " ");
  return esc(cleaned).split("•").join("<br />•");
}
