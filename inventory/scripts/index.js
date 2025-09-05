import "./print.js";
import "./inventory_unit.js";

if (window.location.href.includes("/admin.php?page=inventory-management")) {
  import("./inventory.js").catch((error) =>
    console.error("Error loading inventory module:", error)
  );
}

if (window.location.href.includes("/admin.php?page=customer-management")) {
  import("./customer.js").catch((error) =>
    console.error("Error loading inventory module:", error)
  );
}
if (window.location.href.includes("/admin.php?page=reports-management")) {
  import("./report.js").catch((error) =>
    console.error("Error loading inventory module:", error)
  );
}

// POS
const pos = document.querySelector(".pos");

if (pos) {
  const posSearchForm = pos.querySelector("#pos-search-form");
  const posEmailForm = pos.querySelector("#pos-email-form");
  const searchResult = pos.querySelector(".search-results");

  posSearchForm.addEventListener("submit", (e) => {
    searchResult.innerHTML = "";
    e.preventDefault();
    const formData = new FormData(posSearchForm);
    formData.append("action", "searchPosProducts");

    fetch(ajax_object_another.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => {
        return res.json();
      })
      .then((res) => {
        if (res.success) {
          searchResult.insertAdjacentHTML("beforeend", res.data.html);
          const posContainer = document.querySelectorAll(".pos-item");

          posContainer.forEach((el) => {
            const btn = el.querySelector("#pos-buy-btn");
            const productValue = el.querySelector("#pos-product");
            const title = el.querySelector("#title").textContent;
            btn.addEventListener("click", (e) => {
              if (productValue) {
                let element = `<div><input type="text" hidden name="products[]" value="${productValue.value}"> </input> <p>${title}<button>x</button></p>`;
                posEmailForm.insertAdjacentHTML("afterbegin", element);
              } else {
                let element = `<div><input type="text" hidden name="products[]" value="${el.dataset.sku}"> </input> <p>${title}<button>x</button></p>`;
                posEmailForm.insertAdjacentHTML("afterbegin", element);
              }
            });
          });
        } else {
          console.log("error happened");
        }
      })
      .catch((err) => {
        console.log(err);
      });
  });

  posEmailForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(posEmailForm);
    formData.append("action", "sendPosData");

    fetch(ajax_object_another.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((res) => {
        console.log(res);
      });

    for (let [key, value] of formData) {
      console.log(key, value);
    }
  });
}
