import isValidEmail, { isValidPostalCode } from "../utils/index.js";

const RECAPTCHA_SITE_KEY = "6LdYiK0sAAAAAMkeKv_yJ9YDzca3i8kP04gmcojA";

// Lazy-loads the reCAPTCHA script for pages where it isn't already eager-loaded
// server-side (the site-wide contact modal, as opposed to the dedicated
// contact/customize pages) - see conditional_recaptcha_script() in functions.php.
let recaptchaRequested = false;
function loadRecaptcha() {
  if (recaptchaRequested || window.grecaptcha) return;
  recaptchaRequested = true;
  const script = document.createElement("script");
  script.src = `https://www.google.com/recaptcha/api.js?render=${RECAPTCHA_SITE_KEY}`;
  document.head.appendChild(script);
}

// CONTACT US PAGE
const contactUsFormContainer = document.querySelector(
  ".contact-form-container",
);
if (contactUsFormContainer) {
  const contactUsForm = contactUsFormContainer.querySelector("#contactUsForm");
  const firstName = contactUsFormContainer.querySelector("#first-name");
  const firstNameError = contactUsFormContainer.querySelector("#firstNameError");
  const lastName = contactUsFormContainer.querySelector("#last-name");
  const lastNameError = contactUsFormContainer.querySelector("#lastNameError");
  const preferredContact = contactUsFormContainer.querySelector("#preferred-contact");
  const preferredContactError = contactUsFormContainer.querySelector("#preferredContactError");
  const storeSelection = contactUsFormContainer.querySelector("#store-selection");
  const storeSelectionError = contactUsFormContainer.querySelector("#storeSelectionError");
  const email = contactUsFormContainer.querySelector("#email");
  const emailError = contactUsFormContainer.querySelector("#emailError");
  const phone = contactUsFormContainer.querySelector("#phone");
  const phoneError = contactUsFormContainer.querySelector("#phoneError");
  const street = contactUsFormContainer.querySelector("#street");
  const streetError = contactUsFormContainer.querySelector("#streetError");
  const city = contactUsFormContainer.querySelector("#city");
  const cityError = contactUsFormContainer.querySelector("#cityError");
  const province = contactUsFormContainer.querySelector("#province");
  const provinceError = contactUsFormContainer.querySelector("#provinceError");
  const postalCode = contactUsFormContainer.querySelector("#postalCode");
  const postalCodeError = contactUsFormContainer.querySelector("#postalCodeError");
  const country = contactUsFormContainer.querySelector("#country");
  const countryError = contactUsFormContainer.querySelector("#countryError");
  const message = contactUsFormContainer.querySelector("#message");
  const messageError = contactUsFormContainer.querySelector("#messageError");
  const terms = contactUsFormContainer.querySelector("#terms");
  const termsError = contactUsFormContainer.querySelector("#termsError");
  const serverError = contactUsFormContainer.querySelector("#serverError");
  const contactSuccess = contactUsFormContainer.querySelector("#contactSuccess");
  const submitBtn = contactUsFormContainer.querySelector("#send-message");

  preferredContact.addEventListener("change", () => {
    storeSelection.classList.toggle("hidden", preferredContact.value !== "store");
  });

  contactUsForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const allErrorElements = contactUsFormContainer.querySelectorAll(".error");
    allErrorElements.forEach((el) => el.classList.add("hidden"));
    serverError.innerHTML = "";

    let errors = 0;

    const checkedStore = contactUsFormContainer.querySelector(
      "input[name='preferredStore']:checked",
    );

    const validations = [
      {
        field: firstName,
        errorEl: firstNameError,
        validate: () => firstName.value.trim() !== "",
      },
      {
        field: lastName,
        errorEl: lastNameError,
        validate: () => lastName.value.trim() !== "",
      },
      {
        field: preferredContact,
        errorEl: preferredContactError,
        validate: () => preferredContact.value.trim() !== "",
      },
      {
        field: storeSelection,
        errorEl: storeSelectionError,
        validate: () => preferredContact.value !== "store" || checkedStore !== null,
      },
      {
        field: email,
        errorEl: emailError,
        validate: () => isValidEmail(email.value.trim()),
      },
      {
        field: phone,
        errorEl: phoneError,
        validate: () => phone.value.length == 10,
      },
      {
        field: city,
        errorEl: cityError,
        validate: () => city.value.trim() !== "",
      },
      {
        field: province,
        errorEl: provinceError,
        validate: () => province.value.trim() !== "",
      },
      {
        field: postalCode,
        errorEl: postalCodeError,
        validate: () => postalCode.value.trim() === "" || isValidPostalCode(postalCode.value.trim()),
      },
      {
        field: country,
        errorEl: countryError,
        validate: () => country.value.trim() !== "",
      },
      {
        field: message,
        errorEl: messageError,
        validate: () => message.value.trim() !== "",
      },
      {
        field: terms,
        errorEl: termsError,
        validate: () => terms.checked,
      },
    ];

    validations.forEach((rule) => {
      if (!rule.validate()) {
        rule.errorEl.classList.remove("hidden");
        errors++;
      }
    });

    if (errors < 1) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Sending…";

      grecaptcha.ready(async () => {
        const token = await grecaptcha.execute(
          RECAPTCHA_SITE_KEY,
          { action: "contact_us" },
        );
        const data = new FormData(contactUsForm);
        data.set("terms", terms.checked ? "1" : "0");
        data.set("action", "contact_us");
        data.set("product_url", window.location.href);
        data.set("g-recaptcha-response", token);

        fetch(ajax_object_another.ajax_url, {
          method: "POST",
          body: data,
        })
          .then((res) => res.json())
          .then((res) => {
            if (res.success) {
              contactSuccess.style.display = "flex";
              contactUsForm.style.display = "none";
            } else {
              submitBtn.disabled = false;
              submitBtn.textContent = "Send Message";
              if (res.data.message) {
                serverError.innerHTML = `<li>${res.data.message}</li>`;
              } else {
                let result = "";
                res.data.errors.forEach((el) => {
                  result += `<li>${el}</li>`;
                });
                serverError.innerHTML = result;
                serverError.style.display = "block";
              }
            }
          })
          .catch((err) => {
            submitBtn.disabled = false;
            submitBtn.textContent = "Send Message";
            console.log("err", err);
          });
      });
    }
  });
}

