<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = array();

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
	
	if (!is_numeric($user_id)) {
		echo json_encode(["success" => false, "message" => "Invalid User ID"]);
		exit;
	}
} else {
	echo json_encode(["success" => false, "message" => "User ID is required"]);
	exit;
}

$query = "SELECT budget_name AS budgetName, amount AS budgetAmount, categories, notification_percentage FROM budgets WHERE user_id = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
	echo json_encode(["success" => false, "message" => "Database query preparation failed"]);
	exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$budgets = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(["success" => true, "budgets" => $budgets]);

$stmt->close();
$mysqli->close();
?>