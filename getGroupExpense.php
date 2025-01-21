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

if (!$group_expense_id) {
    echo json_encode(["success" => false, "message" => "Group Expense ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "
    SELECT 
    ge.id,
    ge.group_id,
    ge.payer_id,
    u.username AS payer_name,
    ge.expense_name,
    ge.expense_amount,
    ge.category,
    ge.date,
    ge.description,
    ge.split_method
FROM 
    group_expense ge
JOIN 
    user u ON ge.payer_id = u.user_id
WHERE 
    ge.id = ?;
";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $group_expense_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $group_expenses = $result->fetch_all(MYSQLI_ASSOC);
            $response['success'] = true;
            $response['group_expense'] = $group_expenses;
        } else {
            $response['success'] = true;
            $response['group_expense'] = [];
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
?>
