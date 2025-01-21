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

$user_id = $_GET['user_id'] ?? null;
$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    echo json_encode(["success" => false, "message" => "User ID is required."]);
    exit;
}

if (!$group_id) {
    echo json_encode(["success" => false, "message" => "Group ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT 
    gme.id AS group_member_expense_id,
    gme.group_expense_id,
    gme.user_id,
    gme.pay_amount,
    gme.status,
    ge.payer_id,
    u.username AS payer_username,
    ge.expense_name,
    ge.expense_amount,
    ge.category,
    ge.date,
    ge.description,
    ge.split_method
FROM 
    group_member_expense gme
JOIN 
    group_expense ge ON gme.group_expense_id = ge.id
JOIN 
    user u ON ge.payer_id = u.user_id
WHERE 
    gme.user_id = ? AND ge.group_id = ?";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("ii", $user_id,$group_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $group_expenses = $result->fetch_all(MYSQLI_ASSOC);
            $response['success'] = true;
            $response['settlements'] = $group_expenses;
        } else {
            $response['success'] = true;
            $response['settlements'] = [];
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