// Call Us modal (product pages)
const callOverlay = document.getElementById("call-us-overlay");
if (callOverlay) {
  const openBtns = document.querySelectorAll(".open-call-modal");
  const closeBtns = callOverlay.querySelectorAll(".call-us-close");

  const openCall = () => {
    callOverlay.removeAttribute("hidden");
    document.body.style.overflow = "hidden";
  };

  const closeCall = () => {
    callOverlay.setAttribute("hidden", "");
    document.body.style.overflow = "";
  };

  openBtns.forEach((btn) => btn.addEventListener("click", openCall));
  closeBtns.forEach((btn) => btn.addEventListener("click", closeCall));

  callOverlay.addEventListener("click", (e) => {
    if (e.target === callOverlay) closeCall();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !callOverlay.hasAttribute("hidden")) closeCall();
  });
}

const jewelleryMap = {
  Bracelets: `
    <option value=""></option>
    <option value="Fiore">Fiore</option>
    <option value="Tennis">Tennis</option>
  `,
  Earrings: `
    <option value=""></option>
    <option value="Fiore">Fiore</option>
    <option value="Gioia">Gioia</option>
  `,
  "Engagement Rings": `
    <option value="C">C</option>
    <option value="Halo">Halo</option>
  `,
  "Pendants & Necklace": `
    <option value=""></option>
    <option value="Ballerina">Ballerina</option>
    <option value="Cleopatra">Cleopatra</option>
    <option value="Fiore">Fiore</option>
    <option value="Jean">Jean</option>
    <option value="Luna">Luna</option>
    <option value="Orchid">Orchid</option>
    <option value="Rombo">Rombo</option>
    <option value="Tondo">Tondo</option>
    <option value="Tu Sei">Tu Sei</option>
  `,
  Rings: `
    <option value=""></option>
    <option value="C">C</option>
    <option value="Cleopatra">Cleopatra</option>
    <option value="Halo">Halo</option>
    <option value="Honeycomb">Honeycomb</option>
    <option value="Orchid">Orchid</option>
  `,
  "Wedding Bands": `
    <option value=""></option>
    <option value="Honeycomb">Honeycomb</option>
  `,
};

