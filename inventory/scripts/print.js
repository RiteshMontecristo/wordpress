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
    },
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

const printBtn = document.querySelector("#print-button");

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
      },
    );
  } else {
    alert("No default printer found.");
  }
}

const printCardBtn = document?.querySelector("#card-print");
const printProductImg = document?.querySelector("#print-product-image");
const printDesc = document?.querySelector("#print-desc");
const printSku = document?.querySelector("#print-sku");
const printSerial = document?.querySelector("#print-serial");
const printPrice = document?.querySelector("#print-price");
const skuOption = document?.querySelector("#sku_option");

skuOption?.addEventListener("change", (e) => {
  const selectedOption = e.target.selectedOptions[0];
  const sku = e.target.value;
  const serial = selectedOption.dataset.serial;
  const price = selectedOption.dataset.price;
  const desc = selectedOption.dataset.desc;
  const imgSrc = selectedOption.dataset.imgSrc;

  printProductImg?.setAttribute("src", imgSrc);
  printDesc.innerHTML = desc;
  printSku.textContent = "SKU: " + sku;
  printSerial.textContent = "Serial: " + serial;
  printPrice.textContent = "$" + price;
});

printCardBtn?.addEventListener("click", (e) => {
  e.preventDefault();

  const printCardSection = document.querySelector(
    "#print-card-section",
  ).innerHTML;
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
                      ${printCardSection}
                  </body>
                  </html>
              `);

  printWindow.print();
  printWindow.document.close();
});
