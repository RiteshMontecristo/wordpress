// ALL PRODUCTS SECTION
// ======================

const PAGE_PER_POST = 12;
let page = 0;
const sortby = document.getElementById("sortby");
const loadMoreProducts = document.getElementById("load-more");
const sidebar = document.getElementById("products-sidebar");
const filterContainer = document.getElementById("filter-container");
const mobileFilter = document.getElementById("mobile-filter");
const resetFilter = document.getElementById("reset-filters");
// Grabbing all the checkbox filter values
const allCategory = document.querySelectorAll('input[name="category[]"]');
const allType = document.querySelectorAll('input[name="type[]"]');
const allPrice = document.querySelectorAll('input[name="price[]"]');
const allTargetGroup = document.querySelectorAll(
  'input[name="target_group[]"]'
);
const allMaterial = document.querySelectorAll('input[name="materials[]"]');
const allGemstone = document.querySelectorAll('input[name="gemstone[]"]');
const allGift = document.querySelectorAll('input[name="gifts[]"]');
// to make only one checkbox selectable at a time for the category title brand
const categoryHeading = document.querySelector(".category h3");

// grabbing individual filter divs to hide and display the filters
const filterComponent = document.querySelectorAll(".filter");

document.addEventListener("DOMContentLoaded", function () {

  // Define mobile breakpoint (change as needed)
  const MOBILE_WIDTH = 768;

  function handleScreenSize() {
    if (window.innerWidth <= MOBILE_WIDTH) {
      sidebar?.classList.add("hidden");
    } else {
      sidebar?.classList.remove("hidden");
    }
  }

  // Run once on load
  handleScreenSize();

  // Optional: Run on window resize
  window.addEventListener("resize", handleScreenSize);
});

mobileFilter?.addEventListener("click", () => {
  sidebar.classList.remove("hidden");
});

// Run the provided function in the provided delay values.
function debounce(func, delay) {
  let timeout;

  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      func(...args);
    }, delay);
  };
}

// running the filterProducts after 300 miliseconds
const debouncedFunc = debounce(filterProducts, 300);

sidebar?.addEventListener("click", (e) => {
  const windoWidth = window.innerWidth;
  if (e.target.contains(filterContainer) && windoWidth < 768) {
    sidebar.classList.add("hidden");
  }
});
sortby?.addEventListener("change", debouncedFunc);

allCategory.forEach((el) => {
  el.addEventListener("change", (event) => {
    if (categoryHeading.textContent == "Brand") {
      allCategory.forEach((category) => {
        if (category !== event.target) {
          category.checked = false;
        }
      });
    }
    debouncedFunc();
  });
});

allType.forEach((el) => {
  el.addEventListener("change", debouncedFunc);
});

allPrice.forEach((el) => {
  el.addEventListener("change", debouncedFunc);
});

allTargetGroup.forEach((el) => {
  el.addEventListener("change", debouncedFunc);
});

allMaterial.forEach((el) => {
  el.addEventListener("change", debouncedFunc);
});

allGemstone.forEach((el) => {
  el.addEventListener("change", debouncedFunc);
});

allGift.forEach((el) => {
  el.addEventListener("change", debouncedFunc);
});

filterComponent.forEach((el) => {
  const h3 = el.querySelector("h3");
  const ul = el.querySelector("ul");

  h3?.addEventListener("click", (_) => {
    ul?.classList.toggle("hidden");
    el.classList.toggle("filter-hide");
  });
});

