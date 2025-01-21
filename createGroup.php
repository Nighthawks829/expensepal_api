<?php

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$user_id = $data['user_id'];
	$group_name = $data['group_name'];
	$members = $data['members'];
	
	$mysqli->begin_transaction();
	try {
		$stmt = $mysqli->prepare("INSERT INTO groups (group_name， created_by) VALUES (?, ?)");
		$stmt->bind_param("si", $group_name, $user_id);
		$stmt->execute();
		//$group_id = $stmt->insert_id;
		
		foreach ($members as $member_id) {
			$stmt = $mysqli->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
			$stmt->bind_param("ii", $group_id, $member_id);
			$stmt->execute();
		}
		
		$stmt->close();
		$mysqli->commit();
		
		echo json_encode(["success" => true, "message" => "Group created successfully."]);
	} catch (Exception $e) {
		$mysqli->rollback();
		echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
	}
}

/*
	
	if (!$user_id || !$group_name || empty($members)) {
		$response['success'] = false;
		$response['message'] = 'Please provide all required fields.';
	} else {
		$stmt = $mysqli->prepare("INSERT INTO groups (group_name, created_by) VALUES (?, ?)");
		$stmt->bind_param("si", $group_name, $user_id);
		
		if ($stmt->execute()) {
			$group_id = $stmt->insert_id;
			
			$member_stmt = $mysqli->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
			foreach ($members as $member_id) {
				$member_stmy->bind_param("ii", $group_id, $member_id);
				$member_stmt->execute();
			} 
			$member_stmt->close();
			
            $response['success'] = true;
            $response['message'] = 'Group created successfully!';
            $response['group_id'] = $group_id;
        } else {
            $response['success'] = false;
            $response['message'] = 'Error creating group: ' . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);

?>*/
?>			