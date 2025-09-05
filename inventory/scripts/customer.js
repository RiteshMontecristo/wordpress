const customerForm = document.querySelector("form[name='customer']");
const editCustomerForm = document.querySelector("form[name='edit_customer']");

// Form fields for adding or editing a customer
const firstName = customerForm?.querySelector("#firstName");
const lastName = customerForm?.querySelector("#lastName");
const phone = customerForm?.querySelector("#phone");
const address = customerForm?.querySelector("#address");
const city = customerForm?.querySelector("#city");
const province = customerForm?.querySelector("#province");
const postalCode = customerForm?.querySelector("#postalCode");
const country = customerForm?.querySelector("#country");
const customerCta = customerForm?.querySelector("#customer_cta");

function checkFormValidity() {
  return (
    firstName.value.trim() !== "" &&
    lastName.value.trim() !== "" &&
    phone.value.trim() !== "" &&
    address.value.trim() !== "" &&
    city.value.trim() !== "" &&
    province.value.trim() !== "" &&
    postalCode.value.trim() !== "" &&
    country.value.trim() !== ""
  );
}

function checkPostalCodeValidity() {
  const canadaPostalRegex =
    /^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z][ ]?[0-9][ABCEGHJ-NPRSTV-Z][0-9]$/;
  return canadaPostalRegex.test(postalCode.value.trim());
}

customerForm?.addEventListener("submit", (e) => {
  e.preventDefault();

  if (!checkFormValidity()) {
    alert("Please fill in all required fields");
  } else if (!checkPostalCodeValidity()) {
    alert(
      "Postal code is not valid. Please enter a valid Canadian postal code."
    );
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
