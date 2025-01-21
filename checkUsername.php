<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents("php://input"), true);
	
	if (isset($data['username'])) {
		$username = $mysqli->quote($data['username']);
		$stmt = $mysqli->query("SELECT username FROM user WHERE username = $username");
		$exists = $stmt->rowCount() > 0;
		
		$respoonse = array("exists" => $exists);
		echo json_encode($response);
	} else {
		echo json_encode(["success" => false, "message" => "No username provided."]);
	}
}
?>