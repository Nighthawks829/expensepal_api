<?php
$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

session_start();

$data = json_decode(file_get_contents("php://input"));
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$user_id = $data->user_id;
	$budget_id = $data->budget_id;
	$budget_name = $data->budget_name;
	$amount = $data->amount;
	$start_date = $data->start_date;
	$end_date = $data->end_date;
	$categories = $data->categories;
	$notification_percentage = !empty($data->notification_percentage) ? $data->notification_percentage : 50;
	
	if (empty($data->user_id) || empty($data->budget_name) || empty($data->amount) || empty($data->start_date) || empty($data->end_date) || empty($data->categories)) {
		$response['success'] = false;
		$response['message'] = 'Please fill in all required fields.';
	} else {
		$stmt = $mysqli->prepare("UPDATE budgets SET budget_name = ?, amount = ?, start_date = ?, end_date = ?, categories = ? WHERE budget_id = ? AND user_id = ?");
		$stmt->bind_param("ssssssi", $data->budget_name, $data->amount, $data->start_date, $data->end_date, $data->categories, $data->budget_id, $data->user_id);
		
		if ($stmt->execute()) {
			$response['success'] = true;
			$response['message'] = 'Budget updated successfully.';
		} else {
			$response['success'] = false;
			$response['message'] = 'Error updating budget: ' . $stmt->error;
		}
		$stmt->close();
	}
} else {
	$response['success'] = false;
	$response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>