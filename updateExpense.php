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
	if (!isset($_SESSION['user_id'])) {
		$response['success'] = false;
		$response['message'] = 'User not logged in.';
		echo json_encode($response);
		exit;
	}
	
	$user_id = $data->user_id;
	$expense_id = $data->expense_id;
	$expense_title = $data->expense_title;
	$amount = $data->amount;
	$category = $data->category;
	$date = $data->date;
	$notes = isset($data->notes) ? $data->notes : null;
	
	if (empty($data->expense_id) || empty($data->expense_title) || empty($data->amount) || empty($data->category) || empty($data->date)) {
		$response['success'] = false;
		$response['message'] = 'Please fill in all required fields.';
	} else {
		$stmt = $mysqli->prepare("UPDATE expenses SET expense_title = ?, amount = ?, category = ?, date = ?, notes = ? WHERE expense_id = ? AND user_id = ?");
		$stmt->bind_param("sdsssii", $data->expense_title, $data->amount, $data->category, $data->date, $data->notes, $data->expense_id, $data->user_id);
		
		if ($stmt->execute()) {
			$response['succes'] = true;
			$response['message'] = 'Successfully update.';
		} else {
			$response['success'] = false;
			$response['message'] = 'Error updating expense: ' . $stmt->error;
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