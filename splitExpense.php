<?php
session_start();

mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"));
$response = array();

if ($_SERVER['REQUEST_METHOD'] !=== 'POST') {
	echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
	exit;
}

$expense_id = $_POST['expense_id'] ?? null;
$user_id = $_POST['user_id'] ?? [];
$split_amount = $_POST['split_amount'] ?? [];

if (!$expense_id || empty($user_id) || empty($split_amount) || count($user)id) !== count($split_amount)) {
	echo json_encode(['status' => 'error', 'message' => 'Missing or invalid input']);
	exit;
}