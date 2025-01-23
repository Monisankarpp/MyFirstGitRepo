
const form = document.getElementById('form');
const userName = document.getElementById('username');
const email = document.getElementById('email');
const phoneNumber = document.getElementById('phonenumber');
const password = document.getElementById('password');
const cpassword = document.getElementById('cpassword');


//now i add event
form.addEventListener('submit',(event)=>{
  event.preventDefault();
  validate();

  // Used to get value
  const usernameVal = userName.value.trim();
  const emailVal = email.value.trim();
  const phoneNumberVal = phoneNumber.value.trim();
  const passwordVal = password.value.trim();
  const cpasswordVal = cpassword.value.trim();

  // to store data in our local storage
  localStorage.setItem("Username",usernameVal);
  localStorage.setItem("Email",emailVal);
  localStorage.setItem("Phone Number",phoneNumberVal);
  localStorage.setItem("Password",passwordVal);
  localStorage.setItem("Confirm Password",cpasswordVal);

  // to store the data in session storage
  sessionStorage.setItem("Username",usernameVal);
  sessionStorage.setItem("Email",emailVal);
  sessionStorage.setItem("Phone Number",phoneNumberVal);
  sessionStorage.setItem("Password",passwordVal);
  sessionStorage.setItem("Confirm Password",cpasswordVal);
})


// email validate function
const isEmail = (emailVal)=> {
  let atSymbol=emailVal.indexOf('@');
  if(atSymbol < 1){
    return false;
  }
  let dot = emailVal.lastIndexOf('.');
  if(dot <= atSymbol+4) return false;
  if(dot === emailVal.length-1) return false;
  return true;
}


//define validate function start
const validate = ()=> {
  const usernameVal = userName.value.trim();
  const emailVal = email.value.trim();
  const phonenumberVal = phoneNumber.value.trim();
  const passwordVal = password.value.trim();
  const cpasswordVal = cpassword.value.trim();


  // validate username
  if (usernameVal ===''){
    setErrorMsg(userName,'Username cannot be blank');
  }else if(usernameVal.length<=3){
    setErrorMsg(userName,'Username must be more than 3 character');
  }else{
    setSuccessMsg(userName);
  }

  // validate email
  if (emailVal === ''){
    setErrorMsg(email,'email cannot be blank');
  }else if(!isEmail(emailVal)){
    setErrorMsg(email,'not a valid email');
  }else{
    setSuccessMsg(email);
  }

  // validate phone
  if (phonenumberVal === ''){
    setErrorMsg(phoneNumber,'phone number cannot be blank');
  }else if(phonenumberVal.length!==10){
    setErrorMsg(phoneNumber,'not a valid phone number');
  }else{
    setSuccessMsg(phoneNumber);
  }

  // validate password
  if (passwordVal === ''){
    setErrorMsg(password,'password cannot be blank');
  }else if(passwordVal.length<=7){
    setErrorMsg(password,'password must be 8 character');
  }else{
    setSuccessMsg(password);
  }

  // validate confirm password
  if (cpasswordVal === ''){
    setErrorMsg(cpassword,'confirm password cannot be blank');
  }else if(cpasswordVal!==passwordVal){
    setErrorMsg(cpassword,'confirm password doesnnot match');
  }else{
    setSuccessMsg(cpassword);
  }
}
// validate function end here


// setErrorMsg function start
function setErrorMsg(input,errormsgs){
  const formControl = input.parentElement;
  const small=formControl.querySelector('small');
  formControl.className='form-control error';
  small.innerText=errormsgs;
}
// setErrorMsg function end here 


// setSuccessMsg function 
function setSuccessMsg(input){
  const formControl=input.parentElement;
  formControl.className = 'form-control success';
}
// setSuccessMsg function end here