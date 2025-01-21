<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
	echo json_encode(["success" => false, "message" => "Invalid user ID."]);
	exit;
}

$sql = "
	SELECT u.user_id, u.username
	FROM friends f
	JOIN user u ON (u.user_id = f.friend_id OR u.user_id = f.user_id)
	WHERE
		(f.user_id = ? OR f.friend_id = ?)
		AND f.status = 'accepted'
		AND u.user_id != ?
";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
	echo json_encode(["success" => false, "message" => "Failed to prepare SQL statement."]);
	exit;
}

$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
	$friends = $result->fetch_all(MYSQLI_ASSOC);
	echo json_encode(["success" => true, "friends" => $friends]);
} else {
	echo json_encode(["success" => false, "message" => "Failed to fetch friends."]);
}

$stmt->close();
$mysqli->close();
?>		