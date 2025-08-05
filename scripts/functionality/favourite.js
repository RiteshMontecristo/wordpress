// Favourite My account page
const favouriteContainer = document.querySelector(".favourite-container");

if (favouriteContainer) {
  const favourteItemArr = document.querySelectorAll(".favourite-item");
  const popup = document.querySelector(".popup");
  const yesBtn = document.querySelector("#yes-btn");
  const noBtn = document.querySelector("#no-btn");

  favourteItemArr?.forEach((el) => {
    const removeFavBtn = el.querySelector(".remove-fav");

    const userId = removeFavBtn.dataset.user;
    const productId = removeFavBtn.dataset.product;

    const formData = new FormData();

    formData.append("productId", productId);
    formData.append("userId", userId);
    formData.append("favourite", "true");
    formData.append("action", "toggleFavourite");

    removeFavBtn.addEventListener("click", () => {
      popup.style.display = "flex";

      yesBtn.onclick = () => {
        fetch(ajax_object_another.ajax_url, {
          method: "POST",
          body: formData,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              location.reload();
            }
          })
          .catch((err) => console.error(err));
      };

      noBtn.onclick = () => {
        popup.style.display = "none";
      };
    });
  });
}