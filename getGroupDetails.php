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

$group_id = $_GET['group_id'] ?? null;

if (!$group_id) {
    echo json_encode(["success" => false, "message" => "Group ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM group_expense WHERE group_id = ?";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $group_id);
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
