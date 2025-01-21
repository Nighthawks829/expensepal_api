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
$response = [];

//$user_id = $_GET['user_id'];

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
	$action = $_GET['action'] ?? null;
	
	$query_notifications = "SELECT * FROM notifications WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC";
	$stmt_notifications = $mysqli->prepare($query_notifications);
	
	if ($stmt_notifications) {
		$stmt_notifications->bind_param("i", $user_id);
		$stmt_notifications->execute();
		$result_notifications = $stmt_notifications->get_result();
		
		$notifications = [];
		while ($row = $result_notifications->fetch_assoc()) {
			$notifications[] = $row;
		}
		$stmt_notifications->close();
		
		//$response['success'] = true;
		$response['notifications'] = $notifications;
	}
	
	$query_friend_requests = "
		SELECT
			n.noti_id,
			n.user_id AS receiver_id,
			f.user_id As sender_id,
			u.username AS sender_username,
			n.message,
			n.created_at,
			'friend request' AS type
		FROM
			notifications n
		JOIN
			friends f ON n.user_id = f.friend_id
		JOIN
			user u ON u.user_id = f.user_id
		WHERE
			n.status = 'pending' AND f.status = 'pending' AND n.user_id = ?
	";
	$stmt_friend_requests = $mysqli->prepare($query_friend_requests);
	
	if ($stmt_friend_requests) {
		$stmt_friend_requests->bind_param("i", $user_id);
		$stmt_friend_requests->execute();
		$result_friend_requests = $stmt_friend_requests->get_result();
		
		while ($row = $result_friend_requests->fetch_assoc()) {
			$is_duplicate = false;
			foreach ($response['notifications'] as $notification) {
				if ($notification['noti_id'] == $row['noti_id']) {
					$is_duplicate = true;
					break;
				}
			}
			if (!$is_duplicate) {
				$response['notifications'][] = $row;
			}
		}
		$stmt_friend_requests->close();
	}
	
	$response['success'] = true;
} else {
	$response['success'] = false;
	$response['message'] = 'User not logged in.';
}

$mysqli->close();

echo json_encode($response);
/*session_start();

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = [];

//$user_id = $_GET['user_id'];

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
	/*$action = $_GET['action'] ?? null;
	
	if ($action === 'count') {
		$query_count = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
		$stmt_count = $mysqli->prepare($query_count);
		if ($stmt_count) {
			$stmt_count->bind_param("i", $user_id);
			$stmt_count->execute();
			$result_count = $stmt_count->get_result()->fetch_assoc();
			$stmt_count->close();
			$response['success'] = true;
			$response['unread_count'] = $result_count['unread_count'];
		} else {
			$response['success'] = false;
			$response['message'] = "Error fetching unread count.";
		}
	} else {
		$response['notifications'] = [];*/
		
		/*$query_notifications = "SELECT * FROM notifications WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC";
		$stmt_notifications = $mysqli->prepare($query_notifications);
		
		if ($stmt_notifications) {
			$stmt_notifications->bind_param("i", $user_id);
			$stmt_notifications->execute();
			$result_notifications = $stmt_notifications->get_result();
			
			$notifications = [];
			while ($row = $result_notifications->fetch_assoc()) {
				$response['notifications'][] = $row;
			}
			$stmt_notifications->close();
			
			//$response['success'] = true;
			$response['notifications'] = $notifications;
		}
	
		$query_friend_requests = "
			SELECT
				n.noti_id,
				n.user_id AS receiver_id,
				f.user_id As sender_id,
				u.username AS sender_username,
				n.message,
				n.created_at,
				'friend request' AS type
			FROM
				notifications n
			JOIN
				friends f ON n.user_id = f.friend_id
			JOIN
				user u ON u.user_id = f.user_id
			WHERE
				n.status = 'pending' AND f.status = 'pending' AND n.user_id = ?
		";
		$stmt_friend_requests = $mysqli->prepare($query_friend_requests);
	
		if ($stmt_friend_requests) {
			$stmt_friend_requests->bind_param("i", $user_id);
			$stmt_friend_requests->execute();
			$result_friend_requests = $stmt_friend_requests->get_result();
			/*$friend_notifications = $result_friend_requests->fetch_all(MYQLI_ASSOC);
			$stmt_friend_requests->close();
			
			foreach ($friend_notifications AS $friend_notification) {
				if (!in)array($friend_notification, $response['notifications'], true)) {
					$response['notifications'][] = $friend_notification;
				}
			}
		}
		
		$response['success'] = true;
	}
} else {
	$response['success'] = false;
	$response['message'] = 'User not logged in.';*/
/*}
			while ($row = $result_friend_requests->fetch_assoc()) {
				$is_duplicate = false;
				foreach ($response['notifications'] as $notification) {
					if ($notification['noti_id'] == $row['noti_id']) {
						$is_duplicate = true;
						break;
					}
				}
			if (!$is_duplicate) {
				$response['notifications'][] = $row;
			}
		}
		$stmt_friend_requests->close();
	}
	
	$response['success'] = true;
} else {
	$response['success'] = false;
	$response['message'] = 'User not logged in.';
}

$mysqli->close();

echo json_encode($response);*/
?>