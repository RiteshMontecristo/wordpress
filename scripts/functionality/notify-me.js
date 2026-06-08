// "Notify Me" for out-of-stock products on the single product page.

document.addEventListener("click", function (e) {
  const submitBtn = e.target.closest(".mji-notify-submit");
  if (submitBtn) handleSubmit(submitBtn.closest(".mji-notify-form"));
});

async function handleSubmit(form) {
  if (!form) return;

  const emailInput = form.querySelector(".mji-notify-email");
  const messageEl  = form.querySelector(".mji-notify-message");
  const submitBtn  = form.querySelector(".mji-notify-submit");
  const productId  = form.dataset.product;
  const email      = emailInput?.value?.trim();

  if (!email) {
    showMessage(messageEl, "Please enter your email address.", "error");
    return;
  }

  if (!window.mcCart?.ajax_url) return;

  if (submitBtn) submitBtn.disabled = true;

  try {
    const res  = await fetch(window.mcCart.ajax_url, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action:     "mji_notify_me",
        nonce:      window.mcCart.notify_nonce,
        email:      email,
        product_id: productId,
      }),
    });
    const json = await res.json();

    if (json.success) {
      showMessage(messageEl, "We'll reach out as soon as this item is back in stock.", "success");
      if (emailInput) emailInput.value = "";
    } else {
      showMessage(messageEl, json.data?.message || "Something went wrong. Please try again.", "error");
    }
  } catch {
    showMessage(messageEl, "Something went wrong. Please try again.", "error");
  } finally {
    if (submitBtn) submitBtn.disabled = false;
  }
}

function showMessage(el, text, type) {
  if (!el) return;
  el.textContent = text;
  el.className   = `mji-notify-message ${type}`;
}
