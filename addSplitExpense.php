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

$user_id = $data->user_id;
$friend_id = $data->friend_id;
$payer_id = $data->payer_id;
$amount = $data->amount;
$user_share = $data->user_share;
$friend_share = $data->friend_share;
$description = $data->description;
$status = $data->status;
$date_created = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($user_id)) {
        $response['success'] = false;
        $response['message'] = 'User not logged in.';
    } elseif (empty($amount) || empty($user_share) || empty($friend_share) || empty($date_created)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO splitexpenses (user_id, friend_id,payer_id, amount, user_share, friend_share, description,status,date_created) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)");
        if ($stmt === false) {
            $response['success'] = false;
            $response['message'] = 'Prepare failed: ' . $mysqli_error;
        } else {
            $stmt->bind_param("iiidddsss", $user_id, $friend_id, $payer_id, $amount, $user_share, $friend_share, $description, $status, $date_created);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Split Expense added successfully.';
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