function getFilterValues() {
  let parentBrand = document.getElementById("data-selector")?.dataset?.brand;
  let categoryBrandCheckboxes;

  if (parentBrand === undefined || parentBrand === null || parentBrand === "") {
    parentBrand = document.querySelectorAll('input[name="category[]"]:checked');
  } else {
    categoryBrandCheckboxes = document.querySelectorAll(
      'input[name="category[]"]:checked'
    );
  }
  const typeCheckboxes = document.querySelectorAll(
    'input[name="type[]"]:checked'
  );
  const targetGroupCheckboxes = document.querySelectorAll(
    'input[name="target_group[]"]:checked'
  );
  const materialsCheckboxes = document.querySelectorAll(
    'input[name="materials[]"]:checked'
  );
  const gemstoneCheckboxes = document.querySelectorAll(
    'input[name="gemstone[]"]:checked'
  );
  const giftsCheckboxes = document.querySelectorAll(
    'input[name="gifts[]"]:checked'
  );
  const minPrice = document.getElementById("min-price")?.value;
  const maxPrice = document.getElementById("max-price")?.value;
  const orderby = sortby?.value;

  // Create an array to hold selected values
  const selectedBrandCategories = Array.from(categoryBrandCheckboxes).map(
    (checkbox) => checkbox.value
  );
  // Create an array to hold selected values
  const selectedTypeCategories = Array.from(typeCheckboxes).map(
    (checkbox) => checkbox.value
  );
  // Create an array to hold selected values
  const selectedTargetGroupCategories = Array.from(targetGroupCheckboxes).map(
    (checkbox) => checkbox.value
  );
  // Create an array to hold selected values
  const selectedMaterialsCategories = Array.from(materialsCheckboxes).map(
    (checkbox) => checkbox.value
  );
  // Create an array to hold selected values
  const selectedGemstoneCategories = Array.from(gemstoneCheckboxes).map(
    (checkbox) => checkbox.value
  );
  // Create an array to hold selected values
  const selectedGiftCategories = Array.from(giftsCheckboxes).map(
    (checkbox) => checkbox.value
  );

  const search = window.location.search;

  const searchQueryParams = new URLSearchParams(search);

  // Create a query string from the selected price and brand categories
  const queryParams = new URLSearchParams({
    brands: selectedBrandCategories.join(","),
    type: selectedTypeCategories.join(","),
    targetGroup: selectedTargetGroupCategories.join(","),
    materials: selectedMaterialsCategories.join(","),
    gemstone: selectedGemstoneCategories.join(","),
    gift: selectedGiftCategories.join(","),
    brand: parentBrand,
  });

  if (search) {
    queryParams.append("s", searchQueryParams.get("s"));
  }

  if (orderby !== "" && orderby !== null && orderby !== undefined) {
    queryParams.append("orderby", orderby);
  }
  if (minPrice !== "" && minPrice !== null && minPrice !== undefined) {
    queryParams.append("min_price", minPrice);
  }

  if (maxPrice !== "" && maxPrice !== null && maxPrice !== undefined) {
    queryParams.append("max_price", maxPrice);
  }

  return queryParams;
}

// Filter all products
function filterProducts() {
  page = 0;
  // Get all the values
  const queryParams = getFilterValues();

  queryParams.append("action", "filter_products");
  queryParams.append("page", page);

  // Update the URL or perform AJAX request
  fetch(`${ajax_object_another.ajax_url}?${queryParams}`, {
    method: "GET",
  })
    .then((response) => response.json())
    .then((data) => {
      const container = document.querySelector(".products.columns-3");
      container.innerHTML = data.html;

      if (data.total_products <= PAGE_PER_POST) {
        loadMoreProducts.style.display = "none";
      } else {
        loadMoreProducts.style.display = "block";
      }
    })
    .catch((error) => console.error("Error:", error));
}

resetFilter?.addEventListener("click", () => {
  // Reseting all the filter values and querying it again
  allCategory.forEach((el) => {
    el.checked = false;
  });
  allType.forEach((el) => {
    el.checked = false;
  });
  allTargetGroup.forEach((el) => {
    el.checked = false;
  });
  allMaterial.forEach((el) => {
    el.checked = false;
  });
  allGemstone.forEach((el) => {
    el.checked = false;
  });
  allGift.forEach((el) => {
    el.checked = false;
  });

  allPrice.forEach((el) => {
    el.value = "";
  });

  filterProducts();
});

loadMoreProducts?.addEventListener("click", (el) => {
  page++;
  const queryParams = getFilterValues();
  queryParams.append("action", "load_more");
  queryParams.append("page", page);

  // Update the URL or perform AJAX request
  fetch(`${ajax_object_another.ajax_url}?${queryParams}`, {
    method: "GET",
  })
    .then((response) => response.json())
    .then((data) => {
      const container = document.querySelector(".products.columns-3");
      container.insertAdjacentHTML("beforeend", data.html);

      if (data.total_products <= PAGE_PER_POST * (page + 1)) {
        loadMoreProducts.style.display = "none";
      } else {
        loadMoreProducts.style.display = "block";
      }
    })
    .catch((error) => console.error("Error:", error));
});

// Single Product Page
const isLoggedIn = document.body.classList.contains("logged-in");
const wishlist = document.getElementById("wishlist");

wishlist?.addEventListener("click", () => {
  wishlist.setAttribute("disabled", true);
  if (isLoggedIn) {
    fetch(ajax_object_another.ajax_url, {
      method: "POST",
      body: new URLSearchParams({
        productId: wishlist.dataset.product,
        userId: wishlist.dataset.user,
        favourite: wishlist.dataset.favourite,
        action: "toggleFavourite",
      }),
    })
      .then((res) => res.json())
      .then((res) => {
        wishlist.removeAttribute("disabled");
        if (res.success) {
          if (wishlist.dataset.favourite == "false") {
            wishlist.dataset.favourite = "true";
          } else {
            wishlist.dataset.favourite = "false";
          }
        }
      });
  } else {
    window.location.href = "/my-account";
  }
});
