<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "root", "", "expensepal");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
} else {
	echo json_encode(["success" => false, "message" => "User ID is required"]);
	exit;
}

$query = "SELECT * FROM expenses WHERE user_id = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
	echo json_encode(["success" => false, "message" => "Database query preparation failed"]);
	exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$expenses = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(["success" => true, "expenses" => $expenses]);

$stmt->close();
$mysqli->close();
?>