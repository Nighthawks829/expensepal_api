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

if (!$user_id) {
	echo json_encode(["success" => false, "message" => "User ID is required."]);
	exit;
}

$query = "
SELECT 
	notifications.noti_id,
	notifications.message,
	user.username AS sender_username,
	friends.friendship_id AS friendship_id
FROM
	notifications
JOIN
	friends
ON
	notifications.user_id = friends.friend_id
JOIN 
    user 
ON 
    friends.user_id = user.user_id
WHERE
	friends.status = 'pending' AND notifications.user_id = ?
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
	$result = $stmt->get_result();
	$requests = $result->fetch_all(MYSQLI_ASSOC);
	echo json_encode(["success" => true, "requests" => $requests]);
} else {
	echo json_encode(["success" => false, "message" => "Error fetching friend requests."]);
}

$stmt->close();
$mysqli->close();

/*friends
ON
	notifications.noti_id = friends.friendship_id
JOIN
	user
ON
	friends.user_id = user.user_id
	
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$currentUserId = $_SESSION['user_id'] ?? null;
	//$friendUsername = $data->friendUsername ?? '';
	
	if (empty($currentUserId)) {
		$response['success'] = false;
		$response['message'] = 'User not logged in.';
	} else {
		$query = "SELECT f.friendship_id, u.username, f.status FROM friends f JOIN user u ON f.friend_id = u.user_id WHERE f.user_id = ? AND f.status = 'pending'";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("i", $currentUserId);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$requests = array();
		while ($row = $result->fetch_assoc()) {
			$requests[] = $row;
		}
		
		$response['success'] = true;
		$response['requests'] = $requests;
		$response['count'] = count($requests);
	}
} else {
	$response['success'] = false;
	$response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>*/
?>


