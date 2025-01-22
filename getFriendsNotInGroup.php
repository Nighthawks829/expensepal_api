<?php
session_start();

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

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User ID is required."]);
    exit;
}

if (!$group_id) {
    echo json_encode(["success" => false, "message" => "Group ID is required."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT 
    u.user_id,
    u.username
FROM 
    friends f
JOIN 
    user u ON (f.friend_id = u.user_id OR f.user_id = u.user_id)
WHERE 
    (
        (f.user_id = ? AND u.user_id = f.friend_id) OR 
        (f.friend_id = ? AND u.user_id = f.user_id)
    )
    AND u.user_id NOT IN (
        SELECT gm.user_id 
        FROM group_members gm 
        WHERE gm.group_id = ?
    )
    AND f.status = 'accepted';
";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("iii", $user_id, $user_id, $group_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $users = $result->fetch_all(MYSQLI_ASSOC);
            $response['success'] = true;
            $response['users'] = $users;
        } else {
            $response['success'] = true;
            $response['users'] = [];
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
