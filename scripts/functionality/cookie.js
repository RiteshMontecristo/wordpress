const customerFirstTime = !localStorage.getItem("cookie-banner-setting-done");
const cookieOverlay = document.querySelector(".cookie-overlay");
const preferenceContainer = document.querySelector(".preference-container");
const cookieInfo = document.getElementById("cookieInfo");
const preferenceBtn = document.getElementById("preference");
const declineAllBtn = document.getElementById("declineAll");
const acceptAllBtn = document.getElementById("acceptAll");
const acceptBtn = document.getElementById("accept");
const statisticsCheckbox = document.getElementById("statistics");
const marketingCheckbox = document.getElementById("marketing");
const rolexCheckbox = document.getElementById("rolex");

const cookie = document.getElementById("cookie");

window.dataLayer = window.dataLayer || [];
function gtag() {
  dataLayer.push(arguments);
}

const getCustomerFirstTime = () =>
  !localStorage.getItem("cookie-banner-setting-done");

// show the cookie overlay if customer hasnt dealt with it after 1 sec
const showBanner = () => {
  cookieOverlay.classList.remove("hidden");
  // cookieOverlay.style.display = "flex";
  cookieOverlay.focus();
};

const acceptAllCookies = () => {
  if (getCustomerFirstTime())
    localStorage.setItem("cookie-banner-setting-done", "true");
  document.cookie = `rlx-consent=true; path=/; max-age=31536000`;
  document.cookie = `gtm_ad_storage=granted; path=/; max-age=31536000`;
  document.cookie = `gtm_analytics_storage=granted; path=/; max-age=31536000`;

  gtag("consent", "update", {
    analytics_storage: "granted",
    ad_storage: "granted",
    ad_user_data: "granted",
    ad_personalization: "granted",
  });
  gtmConsentUpdated();
  cookieOverlay.classList.add("hidden");
};

const declineAllCookies = () => {
  if (getCustomerFirstTime())
    localStorage.setItem("cookie-banner-setting-done", "true");
  document.cookie = `rlx-consent=false; path=/; max-age=31536000`;
  document.cookie = `gtm_ad_storage=denied; path=/; max-age=31536000`;
  document.cookie = `gtm_analytics_storage=denied; path=/; max-age=31536000`;

  gtag("consent", "update", {
    analytics_storage: "denied",
    ad_storage: "denied",
    ad_user_data: "denied",
    ad_personalization: "denied",
  });
  gtmConsentUpdated();
  cookieOverlay.classList.add("hidden");
};

const acceptPartialCookies = () => {
  if (getCustomerFirstTime())
    localStorage.setItem("cookie-banner-setting-done", "true");
  const rolexConsent = rolexCheckbox ? rolexCheckbox.checked : false;

  document.cookie = `rlx-consent=${rolexConsent}; path=/; max-age=31536000`;
  document.cookie = `gtm_ad_storage=${
    marketingCheckbox.checked ? "granted" : "denied"
  }; path=/; max-age=31536000`;
  document.cookie = `gtm_analytics_storage=${
    statisticsCheckbox.checked ? "granted" : "denied"
  }; path=/; max-age=31536000`;

  gtag("consent", "update", {
    ad_storage: marketingCheckbox.checked ? "granted" : "denied",
    ad_user_data: marketingCheckbox.checked ? "granted" : "denied",
    ad_personalization: marketingCheckbox.checked ? "granted" : "denied",
    analytics_storage: statisticsCheckbox.checked ? "granted" : "denied",
  });
  gtmConsentUpdated();
  cookieOverlay.classList.add("hidden");
};

const gtmConsentUpdated = () => {
  document.cookie = `gtm_ad_user_data=denied; path=/; max-age=31536000`;
  document.cookie = `gtm_ad_personalization=denied; path=/; max-age=31536000`;
  document.cookie = `gtm_functionality_storage=denied; path=/; max-age=31536000`;
  document.cookie = `gtm_personalization_storage=denied; path=/; max-age=31536000`;
  document.cookie = `gtm_security_storage=granted; path=/; max-age=31536000`;

  window?.dataLayer?.push({ event: "gtm-consent-updated" });
};

function getCookies() {
  return document.cookie.split(";").reduce((acc, cookie) => {
    const [key, value] = cookie.trim().split("=");
    acc[key] = decodeURIComponent(value); // Decode URI-encoded values
    return acc;
  }, {});
}

// Function to set default consent based on cookies
function applyConsentFromCookies() {
  // Read your saved consent cookies

  const documentCookies = getCookies();
  const gtmAnalyticsStorage = documentCookies["gtm_analytics_storage"];
  const gtmAdStorage = documentCookies["gtm_ad_storage"];

  const analytics = gtmAnalyticsStorage || "denied";
  const marketing = gtmAdStorage || "denied";

  gtag("consent", "update", {
    analytics_storage: analytics,
    ad_storage: marketing,
    ad_user_data: marketing,
    ad_personalization: marketing,
    functionality_storage: "denied",
    personalization_storage: "denied",
    security_storage: "granted",
  });

  window?.dataLayer?.push({ event: "gtm-consent-updated" });
}

// Add event listeners
acceptBtn.addEventListener("click", acceptPartialCookies);
acceptAllBtn.addEventListener("click", acceptAllCookies);
declineAllBtn.addEventListener("click", declineAllCookies);

// Hide cookie overlay if clicked outside
cookieOverlay.addEventListener("click", (e) => {
  if (e.target.classList.contains("cookie-overlay")) {
    if (getCustomerFirstTime()) {
      declineAllCookies();
    } else {
      cookieOverlay.classList.add("hidden");
    }
  }
});

// hide cookie overlay when esc is pressed
cookieOverlay.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    if (getCustomerFirstTime()) {
      declineAllCookies();
    } else {
      cookieOverlay.classList.add("hidden");
    }
  }
});

cookie.addEventListener("click", () => {
  showBanner();
  const documentCookies = getCookies();
  const gtmAdStorage = documentCookies["gtm_ad_storage"];
  const gtmAnalyticsStorage = documentCookies["gtm_analytics_storage"];
  const rolexConsent = documentCookies["rlx-consent"];

  statisticsCheckbox.checked = gtmAnalyticsStorage === "granted";
  marketingCheckbox.checked = gtmAdStorage === "granted";
  rolexCheckbox.checked = rolexConsent === "true";
});

// Display multiple options
preferenceBtn.addEventListener("click", () => {
  // Instead of multiple classList.toggle()
  preferenceContainer.classList.toggle("hidden");

  acceptAllBtn.classList.toggle("hidden");
  acceptBtn.classList.toggle("hidden");
});

if (getCustomerFirstTime()) {
  window.addEventListener("load", () => {
    setTimeout(showBanner, 500);
  });
  document.cookie = `rlx-consent=false; path=/; max-age=31536000`;
  gtag("consent", "default", {
    analytics_storage: "denied",
    ad_storage: "denied",
    ad_user_data: "denied",
    ad_personalization: "denied",
    functionality_storage: "denied",
    personalization_storage: "denied",
    security_storage: "granted",
  });
} else {
  applyConsentFromCookies();
}
