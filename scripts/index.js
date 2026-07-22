import "./functionality/header.js";
import "./functionality/live-search.js";
import "./functionality/cart-modal.js";
import "./functionality/products.js";
import "./functionality/homepage.js";
import "./functionality/cookie.js";

const path = window.location.pathname;

if (path.includes("blog")) {
  import("./functionality/blogs.js");
} else if (
  path.includes("contact") ||
  path.includes("customize-your-jewellery")
) {
  import("./functionality/contact.js");
} else if (path.includes("my-account")) {
  import("./functionality/favourite.js");
}

// Load block cart sub-brand injection on cart/checkout when Montecristo items are present
if (window.mjiBlockCartSubBrands) {
  import("./functionality/block-cart-subbrand.js");
}

// Load notify-me JS only when an out-of-stock product has the trigger button
if (document.querySelector(".mji-open-notify-modal")) {
  import("./functionality/notify-me.js");
}

// Load contact form JS whenever the contact modal is present in the page
// (e.g. single product pages)
if (document.getElementById("contact-modal-overlay")) {
  import("./functionality/contact.js");
}

if (document.getElementById("appointment-modal-overlay")) {
  import("./functionality/appointment-modal.js");
}

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
          `https://www.youtube.com/embed/${videoId}?autoplay=1`
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
