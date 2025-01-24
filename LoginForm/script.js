const login=document.getElementById('login');

login.onclick = (event) => {
  event.preventDefault();

  const userNameVal = document.getElementById('username').value;
  const userPasswordVal = document.getElementById('password').value;

  const getUserName = localStorage.getItem('Username');
  const getUserPassword = localStorage.getItem('Password');

  if(userNameVal === '' && userPasswordVal === ''){
    alert('Blank');
  }else{
    if(userNameVal == getUserName && userPasswordVal == getUserPassword){
      alert('success');
      redirect();
    }else{
      alert('Username and Password not correct');
    }
  }
}

function redirect(){
  window.location.href="user-dashboard.html";
}