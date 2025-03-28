<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../models/message.php';
include_once '../middleware/auth.php';

// Authenticate user
$user_id = authenticate();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize message object
$message = new Message($db);

// Get other user ID
$other_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// Validate other user ID
if(empty($other_user_id)) {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Missing user_id parameter."
    ));
    exit();
}

// Get messages
$stmt = $message->getMessages($user_id, $other_user_id);
$num = $stmt->rowCount();

// Check if any messages found
if($num > 0) {
    // Messages array
    $messages_arr = array();
    
    // Retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $message_item = array(
            "id" => $id,
            "sender_id" => $sender_id,
            "receiver_id" => $receiver_id,
            "sender_name" => $sender_name,
            "receiver_name" => $receiver_name,
            "content" => $content,
            "created_at" => $created_at
        );
        
        array_push($messages_arr, $message_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show messages data in JSON format
    echo json_encode($messages_arr);
} else {
    // Set response code - 200 OK
    http_response_code(200);
    
    // Tell the user no messages found
    echo json_encode(array());
}
?>