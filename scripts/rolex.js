// Menu
const menuButton = document.getElementById("menu-button");
const mobileMenu = document.getElementById("mobileMenuContainer");
const menuIcon = document.getElementById("menu-icon");
let rotateIcon = true;

menuButton.addEventListener("click", () => {
  mobileMenu.classList.toggle("menu-hide");
  if (rotateIcon) {
    menuIcon.style.transform = "rotate(180deg)";
  } else {
    menuIcon.style.transform = "";
  }
  rotateIcon = !rotateIcon;
});

// Back to top
const backToTop = document.getElementById("back-to-top");

backToTop.addEventListener("click", () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

// Opening Hours in the rolex store contact us page dropdown
const openingHoursBtn = document.querySelector("#openingHours");
if (openingHoursBtn) {
  const storeHours = document.querySelector("#storeHours");
  const icon = openingHoursBtn.querySelector("svg");
  let storeHoursHidden = "true";

  openingHoursBtn.addEventListener("click", () => {
    if (storeHoursHidden) {
      storeHours.style.display = "block";
      icon.style.transform = "rotate(-180deg)";
    } else {
      icon.style.transform = "rotate(0deg)";
      storeHours.style.display = "none";
    }
    storeHoursHidden = !storeHoursHidden;
  });
}

// Rolex form
document.addEventListener("DOMContentLoaded", () => {
  const multiStepForm = document.querySelector("#multiStepFormRolex");

  // Check if we are on the correct page before doing any of this
  if (multiStepForm) {
    // First Step
    const firstStep = document.querySelector("#firstStep");
    const textMessage = document.querySelector("#message");
    const textMessageError = document.querySelector("#messageError");
    const nextButton = document.querySelector("#nextButton");
    const nonce = document.querySelector('input[name="_wpnonce"]').value;

    // Second Step
    const secondStep = document.querySelector("#secondStep");
    const backButton = document.querySelector("#backButton");
    const title = document.querySelector("#title");
    const firstName = document.querySelector("#firstName");
    const firstNameLabel = document.querySelector("#firstNameLabel");
    const firstNameError = document.querySelector("#firstNameError");
    const lastName = document.querySelector("#lastName");
    const lastNameLabel = document.querySelector("#lastNameLabel");
    const lastNameError = document.querySelector("#lastNameError");
    const email = document.querySelector("#email");
    const emailLabel = document.querySelector("#emailLabel");
    const emailError = document.querySelector("#emailError");
    const code = document.querySelector("#code");
    const phone = document.querySelector("#phone");
    const country = document.querySelector("#country");
    const terms = document.querySelector("#terms");
    const checkboxSvg = document.querySelector("#checkbox-svg");
    const termsError = document.querySelector("#termsError");
    const sendBtn = document.querySelector("#submitButton");
    let formSubmitted = false;
    let errors = 0;

    // Third Step
    const thirdStep = document.querySelector("#thirdStep");

    terms.addEventListener("change", function () {
      if (this.checked) {
        checkboxSvg.innerHTML = `<g>
	<path class="st0" d="M7.5,1 M7.5,0C3.4,0,0,3.4,0,7.5S3.4,15,7.5,15S15,11.6,15,7.5S11.6,0,7.5,0L7.5,0z"/>
</g>
<path class="st1" d="M6,10.5L3.2,7.3l1.1-1l1.8,2.1l4.7-3.9l1,1.1L6,10.5z"/>`;
        checkboxSvg.classList.remove("svg-error");
      } else {
        checkboxSvg.innerHTML = `<g>
	<path class="st0" d="M7.5,1.5c3.3,0,6,2.7,6,6s-2.7,6-6,6s-6-2.7-6-6S4.2,1.5,7.5,1.5 M7.5,0C3.4,0,0,3.4,0,7.5S3.4,15,7.5,15
		S15,11.6,15,7.5S11.6,0,7.5,0L7.5,0z"/>
</g>`;
      }
    });

    nextButton.addEventListener("click", (e) => {
      if (textMessage.value.length < 1) {
        textMessageError.classList.remove("hidden");
      } else {
        textMessageError.classList.add("hidden");
        firstStep.style.display = "none";
        secondStep.style.display = "grid";
      }
    });

    backButton.addEventListener("click", (e) => {
      firstStep.style.display = "grid";
      secondStep.style.display = "none";
    });

    multiStepForm.addEventListener("submit", (e) => {
      e.preventDefault();
      if (formSubmitted) {
        return;
      }
      sendBtn.disabled = true;
      formSubmitted = true;
      errors = 0;

      // Checking the must have fields
      if (firstName.value.length < 1) {
        errors++;
        firstNameLabel.classList.add("error");
        firstNameError.classList.remove("hidden");
      } else {
        firstNameLabel.classList.remove("error");
        firstNameError.classList.add("hidden");
      }

      if (lastName.value.length < 1) {
        errors++;
        lastNameLabel.classList.add("error");
        lastNameError.classList.remove("hidden");
      } else {
        lastNameLabel.classList.remove("error");
        lastNameError.classList.add("hidden");
      }

      if (!isValidEmail(email.value)) {
        errors++;
        emailLabel.classList.add("error");
        emailError.classList.remove("hidden");
      } else {
        emailLabel.classList.remove("error");
        emailError.classList.add("hidden");
      }

      if (!terms.checked) {
        errors++;
        terms.classList.add("terms-error");
        termsError.classList.remove("hidden");
        checkboxSvg.classList.add("svg-error");
      } else {
        terms.classList.remove("terms-error");
        termsError.classList.add("hidden");
        checkboxSvg.classList.remove("svg-error");
      }

      // If none of the required fields are missing
      if (errors === 0) {
        grecaptcha.ready(async () => {
          const token = await grecaptcha.execute(
            "6LeQyf4qAAAAAAWm4dRwb-HQ55gjfYYzwVNMIZMI",
            { action: "rolex_contact_us" }
          );

          const formData = new FormData(multiStepForm);
          // letting wordpress know which hooks we need to run, in this case it will run wp_ajax_send_email and wp_ajax_nopriv_send_email for logged in and not logged in users respectively.
          formData.append("action", "send_email");
          formData.append("_wpnonce", nonce);
          formData.append("g-recaptcha-response", token);

          // the ajax_object.ajax_url is comming from the localize script that is hooked in our rolex enqueue script
          fetch(ajax_object.ajax_url, {
            method: "POST",
            body: formData, //uses the above action data to know which hook to run
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Handle success, maybe show a confirmation message
                secondStep.style.display = "none";
                thirdStep.style.display = "block";
                window._satellite?.track("contactForm");
              } else {
                console.log("Failed to send email.");
              }
            })
            .catch((error) => {
              console.error("Error:", error);
            })
            .finally(() => {
              sendBtn.disabled = false;
              formSubmitted = false;
            });
        });
      } else {
        sendBtn.disabled = false;
        formSubmitted = false;
      }
    });
  }
});

