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

$data->expense_id;
$data->user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	/*if (isset($_SESSION['user_id'])) {
		$response['success'] = false;
		$response['message'] = 'User not logged in.';
		echo json_encode($response);
		//exit,
	}	*/
	if (empty($data->expense_id)) {
		$response['success'] = false;
		$response['message']  = 'Expense ID is required.';
	} else {
		$stmt = $mysqli->prepare("DELETE FROM expenses WHERE expense_id = ? AND user_id = ?");
		$stmt->bind_param("ii", $data->expense_id, $data->user_id);
		
		if ($stmt->execute()) {
			$response['success'] = true;
			$response['message'] = 'Expense deleted successfully.';
			$response['$expense_id'] = $data->expense_id;
			$response['$user_id'] = $data->user_id;
		} else {
			$response['success'] = false;
			$response['messaage'] = 'Error deleting expense: ' . $stmt->error;
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