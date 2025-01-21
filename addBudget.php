<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (empty($data['user_id'])) {
		$response['success'] = false;
		$response['message'] = 'User not logged in.';
	} else if (empty($data['budget_name']) || empty($data['amount']) || empty($data['start_date'])) {
		$response['success'] = false;
		$response['message'] = 'Please fill in all required fields.';
	} else {
		$end_date = !empty($data['end_date']) ? $data['end_date'] : null;
		$repeat_frequency = ($data['repeat_option'] === 'custom') ? $data['repeat_frequency'] : null;
		$notification_percentage = !empty($data['notification_percentage']) ? $data['notification_percentage'] : 50;
		
		$stmt = $mysqli->prepare("INSERT INTO budgets (user_id, budget_name, amount, start_date, end_date, categories, repeat_option, repeat_frequency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("issssssi", $data['user_id'], $data['budget_name'], $data['amount'], $data['start_date'], $end_date, $data['categories'], $data['repeat_option'], $repeat_frequency);

		if ($stmt->execute()) {
			$response['success'] = true;
			$response['message'] = 'Budget added successfully!';
		} else {
			$response['success'] = false;
			$response['message'] = 'Error adding budget: ' . $stmt->error;
		}
		$stmt->close();
	}
} else {
	$response['success'] = false;
	$response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