function isValidEmail(email) {
  // Define the regex pattern for email validation
  const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  // Test the email against the regex pattern
  return emailPattern.test(email);
}

// Contact Us Accordion
const accordionControls = document.getElementById("accordionControls");
const hideIcon = document.getElementById("hideIcon");
const showIcon = document.getElementById("showIcon");
const contactUs = document.getElementById("contactUs");

if (accordionControls) {
  accordionControls.addEventListener("click", () => {
    contactUs.classList.toggle("hidden");
    hideIcon.classList.toggle("hidden");
    showIcon.classList.toggle("hidden");
  });
}

// Keep Exploring
document.addEventListener("DOMContentLoaded", function () {
  // Check if we are on that page or not
  if (document.getElementById("keepExploring")) {
    var splide = new Splide("#keepExploring", {
      perPage: 4,
      lazyLoad: "nearby",
      breakpoints: {
        1025: {
          perPage: 3,
        },
        767: {
          perPage: 2,
        },
      },
    });
    splide.on("mounted", () => {
      // Make pagination buttons focusable
      document
        .querySelectorAll("#keepExploring .splide__pagination button")
        .forEach((btn) => btn.removeAttribute("tabindex"));
    });

    splide.mount();
  }

  if (document.getElementById("discover")) {
    let isMobile = window.matchMedia("(max-width: 767px)").matches;

    let splide = new Splide("#discover", {
      perPage: 1,
      lazyLoad: "false",
      drag: isMobile,
    });

    splide.on("mounted", () => {
      document
        .querySelectorAll("#discover .splide__pagination button")
        .forEach((btn) => btn.removeAttribute("tabindex"));
    });

    splide.mount();

    window.addEventListener("resize", () => {
      isMobile = window.matchMedia("(max-width: 767px)").matches;
      if (splide) {
        splide.destroy();
      }

      splide = new Splide("#discover", {
        perPage: 1,
        lazyLoad: "false",
        drag: isMobile,
      });

      splide.on("mounted", () => {
        document
          .querySelectorAll("#discover .splide__pagination button")
          .forEach((btn) => btn.removeAttribute("tabindex"));
      });

      splide.mount();
    });
  }
});

