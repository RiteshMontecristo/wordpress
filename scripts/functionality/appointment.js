// CONTACT US APPOINTMENT FORM
// const appointmentContainer = document.querySelector(".appointment-container");
// if (appointmentContainer) {
//   const appointmentForm =
//     appointmentContainer.querySelector("#appointmentForm");
//   const firstName = appointmentContainer.querySelector("#first-name");
//   const firstNameError = appointmentContainer.querySelector("#firstNameError");
//   const lastName = appointmentContainer.querySelector("#last-name");
//   const lastNameError = appointmentContainer.querySelector("#lastNameError");
//   const store = appointmentContainer.querySelector("#store");
//   const storeError = appointmentContainer.querySelector("#storeError");
//   const date = appointmentContainer.querySelector("#date");
//   const dateError = appointmentContainer.querySelector("#dateError");
//   const time = appointmentContainer.querySelector("#time");
//   const timeError = appointmentContainer.querySelector("#timeError");
//   const preferredContact =
//     appointmentContainer.querySelector("#preferred-contact");
//   const emailContainer = appointmentContainer.querySelector(".email-address");
//   const email = appointmentContainer.querySelector("#email");
//   const emailError = appointmentContainer.querySelector("#emailError");
//   const phoneContainer = appointmentContainer.querySelector(".phone-number");
//   const phone = appointmentContainer.querySelector("#phone");
//   const phoneError = appointmentContainer.querySelector("#phoneError");
//   const serverError = appointmentContainer.querySelector("#serverError");
//   const appointment_nonce =
//     appointmentContainer.querySelector("#appointment_nonce");

//   const imgDropArea = appointmentContainer.querySelector(".drop-area");
//   const imageFile = appointmentContainer.querySelector("#imageFile");
//   const imageList = appointmentContainer.querySelector(".img-list");

//   [("dragenter", "dragover")].forEach((eventName) => {
//     imgDropArea.addEventListener(eventName, (e) => {
//       e.preventDefault();
//       imgDropArea.classList.add("active");
//     });
//   });

//   ["dragleave", "drop"].forEach((eventName) => {
//     imgDropArea.addEventListener(eventName, (e) => {
//       e.preventDefault();
//       imgDropArea.classList.remove("active");
//     });
//   });

//   imgDropArea.addEventListener("drop", (e) => {
//     e.preventDefault();
//     const files = e.dataTransfer.files;
//     const dataTransfer = new DataTransfer();

//     Array.from(files).forEach((file) => {
//       if (file.type.startsWith("image/")) {
//         dataTransfer.items.add(file);
//       }
//     });
//     imageFile.files = dataTransfer.files;
//     changeListItem();
//   });

//   function changeListItem() {
//     let imageLi = "";

//     Array.from(imageFile.files).forEach((file) => {
//       imageLi += `<li>${file.name}</li>`;
//     });

//     imageList.innerHTML = imageLi;
//   }

//   // Handle file selection through input (if user clicks)
//   imgDropArea.addEventListener("click", () => imageFile.click());
//   imageFile.addEventListener("change", changeListItem);

//   const vancouver = {
//     Sunday: ["Closed"],
//     Monday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//     ],
//     Tuesday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//     ],
//     Wednesday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//     ],
//     Thursday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//     ],
//     Friday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//     ],
//     Saturday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//     ],
//   };

//   const metrotown = {
//     Sunday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//     ],
//     Monday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Tuesday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Wednesday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Thursday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Friday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Saturday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//   };

//   const richmond = {
//     Sunday: [
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//     ],
//     Monday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Tuesday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Wednesday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Thursday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Friday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//     Saturday: [
//       "10:00 AM",
//       "11:00 AM",
//       "12:00 PM",
//       "1:00 PM",
//       "2:00 PM",
//       "3:00 PM",
//       "4:00 PM",
//       "5:00 PM",
//       "6:00 PM",
//       "7:00 PM",
//       "8:00 PM",
//     ],
//   };

//   const appointmentSuccess = appointmentContainer.querySelector(
//     "#appointmentSuccess"
//   );

//   function changeTime() {
//     if (store.value && date.value) {
//       const selectedDate = new Date(date.value + "T00:00:00");
//       const dayOfWeek = selectedDate.toLocaleDateString("en-US", {
//         weekday: "long",
//       });
//       let options = "<option disabled value='' selected>Select One</option>";

