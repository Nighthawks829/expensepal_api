<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$response = array();

$group_expense_id = $_GET['group_expense_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;
$friend_id = $_GET['friend_id'] ?? null;

if (!$friend_id) {
    echo json_encode(["success" => false, "message" => "Friend ID is required."]);
    exit;
}

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User ID is required."]);
    exit;
}
if (!$group_expense_id) {
    echo json_encode(["success" => false, "message" => "Group Expense ID is required."]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "
    SELECT 
        s.id, 
        u.user_id, 
        u.username AS payer_name, 
        r.username AS receiver_name, 
        s.amount, 
        s.created_at, 
        s.status
    FROM 
        group_settleup_payment s
    JOIN 
        user u ON s.user_id = u.user_id
    JOIN 
        user r ON s.receiver_id = r.user_id
    WHERE 
        s.user_id = ? 
    AND 
        s.receiver_id = ? 
    AND 
        s.group_expense_id = ? 
    AND 
        s.status = 1;
    ";


    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("iii", $user_id, $friend_id, $group_expense_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $settleup_payment = $result->fetch_all(MYSQLI_ASSOC);
            $response['success'] = true;
            $response['settleup_payment'] = $settleup_payment;
        } else {
            $response['success'] = true;
            $response['settleup_payment'] = [];
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Error preparing query: ' . $mysqli->error;
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
