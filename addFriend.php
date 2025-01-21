<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$currentUserId = $_SESSION['user_id'] ?? null;
	$friendUsername = $data->friendUsername ?? '';
	
	if (empty($currentUserId)) {
		$response['success'] = false;
		$response['message'] = 'User not logged in.';
	} elseif (empty($friendUsername)) {
		$response['success'] = false;
		$response['message'] = 'Please provide a username to add as a friend.';
	} else {
		$stmt = $mysqli->prepare("SELECT user_id FROM user WHERE username = ?");
		$stmt->bind_param("s", $friendUsername);
		$stmt->execute();
		$stmt->store_result();
		
		if ($stmt->num_rows > 0) {
			$stmt->bind_result($friendId);
			$stmt->fetch();
			
			$checkStmt = $mysqli->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
			$checkStmt->bind_param("iiii", $currentUserId, $friendId, $friendId, $currentUserId);
			$checkStmt->execute();
			$checkStmt->store_result();
			
			if ($checkStmt->num_rows == 0) {
				$insertStmt = $mysqli->prepare("INSERT INTO friends (user_id, friend_id, status, notification_sent) VALUES (?, ?, 'pending', 1)");
				$insertStmt->bind_param("ii", $currentUserId, $friendId);
			
				if ($insertStmt->execute()) {
					$response['success'] = true;
					$response['message'] = 'Friend request sent successfully.';
					
					$noti_message = "You have a friend request from " . $data->username;
					$insertNoti = $mysqli->prepare("INSERT INTO notifications (user_id, message, status, type) VALUES (?, ?, 'pending', 'friend request')");
					$insertNoti->bind_param("is", $friendId, $noti_message);
					$insertNoti->execute();
					$insertNoti->close();
				} else {
					$response['success'] = false;
					$response['message'] = 'Failed to send friend request.';
				}
				$insertStmt->close();
			} else {
				$response['success'] = false;
				$response['message'] = 'Friend request already exists or you are already friends.';
			}
			$checkStmt->close();
		} else {
			$response['success'] = false;
			$response['message'] = 'User not found.';
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