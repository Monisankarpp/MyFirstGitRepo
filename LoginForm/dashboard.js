const getUserName = localStorage.getItem('Username');
const getEmail = localStorage.getItem('Email');
const getPhoneNumber = localStorage.getItem('Phone Number');

const userName = document.querySelector('.username');
const userEmail = document.querySelector('.email');
const userPhoneNumber = document.querySelector('.phoneNumber');


userName.innerText = getUserName;
userEmail.innerText = getEmail;
userPhoneNumber.innerText = getPhoneNumber;