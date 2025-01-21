<?php

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
	if (empty($data['action']) || empty($data['friendship_id'])) {
		echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
		exit();
	}
	
	$action = $data['action'];
	$friendship_id = $data['friendship_id'];
	
	try {
		if ($action === 'accept') {
			$query = "UPDATE friends SET status = 'accepted' WHERE friendship_id = ?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("i", $friendship_id);
			$stmt->execute();
			
			if ($stmt->affected_rows > 0) {
				echo json_encode(['success' => true, 'message' => 'Friend request accepted.']);
			} else {
				echo json_encode(['success' => false, 'message' => 'Unable to accept friend request.']);
			}
		} elseif ($action === 'decline') {
			$query = "DELETE FROM friends WHERE friendship_id = ?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("i", $friendship_id);
			$stmt->execute();
			
			if ($stmt->affected_rows > 0) {
				echo json_encode(['success' => true, 'message' => 'Friend request rejected.']);
			} else {
				echo json_encode(['success' => false, 'message' => 'Unable to reject friend request.']);
			}
		} else {
			echo json_encode(['success' => false, 'message' => 'Invalid action.']);
		}
	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
	}
} else {
	echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$mysqli->close();
?>