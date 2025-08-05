import isValidEmail from "../utils/index.js";

// CONTACT US PAGE
const contactUsFormContainer = document.querySelector(
  ".contact-form-container"
);
if (contactUsFormContainer) {
  // Form and the form values
  const contactUsForm = contactUsFormContainer.querySelector("#contactUsForm");
  const title = contactUsFormContainer.querySelector("#title");
  const firstName = contactUsFormContainer.querySelector("#first-name");
  const firstNameError =
    contactUsFormContainer.querySelector("#firstNameError");
  const lastName = contactUsFormContainer.querySelector("#last-name");
  const lastNameError = contactUsFormContainer.querySelector("#lastNameError");
  const preferredContact =
    contactUsFormContainer.querySelector("#preferred-contact");
  const emailContainer = contactUsFormContainer.querySelector(".email-address");
  const email = contactUsFormContainer.querySelector("#email");
  const emailError = contactUsFormContainer.querySelector("#emailError");
  const phoneContainer = contactUsFormContainer.querySelector(".phone-number");
  const phone = contactUsFormContainer.querySelector("#phone");
  const phoneError = contactUsFormContainer.querySelector("#phoneError");
  const message = contactUsFormContainer.querySelector("#message");
  const messageError = contactUsFormContainer.querySelector("#messageError");
  const terms = contactUsFormContainer.querySelector("#terms");
  const termsError = contactUsFormContainer.querySelector("#termsError");
  const serverError = contactUsFormContainer.querySelector("#serverError");
  const contact_us_nonce =
    contactUsFormContainer.querySelector("#contact_us_nonce");

  const contactSuccess =
    contactUsFormContainer.querySelector("#contactSuccess");

  preferredContact.addEventListener("change", () => {
    if (preferredContact.value == "email") {
      emailContainer.style.display = "flex";
      phoneContainer.style.display = "none";
    } else {
      emailContainer.style.display = "none";
      phoneContainer.style.display = "flex";
    }
  });

  contactUsForm.addEventListener("submit", (e) => {
    e.preventDefault();

    firstNameError.classList.add("hidden");
    lastNameError.classList.add("hidden");
    emailError.classList.add("hidden");
    phoneError.classList.add("hidden");
    messageError.classList.add("hidden");
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

    if (preferredContact.value == "email") {
      if (!isValidEmail(email.value.trim())) {
        emailError.classList.remove("hidden");
        errors++;
      }
    } else {
      if (phone.value.length != 10) {
        phoneError.classList.remove("hidden");
        errors++;
      }
    }

    if (!message.value.trim()) {
      messageError.classList.remove("hidden");
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
          { action: "contact_us" }
        );
        let data = new FormData();

        data.append("title", title.value);
        data.append("lastName", lastName.value);
        data.append("firstName", firstName.value);
        data.append("preferredContact", preferredContact.value);
        data.append("message", message.value);
        data.append("action", "contact_us");
        data.append("terms", terms.checked);
        data.append("contact_us_nonce", contact_us_nonce.value);
        data.append("g-recaptcha-response", token);

        if (preferredContact.value === "email" && email.value) {
          data.append("email", email.value);
        } else if (preferredContact.value === "phone" && phone.value) {
          data.append("phone", phone.value);
        }

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
    "#jewelleryPieceError"
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
          { action: "customize_us" }
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
