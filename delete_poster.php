<?php
session_start();

try {
  $jsonFile = 'users.json';

  // Check if JSON file exists
  if (!file_exists($jsonFile)) {
    throw new Exception("Error: users.json file not found.");
  }

  // Load JSON data
  $usersData = file_get_contents($jsonFile);
  $users = json_decode($usersData, true);

  if ($users === null) {
    throw new Exception("Error: Failed to decode users.json.");
  }

  // Validate required POST parameters
  if (!isset($_SESSION['user_id']) || !isset($_POST['poster_id']) || !isset($_POST['movie_id'])) {
    throw new Exception("Error: Missing required parameters.");
  }

  // Find the current user
  $userIndex = array_search($_SESSION['user_id'], array_column($users, 'id'));
  if ($userIndex === false) {
    throw new Exception("Error: User not found.");
  }

  $movies = &$users[$userIndex]['movies'];
  $movieIndex = array_search($_POST['movie_id'], array_column($movies, 'id'));

  if ($movieIndex === false) {
    throw new Exception("Error: Movie not found.");
  }

  $movie = &$movies[$movieIndex];

  // Find and remove the poster
  foreach ($movie['posters'] as $key => $poster) {
    if ($poster['id'] === $_POST['poster_id']) {
      $posterPath = "uploads/" . $poster['image'];

      // Delete file if it exists
      if (file_exists($posterPath)) {
        if (!unlink($posterPath)) {
          throw new Exception("Error: Failed to delete poster file.");
        }
      }

      unset($movie['posters'][$key]); // Remove the poster from the list
      $movie['posters'] = array_values($movie['posters']); // Reindex array

      // Save updated data back to JSON
      if (!file_put_contents($jsonFile, json_encode($users, JSON_PRETTY_PRINT))) {
        throw new Exception("Error: Failed to update users.json.");
      }

      echo "success";
      exit();
    }
  }

  throw new Exception("Error: Poster not found.");
} catch (Exception $e) {
  echo $e->getMessage(); // Return detailed error message
  exit();
}