//       switch (store.value) {
//         case "vancouver":
//           vancouver[dayOfWeek].forEach((timeEl) => {
//             options += `<option value='${timeEl}' ${
//               timeEl === time.value && "selected"
//             } ${timeEl === "Closed" && "disabled"}>${timeEl}</option>`;
//           });
//           break;
//         case "metrotown":
//           metrotown[dayOfWeek].forEach((timeEl) => {
//             options += `<option value='${timeEl}' ${
//               timeEl === time.value && "selected"
//             }>${timeEl}</option>`;
//           });
//           break;
//         default:
//           richmond[dayOfWeek].forEach((timeEl) => {
//             options += `<option value='${timeEl}' ${
//               timeEl === time.value && "selected"
//             }>${timeEl}</option>`;
//           });
//       }

//       time.innerHTML = options;
//     }
//   }

//   store.addEventListener("change", changeTime);
//   date.addEventListener("change", changeTime);

//   preferredContact.addEventListener("change", () => {
//     if (preferredContact.value == "email") {
//       emailContainer.style.display = "flex";
//       phoneContainer.style.display = "none";
//     } else {
//       emailContainer.style.display = "none";
//       phoneContainer.style.display = "flex";
//     }
//   });

//   appointmentForm.addEventListener("submit", (e) => {
//     e.preventDefault();
//     firstNameError.classList.add("hidden");
//     lastNameError.classList.add("hidden");
//     emailError.classList.add("hidden");
//     phoneError.classList.add("hidden");
//     messageError.classList.add("hidden");
//     serverError.innerHTML = "";

//     let errors = 0;

//     if (!firstName.value.trim()) {
//       errors++;
//       firstNameError.classList.remove("hidden");
//     }
//     if (!lastName.value.trim()) {
//       lastNameError.classList.remove("hidden");
//       errors++;
//     }
//     if (!store.value) {
//       storeError.classList.remove("hidden");
//       errors++;
//     }
//     if (!date.value) {
//       dateError.classList.remove("hidden");
//       errors++;
//     }
//     if (!time.value) {
//       timeError.classList.remove("hidden");
//       errors++;
//     }

//     if (preferredContact.value == "email") {
//       if (!isValidEmail(email.value.trim())) {
//         emailError.classList.remove("hidden");
//         errors++;
//       }
//     } else {
//       if (phone.value.length != 10) {
//         phoneError.classList.remove("hidden");
//         errors++;
//       }
//     }

//     if (errors < 1) {
//       let data = new FormData();

//       data.append("lastName", lastName.value);
//       data.append("firstName", firstName.value);
//       data.append("store", store.value);
//       data.append("date", date.value);
//       data.append("time", time.value);
//       data.append("preferredContact", preferredContact.value);
//       data.append("action", "appointment");
//       data.append("appointment_nonce", appointment_nonce.value);

//       for (let i = 0; i < imageFile.files.length; i++) {
//         data.append("img[]", imageFile.files[i]);
//       }

//       if (preferredContact.value === "email" && email.value) {
//         data.append("email", email.value);
//       } else if (preferredContact.value === "phone" && phone.value) {
//         data.append("phone", phone.value);
//       }

//       fetch(ajax_object_another.ajax_url, {
//         method: "POST",
//         body: data,
//       })
//         .then((res) => {
//           return res.json();
//         })
//         .then((res) => {
//           if (res.success) {
//             appointmentSuccess.style.display = "flex";
//             appointmentForm.style.display = "none";
//           } else {
//             if (res.data.message) {
//               serverError.innerHTML = `<li>${res.data.message}</li>`;
//             } else {
//               let result = "";
//               res.data.errors.forEach((el) => {
//                 result += `<li>${el}</li>`;
//               });

//               serverError.innerHTML = result;
//             }
//           }
//         })
//         .catch((err) => {
//           console.log("err", err);
//         });
//     }
//   });
// }

// const contactNav = document.getElementById("contactNav");
// const appointmentNav = document.getElementById("appointmentNav");

// contactNav?.addEventListener("click", () => {
//   hideAppointment();
// });

// appointmentNav?.addEventListener("click", () => {
//   hideContact();
// });

// function hideAppointment() {
//   appointmentContainer.style.display = "none";
//   contactUsFormContainer.style.display = "flex";
// }

// function hideContact() {
//   contactUsFormContainer.style.display = "none";
//   appointmentContainer.style.display = "flex";
// }

// if (window.location.pathname == "/contact") {
//   console.log(window.location.hash);
//   if (window.location.hash == "#appointment") {
//     hideContact();
//   }
// }