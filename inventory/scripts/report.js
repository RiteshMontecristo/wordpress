document
  .getElementById("exportInventory")
  ?.addEventListener("click", function () {
    const table = document.getElementById("inventoryTable");
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.table_to_sheet(table);

    // Append footer totals (moved out of <tfoot> into a separate #reportTotals element)
    const footer = document.getElementById("reportTotals");
    if (footer) {
      if (footer.tagName === "TABLE") {
        // Financial report: footer is a full table — append its rows directly
        XLSX.utils.sheet_add_dom(ws, footer, { origin: -1 });
      } else {
        // All other reports: footer is a <div> with <p> tags or plain text
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
      "inventory_report_" + new Date().toISOString().slice(0, 10) + ".xlsx",
    );

    // const rows = table.querySelectorAll("tr");
    // const csv = [];

    // rows.forEach(function (row) {
    //   const cols = row.querySelectorAll("th, td");
    //   const rowData = [];
    //   cols.forEach(function (col) {
    //     const text = col.innerText.trim();
    //     rowData.push('"' + text + '"');
    //   });
    //   csv.push(rowData.join(","));
    // });

    // const csvString = csv.join("\n");
    // const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
    // const link = document.createElement("a");
    // link.href = URL.createObjectURL(blob);
    // link.download =
    //   "inventory_report_" + new Date().toISOString().slice(0, 10) + ".csv";
    // link.click();
  });

document
  .getElementById("printInventory")
  ?.addEventListener("click", function () {
    const report = document.getElementById("report");
    const printWindow = window.open("", "_blank");
    printWindow.document.write(`
        <html>
            <head>
              <title>Layaway Receipt</title>
              <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 0;
                        margin: auto;
                        font-size: 10px;
                    }
                    h2, h3 {
                        margin: 0;
                        padding: 0;
                    }
                    table {
                        font-size: 10px;
                        border-collapse: collapse;
                        width: 100%;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                </style>
            </head>
            <body>
              ${report.outerHTML}
            </body>
        </html>
    `);
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  });
