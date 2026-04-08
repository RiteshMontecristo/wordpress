import isValidEmail, { isValidPostalCode } from "../utils/index.js";

// CONTACT US PAGE
const contactUsFormContainer = document.querySelector(
  ".contact-form-container",
);
if (contactUsFormContainer) {
  // Form and the form values
  const contactUsForm = contactUsFormContainer.querySelector("#contactUsForm");
  const firstName = contactUsFormContainer.querySelector("#first-name");
  const firstNameError =
    contactUsFormContainer.querySelector("#firstNameError");
  const lastName = contactUsFormContainer.querySelector("#last-name");
  const lastNameError = contactUsFormContainer.querySelector("#lastNameError");
  const preferredContact =
    contactUsFormContainer.querySelector("#preferred-contact");
  const preferredContactError =
    contactUsFormContainer.querySelector("#preferred-contact");
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
  const postalCodeError =
    contactUsFormContainer.querySelector("#postalCodeError");
  const country = contactUsFormContainer.querySelector("#country");
  const countryError = contactUsFormContainer.querySelector("#countryError");
  const message = contactUsFormContainer.querySelector("#message");
  const messageError = contactUsFormContainer.querySelector("#messageError");
  const terms = contactUsFormContainer.querySelector("#terms");
  const termsError = contactUsFormContainer.querySelector("#termsError");
  const serverError = contactUsFormContainer.querySelector("#serverError");

  const contactSuccess =
    contactUsFormContainer.querySelector("#contactSuccess");

  contactUsForm.addEventListener("submit", (e) => {
    e.preventDefault();

    // Hide all errors
    const allErrorElements = contactUsFormContainer.querySelectorAll(".error");
    allErrorElements.forEach((el) => el.classList.add("hidden"));
    serverError.innerHTML = "";

    let errors = 0;

    // Define validation rules
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
        field: street,
        errorEl: streetError,
        validate: () => street.value.trim() !== "",
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
        validate: () => isValidPostalCode(postalCode.value.trim()),
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
      grecaptcha.ready(async () => {
        const token = await grecaptcha.execute(
          "6LdYiK0sAAAAAMkeKv_yJ9YDzca3i8kP04gmcojA",
          { action: "contact_us" },
        );
        const data = new FormData(contactUsForm);
        data.set("terms", terms.checked ? "1" : "0");
        data.set("action", "contact_us");
        data.set("g-recaptcha-response", token);

        fetch(ajax_object_another.ajax_url, {
          method: "POST",
          body: data,
        })
          .then((res) => {
            return res.json();
          })
          .then((res) => {
            if (res.success) {
              contactSuccess.style.display = "flex";
              contactUsForm.style.display = "none";
            } else {
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
            console.log("err", err);
          });
      });
    }
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

// Customize Contact US PAGE
const customizeContainer = document.querySelector(".customize-container");

if (customizeContainer) {
  const copy = customizeContainer.querySelector(".copy");

  // Form and the form values
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

  const jewelleryPiece = customizeContainer.querySelector("#jewelleryPiece");
  const jewelleryPieceError = customizeContainer.querySelector(
    "#jewelleryPieceError",
  );

  const montecristoPiece =
    customizeContainer.querySelector("#montecristoPiece");

  const material = customizeContainer.querySelector("#material");

  const gemstone = customizeContainer.querySelector("#gemstone");

  const inspiration = customizeContainer.querySelector("#inspiration");
  const inspirationError =
    customizeContainer.querySelector("#inspirationError");

  const terms = customizeContainer.querySelector("#terms");
  const termsError = customizeContainer.querySelector("#termsError");

  const serverError = customizeContainer.querySelector("#serverError");
  const customize_nonce = customizeContainer.querySelector("#customize_nonce");

  const customizeSuccess =
    customizeContainer.querySelector("#customizeSuccess");

  jewelleryPiece.addEventListener("change", (e) => {
    montecristoPiece.innerHTML = jewelleryMap[jewelleryPiece.value];
  });

  customizeForm.addEventListener("submit", (e) => {
    e.preventDefault();

    firstNameError.classList.add("hidden");
    lastNameError.classList.add("hidden");
    emailError.classList.add("hidden");
    phoneError.classList.add("hidden");
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
      grecaptcha.ready(async () => {
        const token = await grecaptcha.execute(
          "6LeQyf4qAAAAAAWm4dRwb-HQ55gjfYYzwVNMIZMI",
          { action: "customize_us" },
        );

        let data = new FormData();
        data.append("g-recaptcha-response", token);
        data.append("title", title.value);
        data.append("firstName", firstName.value);
        data.append("lastName", lastName.value);
        data.append("phone", phone.value);
        data.append("email", email.value);
        data.append("montecristoPiece", montecristoPiece.value);
        data.append("jewelleryPiece", jewelleryPiece.value);
        data.append("material", material.value);
        data.append("gemstone", gemstone.value);
        data.append("inspiration", inspiration.value);
        data.append("action", "customize_contact_us");
        data.append("customize_nonce", customize_nonce.value);

        fetch(ajax_object_another.ajax_url, {
          method: "POST",
          body: data,
        })
          .then((res) => {
            return res.json();
          })
          .then((res) => {
            if (res.success) {
              customizeSuccess.style.display = "flex";
              customizeForm.style.display = "none";
              copy.style.display = "none";
            } else {
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
            console.log("err", err);
          });
      });
    }
  });
}
