// Track which column indices are excluded from export/print
const excludedColumns = new Set();

// ─── Column Toggle ────────────────────────────────────────────────────────────
function initColumnToggle() {
  const table = document.getElementById("inventoryTable");
  if (!table) return;

  const headers = table.querySelectorAll("thead tr th");
  if (!headers.length) return;

  headers.forEach((th, i) => {
    th.dataset.colIndex = i;
    th.title = "Click to exclude/include this column in export and print";
    th.addEventListener("click", () => toggleColumn(i));
  });

  // Inject Reset Columns button after the Export button
  const exportBtn = document.getElementById("exportInventory");
  if (exportBtn) {
    const resetBtn = document.createElement("button");
    resetBtn.id = "resetColumns";
    resetBtn.className = "button";
    resetBtn.textContent = "Reset Columns";
    resetBtn.style.cssText = "margin-bottom:10px; margin-left:8px; display:none;";
    resetBtn.addEventListener("click", resetColumns);
    exportBtn.parentNode.insertBefore(resetBtn, exportBtn.nextSibling);
  }
}

function toggleColumn(index) {
  const table = document.getElementById("inventoryTable");
  const th = table.querySelector(`th[data-col-index="${index}"]`);
  if (!th) return;

  if (excludedColumns.has(index)) {
    excludedColumns.delete(index);
    th.classList.remove("col-excluded");
  } else {
    excludedColumns.add(index);
    th.classList.add("col-excluded");
  }

  const resetBtn = document.getElementById("resetColumns");
  if (resetBtn) {
    resetBtn.style.display = excludedColumns.size > 0 ? "inline-block" : "none";
  }
}

function resetColumns() {
  const table = document.getElementById("inventoryTable");
  excludedColumns.forEach((i) => {
    const th = table.querySelector(`th[data-col-index="${i}"]`);
    if (th) th.classList.remove("col-excluded");
  });
  excludedColumns.clear();
  const resetBtn = document.getElementById("resetColumns");
  if (resetBtn) resetBtn.style.display = "none";
}

// ─── Grid Reconstruction (handles rowspan/colspan) ────────────────────────────

/**
 * Builds a 2D grid from a table, accounting for rowspan and colspan.
 * grid[rowIndex][colIndex] = { cell: HTMLElement, isOrigin: bool }
 * isOrigin = true for the top-left cell of a span (the actual DOM cell)
 */
function buildTableGrid(table) {
  const grid = [];

  table.querySelectorAll("tr").forEach((tr, rowIndex) => {
    if (!grid[rowIndex]) grid[rowIndex] = [];

    let colIndex = 0;
    tr.querySelectorAll("th, td").forEach((cell) => {
      // Advance past columns already occupied by rowspan from above rows
      while (grid[rowIndex][colIndex]) colIndex++;

      const rowspan = parseInt(cell.getAttribute("rowspan") || 1);
      const colspan = parseInt(cell.getAttribute("colspan") || 1);

      for (let r = 0; r < rowspan; r++) {
        for (let c = 0; c < colspan; c++) {
          const ri = rowIndex + r;
          const ci = colIndex + c;
          if (!grid[ri]) grid[ri] = [];
          grid[ri][ci] = { cell, isOrigin: r === 0 && c === 0 };
        }
      }

      colIndex += colspan;
    });
  });

  return grid;
}

// ─── Export ───────────────────────────────────────────────────────────────────

