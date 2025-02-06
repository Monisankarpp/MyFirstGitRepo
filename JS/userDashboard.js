document.addEventListener("DOMContentLoaded", function () {
  // Retrieve the currently logged-in user from sessionStorage
  let loggedInUser = JSON.parse(sessionStorage.getItem("loggedInUser"));

  if (!loggedInUser) {
    window.location.href = "login.html"; // Redirect if not logged in
  }

  // Fetch user-specific movies
  let movies = JSON.parse(localStorage.getItem(`movies_${loggedInUser.email}`)) || [];

  // Display user details on the dashboard
  document.getElementById("displayUsername").textContent = loggedInUser.username;
  document.getElementById("displayEmail").textContent = loggedInUser.email;
  document.getElementById("displayPhone").textContent = loggedInUser.phone;

  // Function to load and display movies in the table
  function loadMovies() {
    let tableBody = document.getElementById("moviesList");
    tableBody.innerHTML = ""; // Clear existing rows

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
  loadMovies(); // Initial load

  // Toggle between Edit and Save mode for movies
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
      localStorage.setItem(`movies_${loggedInUser.email}`, JSON.stringify(movies));

      cells[0].contentEditable = false;
      cells[1].contentEditable = false;
      btn.textContent = "Edit";
      btn.style.background = "orange";
    }
  };

  // Delete a movie
  window.deleteMovie = function (index) {
    movies.splice(index, 1); // Remove the movie
    localStorage.setItem(`movies_${loggedInUser.email}`, JSON.stringify(movies)); // Update localStorage
    loadMovies(); // Refresh the table
  };

  // Edit user profile
  document.getElementById("editProfileBtn").addEventListener("click", function () {
    Swal.fire({
      title: "Edit Profile",
      html: `
        <input id="editUsername" class="swal2-input" value="${loggedInUser.username}">
        <input id="editPhone" class="swal2-input" value="${loggedInUser.phone}">
      `,
      showCancelButton: true,
      confirmButtonText: "Update",
    }).then((result) => {
      if (result.isConfirmed) {
        loggedInUser.username = document.getElementById("editUsername").value.trim();
        loggedInUser.phone = document.getElementById("editPhone").value.trim();

        // Retrieve all users, update the current user's data, and save back to localStorage
        let users = JSON.parse(localStorage.getItem("users")) || [];
        let updatedUsers = users.map(user =>
          user.email === loggedInUser.email ? loggedInUser : user
        );

        localStorage.setItem("users", JSON.stringify(updatedUsers));
        sessionStorage.setItem("loggedInUser", JSON.stringify(loggedInUser));

        location.reload(); // Reload to reflect changes
      }
    });
  });

  // Add a new movie
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
        localStorage.setItem(`movies_${loggedInUser.email}`, JSON.stringify(movies));
        loadMovies();
        document.querySelector('.movies-table').classList.add('display-table');
      }
    });
  });

  // Logout functionality
  document.getElementById("logoutBtn").addEventListener("click", function () {
    sessionStorage.removeItem("loggedInUser"); // Clear session data
    window.location.href = "login.html"; // Redirect to login page
  });
});
