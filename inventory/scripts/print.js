document.addEventListener("DOMContentLoaded", function () {
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) {
      if (device && device.name) {
        console.log("Default Printer Found:", device.name);
        // Store the device object for later use
        window.printerDevice = device;
      } else {
        console.error("No default printer found.");
      }
    },
    function (error) {
      console.error("Error detecting printer:", error);
    }
  );
});

let test = "EXORT";

const zplCommands = `
  ^XA
  
  ^CF0,20
  ^FO70, 20^A0N,20,20 ^FD${test}:X34B-1^FS
  ^FO70,50^FDSKU Number:^FS
  ^FO70,80^FDPrice:^FS
  ^FO70,110^FDBrand:^FS
  
  ^CF0,20
  ^FO240,20^FDModel^FS
  
  ^XZ
  `;

const printBtn = document.getElementById("print-button");

printBtn?.addEventListener("click", printProductInfo);

function printProductInfo(e) {
  if (window.printerDevice) {
    window.printerDevice.send(
      zplCommands,
      function (response) {
        console.log("Print successful", response);
      },
      function (error) {
        alert("Error printing: " + error);
      }
    );
  } else {
    alert("No default printer found.");
  }
}

const printCard = document.getElementById("card-print");

printCard?.addEventListener("click", (e) => {
  e.preventDefault();
  const title = document.getElementById("title")?.value;
  const image = document.querySelector("#print-section img")?.outerHTML;
  const sku = document.querySelector("#sku_text")?.value.split(" ");

  // Display attributes thats not a variant for both variant and non variant products
  const regular_attribute = document?.getElementById("regular-attribute");
  const parsedRegularAttribute = JSON.parse(regular_attribute.value);

  let displayAttribute = "";

  parsedRegularAttribute.forEach((el) => {
    const entries = Object.entries(el);
    // Loop through entries (each object has one key-value pair)
    entries.forEach(([key, value]) => {
      displayAttribute += `<p>${key}: ${value}</p>`;
    });
  });

  let variableEl = "";
  const variable = document.getElementById("variable")?.value;

  // FOR VARIANT CASES
  if (variable) {
    const decodedValue = variable
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, "&");

    const parsedVariable = JSON.parse(decodedValue);
    const foundEl = parsedVariable.find((el) => el.id == sku[1]);

    variableEl += `<p>Price: $${foundEl.price}</p>`;

    for (const [key, value] of Object.entries(foundEl.attributes)) {
      variableEl += `<p>${key}: ${value}</p>`;
    }
  } else {
    const price = document.getElementById("price")?.value;
    price && (variableEl += `<p>Price: $${price}</p>`);
  }

  const printWindow = window.open("", "", "width=800,height=600");
  printWindow.document.open();
  printWindow.document.write(`  <html>
                  <head>
                      <title>Print Product</title>
                      <style>
                      @media print {
                        @page {
                          margin: 0;
                          size: auto;
                        }
                        body { 
                          font-family: Arial, sans-serif; 
                          padding:0 !important; 
                          margin:0 !important; 
                        }
                        body > div { 
                          display: grid; 
                          // grid-template-rows: repeat(2, 50%); 
                          grid-template-rows: 50% 1fr;
                        }
                        #card-section { 
                          height: 576px;
                          width: 384px;
                          overflow: hidden;
                          padding: 5px 10px;
                        }
                        #card-section div {
                          // transform: rotate(90deg);
                          width: 384px;
                        }
                        #card-section div:last-child{
                          display: flex;
                          flex-direction: column;
                          gap: 5px;
                          text-align: left; 
                          align-items: start;
                          width: fit-content;
                          font-size: 12px;
                          margin-left: 16px;
                        }
                        #card-section h2 { margin: 0; padding:0; font-size: 16px; }
                        #card-section p { margin: 0; }
                        img { max-width:100%; max-height: 100%; object-fit:cover; }
                      }
                      </style>
                  </head>
                  <body>
                      <div id="card-section">
                        <div>
                          ${image}
                        </div>
                        <div>
                          <h2>${title}</h2>
                          ${displayAttribute}
                          SKU: ${sku[0]}
                          ${variableEl}
                        </div>
                      </div>
                  </body>
                  </html>
              `);

  printWindow.print();
  printWindow.document.close();
});
