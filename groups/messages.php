<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

// Get group ID
$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : '';

// Validate group ID
if(empty($group_id)) {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Missing group_id parameter."
    ));
    exit();
}

// Check if user is a member of the group
if(!$group_message->isGroupMember($user_id, $group_id)) {
    // Set response code - 403 forbidden
    http_response_code(403);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "You are not a member of this group."
    ));
    exit();
}

// Set group ID
$group_message->group_id = $group_id;

// Get messages
$stmt = $group_message->getMessages();
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
            "group_id" => $group_id,
            "user_id" => $user_id,
            "username" => $username,
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