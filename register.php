<?php
include 'db_connection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents("php://input"), true);
	
	if (isset($data['username'], $data['email'], $data['phone_number'], $data['password'])) {
		$username = $mysqli->quote($data['username']);
		$email = $mysqli->quote($data['email']);
		$phone_number = $mysqli->quote($data['phone_number']);
		$password = password_hash($data['password'], PASSWORD_DEFAULT);
		
		$stmt = $mysqli->query("SELECT username FROM user WHERE username = $username");
		
		if ($stmt->rowCount() > 0) {
			echo json_encode(["success" => false, "message" => "Username already taken."]);
		} else {
			$stmt = $mysqli->prepare("INSERT INTO user (username, email, phone_number, password) VALUES (?, ?, ?, ?)");
			$stmt->execute([$data['username'], $data['email'], $data['phone_number'], $password]);
			
			echo json_encode(["success" => true, "message" => "User registered successfully."]);
		}
	} else {
		echo json_encode(["success" => false, "messasge" => "Invalid input data."]);
	}
}
?>