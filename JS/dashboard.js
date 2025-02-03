document.addEventListener("DOMContentLoaded", function () {
  let user = JSON.parse(localStorage.getItem("user"));
  if (!user) {
    window.location.href = "login.html";
  }

  // Fetch user-specific movies
  let movies = JSON.parse(localStorage.getItem(`movies_${user.email}`)) || [];

  document.getElementById("displayUsername").textContent = user.username;
  document.getElementById("displayEmail").textContent = user.email;
  document.getElementById("displayPhone").textContent = user.phone;

  function loadMovies() {
    let tableBody = document.getElementById("moviesList");
    tableBody.innerHTML = "";
    movies.forEach((movie, index) => {
      let row = `<tr>
          <td contenteditable="false">${movie.name}</td>
          <td contenteditable="false">${movie.rating}</td>
          <td>
            <button onclick="toggleEdit(${index}, this)">Edit</button>
            <button class="delete-btn" onclick="deleteMovie(${index})">Delete</button>
          </td>
        </tr>`;
      tableBody.innerHTML += row;
    });
  }
  loadMovies();

  // Toggle between edit and save
  window.toggleEdit = function (index, btn) {
    let row = btn.parentElement.parentElement;
    let cells = row.querySelectorAll("td");

    if (btn.textContent === "Edit") {
      cells[0].contentEditable = true;
      cells[1].contentEditable = true;
      cells[0].focus();
      btn.textContent = "Save";
      btn.style.background = "green";
    } else {
      movies[index].name = cells[0].innerText.trim();
      movies[index].rating = cells[1].innerText.trim();
      localStorage.setItem(`movies_${user.email}`, JSON.stringify(movies));

      cells[0].contentEditable = false;
      cells[1].contentEditable = false;
      btn.textContent = "Edit";
      btn.style.background = "orange";
    }
  };

  // Delete movie
  window.deleteMovie = function (index) {
    movies.splice(index, 1);
    localStorage.setItem(`movies_${user.email}`, JSON.stringify(movies));
    loadMovies();
  };

  // Edit profile
  document.getElementById("editProfileBtn").addEventListener("click", function () {
    Swal.fire({
      title: "Edit Profile",
      html: `
          <input id="editUsername" class="swal2-input" value="${user.username}">
          <input id="editPhone" class="swal2-input" value="${user.phone}">
        `,
      showCancelButton: true,
      confirmButtonText: "Update",
    }).then((result) => {
      if (result.isConfirmed) {
        user.username = document.getElementById("editUsername").value;
        user.phone = document.getElementById("editPhone").value;
        localStorage.setItem("user", JSON.stringify(user));
        location.reload();
      }
    });
  });

  // Add new movie
  document.getElementById("addMovieBtn").addEventListener("click", function () {
    Swal.fire({
      title: "Add Movie",
      html: `
        <input id="movieName" class="swal2-input" placeholder="Movie Name">
        <input id="movieRating" class="swal2-input" placeholder="Rating (1-10)">
        `,
      showCancelButton: true,
      confirmButtonText: "Create",
    }).then((result) => {
      if (result.isConfirmed) {
        let movie = {
          name: document.getElementById("movieName").value.trim(),
          rating: document.getElementById("movieRating").value.trim()
        };
        movies.push(movie);
        localStorage.setItem(`movies_${user.email}`, JSON.stringify(movies));
        loadMovies();
        document.querySelector('.movies-table').classList.add('display-table');
      }
    });
  });

  // Logout
  document.getElementById("logoutBtn").addEventListener("click", function () {
    window.location.href = "login.html";
  });
});
