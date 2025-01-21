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
	if (empty($data->budget_id) || empty($data->user_id)) {
		$response['success'] = false;
		$response['message'] = 'Budget ID and User ID are required.';
	} else {
		$stmt = $mysqli->prepare("DELETE FROM budgets WHERE budget_id = ? AND user_id = ?");
		$stmt->bind_param("ii", $data->budget_id, $data->user_id);
		
		if ($stmt->execute()) {
			$response['success'] = true;
			$response['message'] = 'Budget deleted successfullly.';
		} else {
			$response['success'] = false;
			$response['message'] = 'Error deleting budget: ' . $stmt->error;
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