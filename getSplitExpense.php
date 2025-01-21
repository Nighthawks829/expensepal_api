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
$friend_id = $_GET['friend_id'] ?? null;

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User ID is required."]);
    exit;
}

if (!$friend_id) {
    echo json_encode(["success" => false, "message" => "Friend ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT se.*, 
                     u1.username AS user_name, 
                     u2.username AS friend_name,
                     u3.username AS payer_name
              FROM splitexpenses se
              JOIN user u1 ON se.user_id = u1.user_id
              JOIN user u2 ON se.friend_id = u2.user_id
              JOIN user u3 ON se.payer_id = u3.user_id
              WHERE (se.user_id = ? OR se.friend_id = ?) 
                AND (se.user_id = ? OR se.friend_id = ?)";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("iiii", $user_id, $user_id, $friend_id, $friend_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $split_expense = $result->fetch_all(MYSQLI_ASSOC);
            $response['success'] = true;
            $response['splitExpense'] = $split_expense;
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
