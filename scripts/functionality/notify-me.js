const overlay  = document.getElementById("mji-notify-modal-overlay");
if (!overlay) throw new Error("notify-me: overlay not found");

const form      = overlay.querySelector(".mji-notify-form");
const emailInput = overlay.querySelector(".mji-notify-email");
const messageEl  = overlay.querySelector(".mji-notify-message");
const submitBtn  = overlay.querySelector(".mji-notify-submit");
const closeBtn   = overlay.querySelector(".mji-notify-modal-close");

function openModal(productId) {
  form.dataset.product  = productId;
  emailInput.value      = "";
  messageEl.textContent = "";
  messageEl.className   = "mji-notify-message";
  overlay.removeAttribute("hidden");
  document.body.style.overflow = "hidden";
  emailInput.focus();
}

function closeModal() {
  overlay.setAttribute("hidden", "");
  document.body.style.overflow = "";
}

document.addEventListener("click", (e) => {
  const trigger = e.target.closest(".mji-open-notify-modal");
  if (trigger) openModal(trigger.dataset.product);
});

closeBtn?.addEventListener("click", closeModal);
overlay.addEventListener("click", (e) => {
  if (e.target === overlay) closeModal();
});
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !overlay.hasAttribute("hidden")) closeModal();
});

submitBtn?.addEventListener("click", handleSubmit);

async function handleSubmit() {
  const productId = form.dataset.product;
  const email     = emailInput?.value?.trim();

  if (!email) {
    showMessage("Please enter your email address.", "error");
    return;
  }

  if (!ajax_object_another?.ajax_url) return;

  submitBtn.disabled = true;

  try {
    const res  = await fetch(ajax_object_another.ajax_url, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action:     "mji_notify_me",
        nonce:      ajax_object_another.notify_nonce,
        email,
        product_id: productId,
      }),
    });
    const json = await res.json();

    if (json.success) {
      showMessage("We'll reach out as soon as this item is back in stock.", "success");
      emailInput.value = "";
    } else {
      showMessage(json.data?.message || "Something went wrong. Please try again.", "error");
    }
  } catch {
    showMessage("Something went wrong. Please try again.", "error");
  } finally {
    submitBtn.disabled = false;
  }
}

function showMessage(text, type) {
  messageEl.textContent = text;
  messageEl.className   = `mji-notify-message ${type}`;
}
