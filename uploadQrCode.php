<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && isset($_POST['user_id']) && isset($_POST['file_name']) && isset($_POST['file_size'])) {
        $user_id = $mysqli->real_escape_string($_POST['user_id']);
        $file_name = $mysqli->real_escape_string($_POST['file_name']);
        $file_size = $mysqli->real_escape_string($_POST['file_size']);

        // Append user_id to the file name
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '_' . $user_id . '.' . $file_ext;

        $uploadDir = './uploads/';
        $uploadFile = $uploadDir . basename($new_file_name);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            $stmt = $mysqli->prepare("UPDATE user SET file_name = ? WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $new_file_name, $user_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "QR Code uploaded and data updated successfully!";
                } else {
                    $response['success'] = false;
                    $response['message'] = "Failed to update data: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['success'] = false;
                $response['message'] = "Error preparing statement: " . $mysqli->error;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to move uploaded file.";
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Missing required parameters.";
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

$mysqli->close();
echo json_encode($response);