document
  .getElementById("exportInventory")
  ?.addEventListener("click", function () {
    const table = document.getElementById("inventoryTable");
    const wb = XLSX.utils.book_new();

    let ws;

    if (excludedColumns.size === 0) {
      // No columns excluded — use fast path
      ws = XLSX.utils.table_to_sheet(table);
    } else {
      // Build a 2D array skipping excluded columns
      const grid = buildTableGrid(table);
      const totalCols = grid[0] ? grid[0].length : 0;
      const data = [];

      grid.forEach((row) => {
        if (!row) return;
        const rowData = [];
        for (let c = 0; c < totalCols; c++) {
          if (excludedColumns.has(c)) continue;
          const entry = row[c];
          if (!entry) {
            rowData.push("");
          } else if (entry.isOrigin) {
            rowData.push(entry.cell.innerText.trim());
          } else {
            // Part of a span already accounted for — push empty to avoid duplication
            rowData.push("");
          }
        }
        data.push(rowData);
      });

      ws = XLSX.utils.aoa_to_sheet(data);
    }

    // Append footer totals
    const footer = document.getElementById("reportTotals");
    if (footer) {
      if (footer.tagName === "TABLE") {
        // Financial report: footer is a full table
        XLSX.utils.sheet_add_dom(ws, footer, { origin: -1 });
      } else {
        // All other reports: footer is a <div> with <p> tags
        const lines = Array.from(footer.querySelectorAll("p")).map((p) =>
          p.innerText.trim()
        );
        if (lines.length === 0 && footer.innerText.trim()) {
          lines.push(footer.innerText.trim());
        }
        XLSX.utils.sheet_add_aoa(ws, lines.map((l) => [l]), { origin: -1 });
      }
    }

    XLSX.utils.book_append_sheet(wb, ws, "Inventory");
    XLSX.writeFile(
      wb,
      "inventory_report_" + new Date().toISOString().slice(0, 10) + ".xlsx"
    );
  });

// ─── Print ────────────────────────────────────────────────────────────────────

/**
 * Builds a clean HTML table from the original table using grid data.
 * Strips all inline styles, sticky positioning, CSS classes, and background colors.
 * Skips excluded columns. Preserves rowspan attributes correctly.
 */
function buildCleanTable(originalTable) {
  const grid = buildTableGrid(originalTable);
  const totalCols = grid[0] ? grid[0].length : 0;

  let html = "<table>";
  let currentSection = null;

  grid.forEach((row, rowIndex) => {
    if (!row) return;

    // Row 0 = header, all others = body
    const section = rowIndex === 0 ? "thead" : "tbody";
    if (section !== currentSection) {
      if (currentSection) html += `</${currentSection}>`;
      html += `<${section}>`;
      currentSection = section;
    }

    html += "<tr>";
    for (let c = 0; c < totalCols; c++) {
      if (excludedColumns.has(c)) continue;

      const entry = row[c];
      if (!entry || !entry.isOrigin) continue; // hole or covered by rowspan — skip

      const cell = entry.cell;
      const tag = cell.tagName.toLowerCase(); // 'th' or 'td'
      const rowspan = parseInt(cell.getAttribute("rowspan") || 1);
      const rowspanAttr = rowspan > 1 ? ` rowspan="${rowspan}"` : "";
      const content = cell.innerHTML;
      html += `<${tag}${rowspanAttr}>${content}</${tag}>`;
    }
    html += "</tr>";
  });

  if (currentSection) html += `</${currentSection}>`;
  html += "</table>";
  return html;
}

document
  .getElementById("printInventory")
  ?.addEventListener("click", function () {
    const table = document.getElementById("inventoryTable");
    const reportHeader = document.querySelector("#report header")?.innerText.trim() || "";
    const footer = document.getElementById("reportTotals");
    const footerText = footer ? footer.innerText.trim() : "";

    const tableHTML = table ? buildCleanTable(table) : "";

    const printWindow = window.open("", "_blank");
    printWindow.document.write(`
        <html>
            <head>
              <title>Report</title>
              <style>
                body {
                  font-family: Arial, sans-serif;
                  padding: 16px;
                  margin: 0;
                  font-size: 10px;
                }
                .report-header {
                  margin-bottom: 12px;
                  white-space: pre-line;
                }
                table {
                  font-size: 10px;
                  border-collapse: collapse;
                  width: 100%;
                }
                th, td {
                  border: 1px solid #aaa;
                  padding: 6px 8px;
                  text-align: left;
                  vertical-align: top;
                }
                thead th {
                  background: #f1f1f1;
                }
                img {
                  max-width: 50px;
                  max-height: 50px;
                  display: block;
                }
                .report-footer {
                  margin-top: 12px;
                  padding: 8px;
                  border-top: 2px solid #555;
                  white-space: pre-line;
                }
              </style>
            </head>
            <body>
              ${reportHeader ? `<div class="report-header">${reportHeader}</div>` : ""}
              ${tableHTML}
              ${footerText ? `<div class="report-footer">${footerText}</div>` : ""}
            </body>
        </html>
    `);
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  });

// ─── Init ─────────────────────────────────────────────────────────────────────

initColumnToggle();
