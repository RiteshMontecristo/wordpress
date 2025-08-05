import "./functionality/header.js";
import "./functionality/products.js";
import "./functionality/homepage.js";

const path = window.location.pathname;

if (path.includes("blog")) {
  import("./functionality/blogs.js");
} else if (path.includes("contact") || path.includes("customize-your-jewellery")) {
  import("./functionality/contact.js");
} else if (path.includes("my-account")) {
  import("./functionality/favourite.js");
}

if ('requestIdleCallback' in window) {
  requestIdleCallback(() => {
    import("./functionality/cookie.js");
  });
} else {
  setTimeout(() => {
    import("./functionality/cookie.js");
  }, 2000);
}

// Youtube video player
document.addEventListener("DOMContentLoaded", () => {

  const videoContainer = document.querySelectorAll("#youtubeVideo");

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
