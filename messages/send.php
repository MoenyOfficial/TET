<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../models/message.php';
include_once '../models/friend.php';
include_once '../middleware/auth.php';

// Authenticate user
$user_id = authenticate();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize message and friend objects
$message = new Message($db);
$friend = new Friend($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->receiver_id) && !empty($data->content)) {
    // Set message property values
    $message->sender_id = $user_id;
    $message->receiver_id = $data->receiver_id;
    $message->content = $data->content;
    $message->created_at = date('Y-m-d H:i:s');
    
    // Check if users are friends
    $friend->user_id = $user_id;
    $friend->friend_id = $data->receiver_id;
    
    if(!$friend->checkFriendship()) {
        // Set response code - 403 forbidden
        http_response_code(403);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "You can only send messages to your friends."
        ));
        exit();
    }
    
    // Create message
    if($message->create()) {
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode(array(
            "success" => true,
            "message" => "Message was sent."
        ));
    } else {
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to send message."
        ));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to send message. Data is incomplete."
    ));
}
?>