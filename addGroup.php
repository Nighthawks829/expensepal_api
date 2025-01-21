<?php
// session_start();

// date_default_timezone_set('Asia/Kuala_Lumpur');
// $mysqli = new mysqli("localhost", "root", "", "expensepal");

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type");

// if ($mysqli->connect_error) {
//     die("Connection failed: " . $mysqli->connect_error);
// }

// $data = json_decode(file_get_contents("php://input"));
// $response = array();

// $user_id = $data->user_id;
// $group_name = $data->groupName;
// $selectedFriends = $data->selectedFriends;
// $created_at = date('Y-m-d H:i:s');

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     if (empty($data->user_id)) {
//         $response['success'] = false;
//         $response['message'] = 'User not logged in.';
//     } elseif (empty($data->groupName) || empty($data->selectedFriends)) {
//         $response['success'] = false;
//         $response['message'] = 'Please fill in all required fields.';
//     } else {
//         $query = "INSERT INTO groups (group_name, created_by, created_at) VALUES ('$group_name', '$user_id', NOW())";

//         $stmt = $mysqli->prepare("INSERT INTO groups (group_name, created_by, created_at) VALUES (?,?,?)");

//         $stmt->bind_param("sss", $group_name, $user_id, $created_at);
//         if ($stmt->execute()) {
//             $response['success'] = true;
//             $response['message'] = 'Group added successfully!';
//         } else {
//             $response['success'] = false;
//             $response['message'] = 'Error adding group: ' . $stmt->error;
//         }
//         $stmt->close();
//     }
// } else {
//     $response['success'] = false;
//     $response['message'] = 'Invalid request method.';
// }

// $mysqli->close();
// echo json_encode($response);
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');
$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = array();

$user_id = $data->user_id;
$group_name = $data->groupName;
$selectedFriends = $data->selectedFriends;
$created_at = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data->user_id)) {
        $response['success'] = false;
        $response['message'] = 'User not logged in.';
    } elseif (empty($data->groupName) || empty($data->selectedFriends)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO groups (group_name, created_by, created_at) VALUES (?,?,?)");
        $stmt->bind_param("sss", $group_name, $user_id, $created_at);

        if ($stmt->execute()) {
            $group_id = $mysqli->insert_id; // Get the last inserted group ID

            // Prepare statement to insert members
            $friend_stmt = $mysqli->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $friend_stmt->bind_param("ii", $group_id, $member_id);

            // Add the user as a member of the group
            $member_id = $user_id;
            $friend_stmt->execute();

            // Loop through the selected friends and add them to the group
            foreach ($selectedFriends as $friend_id) {
                $member_id = $friend_id;
                $friend_stmt->execute();
            }

            $friend_stmt->close();
            $response['success'] = true;
            $response['message'] = 'Group and members added successfully!';
        } else {
            $response['success'] = false;
            $response['message'] = 'Error adding group: ' . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>