// -------------------------------------------------------
// Contact Us Modal (shop / product-category pages)
// -------------------------------------------------------
const contactModal = document.getElementById("contact-modal-overlay");

if (contactModal) {
  const openBtns = document.querySelectorAll(".open-contact-modal");
  const closeBtns = contactModal.querySelectorAll(".contact-modal-close");
  const headerTitle = contactModal.querySelector(".contact-modal-header span");
  const defaultTitle = headerTitle ? headerTitle.textContent : "Contact Us";
  const inquiryTypeInput = contactModal.querySelector("#contact-inquiry-type");
  const modalForm = contactModal.querySelector("#contactUsForm");
  const modalSuccess = contactModal.querySelector("#contactSuccess");

  const openModal = (trigger = null) => {
    loadRecaptcha();
    if (headerTitle) {
      headerTitle.textContent = trigger?.dataset?.modalTitle || defaultTitle;
    }
    if (inquiryTypeInput) {
      inquiryTypeInput.value = trigger?.dataset?.inquiryType || "contact";
    }
    contactModal.removeAttribute("hidden");
    document.body.style.overflow = "hidden";
    const firstFocusable = contactModal.querySelector(
      "button, input, select, textarea, [href]",
    );
    if (firstFocusable) firstFocusable.focus();
  };

  const closeModal = () => {
    contactModal.setAttribute("hidden", "");
    document.body.style.overflow = "";
    if (headerTitle) headerTitle.textContent = defaultTitle;
    if (modalForm) {
      modalForm.reset();
      modalForm.style.display = "";
      modalForm.querySelectorAll(".error").forEach((el) => el.classList.add("hidden"));
      modalForm.querySelector("#serverError").innerHTML = "";
      const submitBtn = modalForm.querySelector("#send-message");
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = "Send Message";
      }
    }
    if (modalSuccess) modalSuccess.style.display = "";
  };

  openBtns.forEach((btn) => btn.addEventListener("click", () => openModal(btn)));
  closeBtns.forEach((btn) => btn.addEventListener("click", closeModal));

  contactModal.addEventListener("click", (e) => {
    if (e.target === contactModal) closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !contactModal.hasAttribute("hidden")) {
      closeModal();
    }
  });
}

// Customize Contact US PAGE
const customizeContainer = document.querySelector(".customize-container");

