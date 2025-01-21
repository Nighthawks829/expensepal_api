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

$data = json_decode(file_get_contents("php://input"), true);
$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$user_id = $_GET['user_id'];

	$query = "SELECT groups.group_id, group_name, COUNT(group_members.member_id) as member_count
			  FROM groups
			  JOIN group_members ON groups.group_id = group_members.group_id
			  WHERE group_members.user_id = ?
			  GROUP BY groups.group_id";

	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$result = $stmt->get_result();

	$groups = [];
	while ($row = $result->fetch_assoc()) {
		$groups[] = $row;
	}

	echo json_encode($groups);
}
