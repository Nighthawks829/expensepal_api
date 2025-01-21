<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
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

$group_expense_id = $data->group_expense_id;
$user_id = $data->user_id;
$pay_amounts = $data->pay_amount; // Assuming this is an array of amounts
$status = $data->status;

echo $status;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data->user_id)) {
        $response['success'] = false;
        $response['message'] = 'User not logged in.';
    } elseif (empty($group_expense_id)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO group_member_expense (group_expense_id, user_id, pay_amount, status) VALUES (?,?,?,?)");
        if ($stmt === false) {
            $response['success'] = false;
            $response['message'] = 'Prepare failed: ' . $mysqli->error;
        } else {
            foreach ($pay_amounts as $pay_amount) {
                $user_id = $pay_amount->user_id;
                $amount = $pay_amount->amount;
                $stmt->bind_param("iiii", $group_expense_id, $user_id, $amount, $status);
                if (!$stmt->execute()) {
                    $response['success'] = false;
                    $response['message'] = 'Database error: ' . $stmt->error;
                    break;
                }
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
