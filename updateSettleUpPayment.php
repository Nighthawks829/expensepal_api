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

$id = $data->id;
$status = $data->status;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($id) || empty($status)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $stmt = $mysqli->prepare("UPDATE settleup_payment SET status = ? WHERE id = ?");
        if ($stmt === false) {
            $response['success'] = false;
            $response['message'] = 'Prepare failed: ' . $mysqli_error;
        } else {
            $stmt->bind_param("ii", $status, $id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Settle Patment updated successfully.';
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
