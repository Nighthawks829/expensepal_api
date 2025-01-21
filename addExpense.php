<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = array();

$data->expense_title;
$data->amount;
$data->category;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (empty($data->user_id)) {
		$response['success'] = false;
		$response['message'] = 'User not logged in.';
	} elseif (empty($data->expense_title) || empty($data->amount) || empty($data->category) || empty($data->date)) {
		$response['success'] = false;
		$response['message'] = 'Please fill in all required fields.';
	} else {
		$stmt = $mysqli->prepare("INSERT INTO expenses (user_id, expense_title, amount, category, date, notes) VALUES (?, ?, ?, ?, ?, ?)");
		if ($stmt === false) {
			$response['success'] = false;
			$response['message'] = 'Prepare failed: ' . $mysqli_error;
		} else {
			$stmt->bind_param("isssss", $data->user_id, $data->expense_title, $data->amount, $data->category, $data->date, $data->notes);
			if ($stmt->execute()) {
				$response['success'] = true;
				$response['message'] = 'Expense added successfully.';
			} else {
				$response['success'] = false;
				$response['message'] = 'Database error: ' . $stmt->error;
			}
			$stmt->close();
		}
	}
} else {
	$response['success'] = false;
	$response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>