// Blogs
const loadMoreBlogs = document.getElementById("loadMoreBlogs");
const blogsContainer = document.querySelector(".blog-container");
let blogPage = 1;

loadMoreBlogs?.addEventListener("click", () => {
  const queryParams = new URLSearchParams({
    page: blogPage,
  });
  blogPage++;

  queryParams.append("action", "load_more_blogs");

  fetch(`${ajax_object_another.ajax_url}?${queryParams}`, {
    method: "GET",
  })
    .then((response) => response.json())
    .then((data) => {
      blogsContainer.insertAdjacentHTML("beforeend", data.html);
      if (!data.display_load_button) {
        loadMoreBlogs.style.display = "none";
      }
    });
});