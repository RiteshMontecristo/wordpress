const overlay = document.getElementById("appointment-modal-overlay");
if (!overlay) throw new Error("appointment-modal: overlay not found");

const form        = document.getElementById("appointmentModalForm");
const successEl   = document.getElementById("apptSuccess");
const serverError = document.getElementById("apptServerError");
const submitBtn   = document.getElementById("appt-submit");
const closeBtn    = overlay.querySelector(".appt-modal-close");
const storeSection = document.getElementById("appt-store-selection");

function openModal() {
  overlay.removeAttribute("hidden");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  overlay.setAttribute("hidden", "");
  document.body.style.overflow = "";
  if (form) {
    form.reset();
    form.style.display = "";
    form.querySelectorAll(".error").forEach((el) => el.classList.add("hidden"));
    if (serverError) serverError.innerHTML = "";
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = "Book Appointment";
    }
  }
  if (successEl) {
    successEl.setAttribute("hidden", "");
    successEl.style.display = "";
  }
  storeSection?.classList.add("hidden");
}

document.querySelectorAll(".open-appointment-modal").forEach((btn) =>
  btn.addEventListener("click", openModal)
);
closeBtn?.addEventListener("click", closeModal);
overlay.addEventListener("click", (e) => {
  if (e.target === overlay) closeModal();
});
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !overlay.hasAttribute("hidden")) closeModal();
});

overlay.querySelectorAll("input[name='appointmentType']").forEach((radio) => {
  radio.addEventListener("change", () => {
    const isInStore = radio.value === "in-store" && radio.checked;
    storeSection?.classList.toggle("hidden", !isInStore);
  });
});

form?.addEventListener("submit", async (e) => {
  e.preventDefault();

  overlay.querySelectorAll(".error").forEach((el) => el.classList.add("hidden"));
  if (serverError) serverError.innerHTML = "";

  const firstName       = overlay.querySelector("#appt-first-name");
  const lastName        = overlay.querySelector("#appt-last-name");
  const appointmentType = overlay.querySelector("input[name='appointmentType']:checked");
  const store           = overlay.querySelector("input[name='store']:checked");
  const date            = overlay.querySelector("#appt-date");
  const time            = overlay.querySelector("#appt-time");
  const email           = overlay.querySelector("#appt-email");
  const phone           = overlay.querySelector("#appt-phone");
  const terms           = overlay.querySelector("#appt-terms");

  const validations = [
    { id: "apptFirstNameError", valid: firstName?.value.trim() !== "" },
    { id: "apptLastNameError",  valid: lastName?.value.trim() !== "" },
    { id: "apptTypeError",      valid: !!appointmentType },
    { id: "apptStoreError",     valid: appointmentType?.value !== "in-store" || !!store },
    { id: "apptDateError",      valid: !!date?.value },
    { id: "apptTimeError",      valid: !!time?.value },
    { id: "apptEmailError",     valid: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email?.value ?? "") },
    { id: "apptPhoneError",     valid: phone?.value.trim() !== "" },
    { id: "apptTermsError",     valid: terms?.checked },
  ];

  let errors = 0;
  validations.forEach(({ id, valid }) => {
    if (!valid) {
      document.getElementById(id)?.classList.remove("hidden");
      errors++;
    }
  });

  if (errors > 0) return;

  if (submitBtn) submitBtn.disabled = true;

  const body = new URLSearchParams({
    action:          "mji_appointment_modal",
    nonce:           overlay.querySelector("#appt-modal-nonce")?.value ?? "",
    firstName:       firstName?.value.trim() ?? "",
    lastName:        lastName?.value.trim() ?? "",
    appointmentType: appointmentType?.value ?? "",
    store:           store?.value ?? "",
    date:            date?.value ?? "",
    time:            time?.value ?? "",
    email:           email?.value.trim() ?? "",
    phone:           phone?.value.trim() ?? "",
    message:         overlay.querySelector("#appt-message")?.value.trim() ?? "",
  });

  const ajaxUrl = window.mcCart?.ajax_url ?? window.ajax_object_another?.ajax_url;

  try {
    const res  = await fetch(ajaxUrl, { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body });
    const json = await res.json();

    if (json.success) {
      form.style.display = "none";
      if (successEl) {
        successEl.removeAttribute("hidden");
        successEl.style.display = "block";
      }
    } else {
      const errs = json.data?.errors ?? [json.data?.message ?? "Something went wrong. Please try again."];
      if (serverError) serverError.innerHTML = errs.map((err) => `<li>${err}</li>`).join("");
    }
  } catch {
    if (serverError) serverError.innerHTML = "<li>Something went wrong. Please try again.</li>";
  } finally {
    if (submitBtn) submitBtn.disabled = false;
  }
});
