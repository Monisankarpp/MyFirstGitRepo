// Function to handle login for multiple users
function loginUser() {
  let email = document.getElementById("loginEmail").value.trim();
  let password = sha1(document.getElementById("loginPassword").value.trim());

  // Retrieve users from localStorage or sessionStorage
  let users = JSON.parse(localStorage.getItem("users")) || JSON.parse(sessionStorage.getItem("users")) || [];

  // Check if user exists
  let foundUser = users.find(user => user.email === email && user.password === password);

  if (foundUser) {
    // Save logged-in user to sessionStorage for session management
    sessionStorage.setItem("loggedInUser", JSON.stringify(foundUser));

    Swal.fire({
      title: "Welcome Back!",
      text: "Login successful!",
      icon: "success",
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      window.location.href = "dashboard.html";
    });
  } else {
    Swal.fire({
      title: "Error!",
      text: "Invalid email or password.",
      icon: "error"
    });
  }
}
