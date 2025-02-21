<?php
session_start();
require 'db_connection.php'; // Include database connection

$logFile = __DIR__ . DIRECTORY_SEPARATOR . "error.log";

function logError($message)
{
  global $logFile;
  $timestamp = date("Y-m-d H:i:s");
  error_log("[$timestamp] ERROR: $message" . PHP_EOL, 3, $logFile);
}

try {
  // Validate required POST parameters
  if (!isset($_SESSION['user_id']) || !isset($_POST['poster_id']) || !isset($_POST['movie_id'])) {
    throw new Exception("Error: Missing required parameters.");
  }

  $userId = $_SESSION['user_id'];
  $movieId = $_POST['movie_id'];
  $posterId = $_POST['poster_id'];

  // Check if the movie exists and belongs to the user
  $stmt = $conn->prepare("SELECT id FROM movies WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $movieId, $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    throw new Exception("Error: Movie not found or unauthorized access.");
  }

  // Get the poster details
  $stmt = $conn->prepare("SELECT image FROM movie_posters WHERE id = ? AND movie_id = ?");
  $stmt->bind_param("ii", $posterId, $movieId);
  $stmt->execute();
  $result = $stmt->get_result();
  $poster = $result->fetch_assoc();
  if (!$poster) {
    throw new Exception("Error: Poster not found.");
  }

  $posterPath = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $poster['image'];

  // Delete file if it exists
  if (file_exists($posterPath)) {
    if (!unlink($posterPath)) {
      throw new Exception("Error: Failed to delete poster file.");
    }
  }

  // Delete poster from database
  $stmt = $conn->prepare("DELETE FROM movie_posters WHERE id = ?");
  $stmt->bind_param("i", $posterId);
  $stmt->execute();

  echo "success";
  exit();
} catch (Exception $e) {
  logError($e->getMessage());
  echo $e->getMessage();
  exit();
}
