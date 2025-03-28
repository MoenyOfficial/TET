<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../models/friend.php';
include_once '../middleware/auth.php';

// Authenticate user
$user_id = authenticate();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize friend object
$friend = new Friend($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->friend_id)) {
    // Set friend property values
    $friend->user_id = $user_id;
    $friend->friend_id = $data->friend_id;
    
    // Check if trying to add self
    if($friend->user_id == $friend->friend_id) {
        // Set response code - 400 bad request
        http_response_code(400);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "You cannot add yourself as a friend."
        ));
        exit();
    }
    
    // Check if already friends
    if($friend->checkFriendship()) {
        // Set response code - 400 bad request
        http_response_code(400);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "You are already friends with this user."
        ));
        exit();
    }
    
    // Add friend
    if($friend->addFriend()) {
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode(array(
            "success" => true,
            "message" => "Friend was added."
        ));
    } else {
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to add friend."
        ));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to add friend. Data is incomplete."
    ));
}
?>