// Youtube video player
document.addEventListener("DOMContentLoaded", () => {
  const videoContainer = document.querySelectorAll("#youtubeVideoContainer");

  if (videoContainer) {
    videoContainer.forEach(function (placeholder) {
      placeholder.addEventListener("click", function () {
        const videoId = placeholder.getAttribute("data-video-id");
        const iframe = document.createElement("iframe");

        // Set the iframe attributes for the YouTube video
        iframe.setAttribute("width", "100%");
        iframe.setAttribute("height", "100%");
        iframe.setAttribute(
          "src",
          `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1`
        );
        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("allow", "autoplay; encrypted-media");
        iframe.setAttribute("allowfullscreen", true);

        // Replace the placeholder with the iframe
        placeholder.innerHTML = ""; // Remove the placeholder content
        placeholder.appendChild(iframe);
      });
    });
  }
});

// Load more Watch grid
const loadMore = document.getElementById("loadMore");

if (loadMore) {
  const watchesContainer = document.getElementById("watchesContainer");
  const term = watchesContainer.dataset.term;
  let page = 2;
  loadMore.addEventListener("click", () => {
    const data = {
      action: "load_more_products",
      page: page,
      term: term,
    };

    fetch(ajax_object.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams(data),
    })
      .then((response) => {
        return response.json();
      })
      .then((data) => {
        page++;
        watchesContainer.insertAdjacentHTML("beforeend", data.html);
        // if post remaining is greater than 6 that means there is still more products to be displayed
        if (data.posts_remaining <= 6) {
          loadMore.style.display = "none";
        }
      });
  });
}

// Watch cover
const watchCover = document.querySelector("#watch-cover-splide");

if (watchCover) {
  var splide = new Splide(watchCover, {
    type: "loop",
    arrows: false,
  }).mount();
}

// Watch Lightbox
const watchGallery = document.querySelectorAll(".watch-item");
const watchLightbox = document.querySelector("#watch-lightbox");
const closeLightbox = document.querySelector("#close-lightbox");

if (watchLightbox && watchGallery) {
  var splide = new Splide(watchLightbox, {
    type: "loop",
  }).mount();

  watchLightbox.classList.remove("is-initialized");

  watchGallery.forEach((el, index) => {
    el.addEventListener("click", () => {
      splide.options.speed = 0;
      splide.go(index);
      watchLightbox.classList.add("is-initialized");
      // Restore the original animation speed after navigation
      setTimeout(() => {
        splide.options.speed = 400; // Restore default speed
      }, 0);
    });
  });

  closeLightbox.addEventListener("click", () => {
    watchLightbox.classList.remove("is-initialized");
  });
}

// Key Selling Accordion
const keyPointsAccordion = document.querySelectorAll("#keyPointsAccordion");
if (keyPointsAccordion.length > 0) {
  let currentActive = 1;

  for (let i = 1; i <= keyPointsAccordion.length; i++) {
    const accordionBtn = document.querySelector(`#accordion-button-${i}`);

    accordionBtn.addEventListener("click", () => {
      if (currentActive === i) {
        accordionBtn.ariaExpanded = "false";
        currentActive = null;
      } else {
        if (currentActive !== null) {
          const previousButton = document.querySelector(
            `#accordion-button-${currentActive}`
          );
          previousButton.ariaExpanded = "false";
        }
        accordionBtn.ariaExpanded = "true";
        currentActive = i;
      }
    });
  }
}
