<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = array();

$group_id = $data->group_id;
$payer_id = $data->payer_id;
$expense_name = $data->expense_name;
$expense_amount = $data->expense_amount;
$category = $data->category;
$date = date('Y-m-d H:i:s');
$description = $data->description;
$split_method = $data->split_method;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data->user_id)) {
        $response['success'] = false;
        $response['message'] = 'User not logged in.';
    } elseif (empty($group_id) || empty($payer_id) || empty($expense_name) || empty($expense_amount) || empty($category) || empty($date) || empty($description) || empty($split_method)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO group_expense (group_id, payer_id, expense_name, expense_amount, category, date, description, split_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $response['success'] = false;
            $response['message'] = 'Prepare failed: ' . $mysqli_error;
        } else {
            $stmt->bind_param("iisdssss", $group_id, $payer_id, $expense_name, $expense_amount, $category, $date, $description, $split_method);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Group Expense added successfully.';
                $response['record_id'] = $mysqli->insert_id;
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
