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

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT s.id, u.user_id, u.username AS payer_name, s.amount, s.created_at, s.status
FROM settleup_payment s
JOIN user u ON s.user_id = u.user_id
WHERE s.user_id = ?;
";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $user_id);
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
