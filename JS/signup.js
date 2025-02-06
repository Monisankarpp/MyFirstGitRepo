function validateForm() {
  let isValid = true;
  let username = document.getElementById("username").value.trim();
  let email = document.getElementById("email").value.trim();
  let phone = document.getElementById("phone").value.trim();
  let password = document.getElementById("password").value.trim();
  let confirmPassword = document.getElementById("confirmPassword").value.trim();

  // Reset errors
  document.querySelectorAll('.error').forEach(el => el.textContent = "");

  // Username validation
  if (username.length < 3) {
    document.getElementById("usernameError").textContent = "Must be at least 3 characters.";
    isValid = false;
  }

  // Email validation
  let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailPattern.test(email)) {
    document.getElementById("emailError").textContent = "Enter a valid email.";
    isValid = false;
  }

  // Phone validation (Must be exactly 10 digits, no letters, no 'e')
  let phonePattern = /^[0-9]{10}$/;
  if (!phonePattern.test(phone) || phone.includes('e')) {
    document.getElementById("phoneError").textContent = "Enter a valid 10-digit phone number.";
    isValid = false;
  }

  // Password validation (At least 8 characters, uppercase, lowercase, number, special character)
  let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;
  if (!passwordPattern.test(password)) {
    document.getElementById("passwordError").textContent =
      "Password must be at least 8 characters long, with at least 1 uppercase, 1 lowercase, 1 number, and 1 special character.";
    isValid = false;
  }

  // Confirm Password validation
  if (password !== confirmPassword) {
    document.getElementById("confirmPasswordError").textContent = "Passwords do not match.";
    isValid = false;
  }

  return isValid;
}

// Prevent invalid characters in phone input
document.getElementById("phone").addEventListener("keydown", function (e) {
  let invalidChars = ["-", "+", "e", "."];
  if (invalidChars.includes(e.key)) {
    e.preventDefault();
  }
});

// Function to save user data
function saveData(storageType) {
  if (validateForm()) {
    let newUser = {
      username: document.getElementById("username").value.trim(),
      email: document.getElementById("email").value.trim(),
      phone: document.getElementById("phone").value.trim(),
      password: sha1(document.getElementById("password").value.trim())
    };

    // Retrieve existing users
    let users = JSON.parse(localStorage.getItem("users")) || [];

    // Check if email or phone already exists
    let userExists = users.some(user => user.email === newUser.email || user.phone === newUser.phone);

    if (userExists) {
      Swal.fire({
        title: "Error!",
        text: "User with this email or phone number already exists!",
        icon: "error"
      });
      return;
    }

    // Save new user
    users.push(newUser);
    localStorage.setItem("users", JSON.stringify(users));

    Swal.fire({
      title: "Success!",
      text: "Account created successfully!",
      icon: "success",
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      window.location.href = "login.html";
    });
  }
}
