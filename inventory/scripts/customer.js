const customerForm = document.querySelector("form[name='customer']");
const editCustomerForm = document.querySelector("form[name='edit_customer']");

// Form fields for adding or editing a customer
const firstName = customerForm?.querySelector("#firstName");
const lastName = customerForm?.querySelector("#lastName");
const address = customerForm?.querySelector("#address");
const city = customerForm?.querySelector("#city");
const province = customerForm?.querySelector("#province");
const postalCode = customerForm?.querySelector("#postalCode");
const country = customerForm?.querySelector("#country");
const customerCta = customerForm?.querySelector("#customer_cta");

function checkFormValidity() {
  return firstName.value.trim() !== "" && lastName.value.trim() !== "";
}

function checkPostalCodeValidity() {
  const input = postalCode.value.trim();

  const usZipRegex = /^\d{5}(-\d{4})?$/;

  const canadaPostalRegex =
    /^[ABCEGHJ-NPRSTVXY]\d[ABCEGHJ-NPRSTV-Z]\s*\d[ABCEGHJ-NPRSTV-Z]\d$/i;

  return usZipRegex.test(input) || canadaPostalRegex.test(input);
}

customerForm?.addEventListener("submit", (e) => {
  e.preventDefault();

  if (!checkFormValidity()) {
    alert("Please fill in all required fields");
  } else {
    customerCta.disabled = true;
    customerCta.textContent = "Submitting...";
    customerForm.submit();
  }
});

// View Profile Switcher
document.querySelectorAll(".nav-tab").forEach((tab) => {
  tab.addEventListener("click", (e) => {
    e.preventDefault();
    document
      .querySelectorAll(".nav-tab")
      .forEach((t) => t.classList.remove("nav-tab-active"));
    document
      .querySelectorAll(".tab-content")
      .forEach((c) => (c.style.display = "none"));
    tab.classList.add("nav-tab-active");
    document.querySelector(tab.getAttribute("href")).style.display = "block";
  });
});
