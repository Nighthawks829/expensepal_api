<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
	
	$query = "SELECT username, email, phone_number FROM user WHERE user_id = ?";
	$stmt = $mysqli->prepare($query);
	
	if (!$stmt) {
		echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
		exit;
	}
	
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if ($result->num_rows > 0) {
		$user = $result->fetch_assoc();
		echo json_encode(['success' => true, 'data' => $user]);
	} else {
		echo json_encode(['success' => false, 'message' => 'User not found.']);
	}
	$stmt->close();
} else {
	echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
}

$mysqli->close();
?>