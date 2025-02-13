function validateForm() {
  let valid = true;

  let username = document.getElementById("username").value.trim();
  let email = document.getElementById("email").value.trim();
  let phone = document.getElementById("phone").value.trim();
  let password = document.getElementById("password").value.trim();

  // Username Validation (Min 6 chars)
  if (username.length < 6) {
    document.getElementById("usernameError").innerText = "Username must be at least 6 characters.";
    valid = false;
  } else {
    document.getElementById("usernameError").innerText = "";
  }

  // Email Validation (Proper format)
  let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailPattern.test(email)) {
    document.getElementById("emailError").innerText = "Enter a valid email address.";
    valid = false;
  } else {
    document.getElementById("emailError").innerText = "";
  }

  // Phone Number Validation (Only numbers, exactly 10 digits)
  let phonePattern = /^[0-9]{10}$/;
  if (!phonePattern.test(phone)) {
    document.getElementById("phoneError").innerText = "Phone number must be exactly 10 digits.";
    valid = false;
  } else {
    document.getElementById("phoneError").innerText = "";
  }

  // Password Validation (Min 6 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char)
  let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
  if (!passwordPattern.test(password)) {
    document.getElementById("passwordError").innerText = "Password must be at least 6 characters, include 1 uppercase, 1 lowercase, 1 number & 1 special character.";
    valid = false;
  } else {
    document.getElementById("passwordError").innerText = "";
  }

  return valid; // Prevent form submission if any validation fails
}

// Restrict Phone Number Input to Only Numbers
document.getElementById("phone").addEventListener("input", function () {
  this.value = this.value.replace(/[^0-9]/g, "");
});