if (customizeContainer) {
  const copy = customizeContainer.querySelector(".copy");

  const customizeForm = customizeContainer.querySelector("#customize-form");

  const title = customizeContainer.querySelector("#title");

  const firstName = customizeContainer.querySelector("#firstName");
  const firstNameError = customizeContainer.querySelector("#firstNameError");

  const lastName = customizeContainer.querySelector("#lastName");
  const lastNameError = customizeContainer.querySelector("#lastNameError");

  const phone = customizeContainer.querySelector("#phone");
  const phoneError = customizeContainer.querySelector("#phoneError");

  const email = customizeContainer.querySelector("#email");
  const emailError = customizeContainer.querySelector("#emailError");

  const preferredContact = customizeContainer.querySelector("#preferred-contact");
  const preferredContactError = customizeContainer.querySelector("#preferredContactError");
  const storeSelection = customizeContainer.querySelector("#store-selection");
  const storeSelectionError = customizeContainer.querySelector("#storeSelectionError");

  const jewelleryPiece = customizeContainer.querySelector("#jewelleryPiece");
  const jewelleryPieceError = customizeContainer.querySelector("#jewelleryPieceError");

  const montecristoPiece = customizeContainer.querySelector("#montecristoPiece");

  const material = customizeContainer.querySelector("#material");

  const gemstone = customizeContainer.querySelector("#gemstone");

  const inspiration = customizeContainer.querySelector("#inspiration");
  const inspirationError = customizeContainer.querySelector("#inspirationError");

  const terms = customizeContainer.querySelector("#terms");
  const termsError = customizeContainer.querySelector("#termsError");

  const serverError = customizeContainer.querySelector("#serverError");
  const customize_nonce = customizeContainer.querySelector("#customize_nonce");

  const customizeSuccess = customizeContainer.querySelector("#customizeSuccess");
  const customizeSubmitBtn = customizeContainer.querySelector("#sendMessage");

  jewelleryPiece.addEventListener("change", (e) => {
    montecristoPiece.innerHTML = jewelleryMap[jewelleryPiece.value];
  });

  preferredContact.addEventListener("change", () => {
    storeSelection.classList.toggle("hidden", preferredContact.value !== "store");
  });

  customizeForm.addEventListener("submit", (e) => {
    e.preventDefault();

    firstNameError.classList.add("hidden");
    lastNameError.classList.add("hidden");
    emailError.classList.add("hidden");
    phoneError.classList.add("hidden");
    preferredContactError.classList.add("hidden");
    storeSelectionError.classList.add("hidden");
    jewelleryPieceError.classList.add("hidden");
    inspirationError.classList.add("hidden");
    termsError.classList.add("hidden");
    serverError.innerHTML = "";

    let errors = 0;

    if (!firstName.value.trim()) {
      errors++;
      firstNameError.classList.remove("hidden");
    }
    if (!lastName.value.trim()) {
      lastNameError.classList.remove("hidden");
      errors++;
    }
    if (phone.value.length != 10) {
      phoneError.classList.remove("hidden");
      errors++;
    }
    if (!isValidEmail(email.value.trim())) {
      emailError.classList.remove("hidden");
      errors++;
    }
    if (!preferredContact.value) {
      preferredContactError.classList.remove("hidden");
      errors++;
    }
    if (preferredContact.value === "store") {
      const checkedStore = customizeContainer.querySelector(
        "input[name='preferredStore']:checked",
      );
      if (!checkedStore) {
        storeSelectionError.classList.remove("hidden");
        errors++;
      }
    }
    if (!jewelleryPiece.value) {
      jewelleryPieceError.classList.remove("hidden");
      errors++;
    }
    if (!inspiration.value.trim()) {
      inspirationError.classList.remove("hidden");
      errors++;
    }
    if (!terms.checked) {
      termsError.classList.remove("hidden");
      errors++;
    }

    if (errors < 1) {
      customizeSubmitBtn.disabled = true;
      customizeSubmitBtn.textContent = "Sending…";

      grecaptcha.ready(async () => {
        const token = await grecaptcha.execute(
          RECAPTCHA_SITE_KEY,
          { action: "customize_contact_us" },
        );

        const checkedStore = customizeContainer.querySelector(
          "input[name='preferredStore']:checked",
        );

        let data = new FormData();
        data.append("g-recaptcha-response", token);
        data.append("title", title.value);
        data.append("firstName", firstName.value);
        data.append("lastName", lastName.value);
        data.append("phone", phone.value);
        data.append("email", email.value);
        data.append("preferredContact", preferredContact.value);
        data.append("preferredStore", checkedStore ? checkedStore.value : "");
        data.append("montecristoPiece", montecristoPiece.value);
        data.append("jewelleryPiece", jewelleryPiece.value);
        data.append("material", material.value);
        data.append("gemstone", gemstone.value);
        data.append("inspiration", inspiration.value);
        data.append("product_url", window.location.href);
        data.append("action", "customize_contact_us");
        data.append("customize_nonce", customize_nonce.value);
        data.append("honeypot", document.querySelector("#website")?.value ?? "");

        fetch(ajax_object_another.ajax_url, {
          method: "POST",
          body: data,
        })
          .then((res) => res.json())
          .then((res) => {
            if (res.success) {
              customizeSuccess.style.display = "flex";
              customizeForm.style.display = "none";
              copy.style.display = "none";
            } else {
              customizeSubmitBtn.disabled = false;
              customizeSubmitBtn.textContent = "Send Message";
              if (res.data.message) {
                serverError.innerHTML = `<li>${res.data.message}</li>`;
              } else {
                let result = "";
                res.data.errors.forEach((el) => {
                  result += `<li>${el}</li>`;
                });
                serverError.innerHTML = result;
              }
            }
          })
          .catch((err) => {
            customizeSubmitBtn.disabled = false;
            customizeSubmitBtn.textContent = "Send Message";
            console.log("err", err);
          });
      });
    }
  });
}
