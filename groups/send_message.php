<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../models/group_message.php';
include_once '../middleware/auth.php';

// Authenticate user
$user_id = authenticate();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize group message object
$group_message = new GroupMessage($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->group_id) && !empty($data->content)) {
    // Set message property values
    $group_message->group_id = $data->group_id;
    $group_message->user_id = $user_id;
    $group_message->content = $data->content;
    $group_message->created_at = date('Y-m-d H:i:s');
    
    // Check if user is a member of the group
    if(!$group_message->isGroupMember($user_id, $data->group_id)) {
        // Set response code - 403 forbidden
        http_response_code(403);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "You are not a member of this group."
        ));
        exit();
    }
    
    // Create message
    if($group_message->create()) {
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