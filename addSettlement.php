<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');

$mysqli = new mysqli("localhost", "root", "", "expensepal");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$data = json_decode(file_get_contents("php://input"));
$response = array();

$group_expense_id = $data->group_expense_id;
$user_id = $data->user_id;
$amount = $data->amount;
$settled_at = date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($data->user_id)) {
        $response['success'] = false;
        $response['message'] = 'User not logged in.';
    } elseif (empty($group_expense_id) || empty($user_id) || empty($amount) || empty($settled_at)) {
        $response['success'] = false;
        $response['message'] = 'Please fill in all required fields.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO settlements (group_expense_id, user_id, amount, settled_at) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $response['success'] = false;
            $response['message'] = 'Prepare failed: ' . $mysqli->error;
        } else {
            $stmt->bind_param("iids", $group_expense_id, $user_id, $amount, $settled_at);
            if ($stmt->execute()) {
                // Update group_member_expense to reduce the pay_amount
                $update_stmt = $mysqli->prepare("UPDATE group_member_expense SET pay_amount = pay_amount - ? WHERE group_expense_id = ? AND user_id = ?");
                if ($update_stmt === false) {
                    $response['success'] = false;
                    $response['message'] = 'Update failed: ' . $mysqli->error;
                } else {
                    $update_stmt->bind_param("iii", $amount, $group_expense_id, $user_id);
                    if ($update_stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Settlement added and group member expense updated successfully.';
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Update error: ' . $update_stmt->error;
                    }
                    $update_stmt->close();
                }
                $stmt->close();
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: ' . $stmt->error;
            }
        }
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$mysqli->close();
echo json_encode($response);
?>
