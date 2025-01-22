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

$friend_id = $_GET['friend_id'] ?? null;

if (!$friend_id) {
    echo json_encode(["success" => false, "message" => "Friend ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM user WHERE user_id = ?";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $friend_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $split_expense = $result->fetch_all(MYSQLI_ASSOC);
            $response['success'] = true;
            $response['user'] = $split_expense;
        } else {
            $response['success'] = true;
            $response['user'] = [];
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
