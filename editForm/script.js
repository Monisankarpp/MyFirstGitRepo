const getUserName = localStorage.getItem('Username');
const getEmail = localStorage.getItem('Email');
// const getPhoneNumber = localStorage.getItem('Phone Number');

const userName = document.querySelector('.username');
const userEmail = document.querySelector('.email');
// const userPhoneNumber = document.querySelector('.phoneNumber');

userName.placeholder = getUserName;
userEmail.placeholder = getEmail;

function editData() {
  const modifiedUsername = document.querySelector('.username').value;
  const modifiedEmail = document.querySelector('.email').value;

  localStorage.setItem("Username", modifiedUsername);
  localStorage.setItem("Email", modifiedEmail);
  // redirect();
}
// function redirect() {
//   window.location.href = "http://127.0.0.1:5500/LoginForm/user-dashboard.html";
// }