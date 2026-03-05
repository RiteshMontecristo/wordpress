function isValidEmail(email) {
  // Define the regex pattern for email validation
  const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  return emailPattern.test(email);
}
function isValidPostalCode(postalCode) {
  const regex = /^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i;
  return regex.test(postalCode.trim());
}

export default isValidEmail;
export { isValidPostalCode };
