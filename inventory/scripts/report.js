document
  .getElementById("exportInventory")
  .addEventListener("click", function () {
    const table = document.getElementById("inventoryTable");
    const rows = table.querySelectorAll("tr");
    const csv = [];

    rows.forEach(function (row) {
      const cols = row.querySelectorAll("th, td");
      const rowData = [];
      cols.forEach(function (col) {
        const text = col.innerText.trim();
        rowData.push('"' + text + '"');
      });
      csv.push(rowData.join(","));
    });

    const csvString = csv.join("\n");
    const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download =
      "inventory_report_" + new Date().toISOString().slice(0, 10) + ".csv";
    link.click();
  });

document
  .getElementById("printInventory")
  .addEventListener("click", function () {
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
