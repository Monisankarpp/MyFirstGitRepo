<?php
$api_url = "https://api.themoviedb.org/3/movie/popular?api_key=b3bd0a7d4c57a233823c00863c87325f&language=en-US&page=1";
$response = file_get_contents($api_url);
$movies = json_decode($response, true);

if (isset($movies["results"])) {
  foreach ($movies["results"] as $movie) {
    echo '<div class="movie-card">';
    echo '<img src="https://image.tmdb.org/t/p/w500' . htmlspecialchars($movie["poster_path"]) . '" alt="' . htmlspecialchars($movie["title"]) . '">';
    echo '<h3>' . htmlspecialchars($movie["title"]) . '</h3>';
    echo '<p>Rating: ' . htmlspecialchars(round($movie["vote_average"])) . '/10</p>';
    echo '</div>';
  }
} else {
  echo "<p>No popular movies found.</p>";
}
?>