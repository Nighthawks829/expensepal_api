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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $group_id = $_GET['group_id'];

    $query = "SELECT u.user_id, u.username
              FROM group_members gm
              JOIN user u ON gm.user_id = u.user_id
              WHERE gm.group_id = ?";

    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = array(
                'user_id' => $row['user_id'],
                'username' => $row['username']
            );
        }

        $response['success'] = true;
        $response['users'] = $users;
        $stmt->close();
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
