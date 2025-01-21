<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));
$response = array();

$user_id = $_POST['user_id'];
$message = $_POST['message'];

if ($user_id && $message) {
	$query = "INSERT INTO notifications (user_id, message, status, created_at, type) VALUES ('$user_id', '$message', 'pending', NOW(), 'general')";
	
	if (mysqli_query($mysqli, $query)) {
		echo json_encode(['success' => true, 'message' => 'Notification sent successfully.']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Failed to send notification.']);
	}
} else {
	echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}

?>