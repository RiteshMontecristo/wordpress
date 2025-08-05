// FOR SEARCH
const headerSearchBtn = document.querySelector("#headerSearchBtn");
const searchForm = document.querySelector("#searchForm");
const searchField = document.querySelector(
  "#woocommerce-product-search-field-0"
);

headerSearchBtn?.addEventListener("click", () => {
  searchForm.classList.toggle("hidden");
  searchField.focus();
});


// FOR MOBILE MENU
const mobileMenuButton = document.querySelector("#site-navigation-menu-toggle");
const mobilePrimaryNavigationContainer = document.querySelector(
  "#mobilePrimaryNavigationContainer"
);
const mobileMenuCloseBtn = document.querySelector("#mobileMenuCloseBtn");
const primaryNavigationSubMenu = document.querySelectorAll(
  ".menu-item-has-children"
);

mobileMenuButton?.addEventListener("click", () => {
  mobilePrimaryNavigationContainer.classList.toggle("hidden");
  mobilePrimaryNavigationContainer.setAttribute(
    "aria-expanded",
    mobilePrimaryNavigationContainer.getAttribute("aria-expanded") === "true"
      ? "false"
      : "true"
  );
});

mobileMenuCloseBtn?.addEventListener("click", (e) => {
  mobilePrimaryNavigationContainer.classList.toggle("hidden");
  mobilePrimaryNavigationContainer.setAttribute("aria-expanded", "false");
});

// opening the menus
primaryNavigationSubMenu.forEach((navigationEl) => {
  navigationEl.addEventListener("click", function (e) {
    this.classList.toggle("open");
    e.stopPropagation();

    primaryNavigationSubMenu.forEach((secondNavEl) => {
      if (!secondNavEl.contains(this)) {
        secondNavEl.classList.remove("open");
      }
    });
  });
});