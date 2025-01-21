<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Original: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = array();

if ($data && isset($data->username) && isset($data->password)) {
	$username = $mysqli->real_escape_string($data->username);
	$password = $data->password;
	
	$stmt = $mysqli->prepare("SELECT user_id, username, email, password FROM user WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$stmt->store_result();
	
	if($stmt->num_rows > 0) {
		$stmt->bind_result($user_id, $username, $email, $hashedPassword);
		$stmt->fetch();
		
		if (password_verify($password, $hashedPassword)) {
			$_SESSION['user_id'] = $user_id;
			//error_log("User ID set in session: " . $_SESSION['user_id']);
			$response['success'] = true;
			$response['message'] = 'Login succesfull!';
			$response['user'] = array(
				'user_id' => $user_id,
				'username' => $username,
				'email' => $email
			);
		} else {
			$response['success'] = false;
			$response['message'] = 'Incorrect password.';
		}
	} else {
		$response['success'] = false;
		$response['message'] = 'No account found. Please sign up first.';
	}
	
	$stmt->close();
} else {
	$response['success'] = false;
	$response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>