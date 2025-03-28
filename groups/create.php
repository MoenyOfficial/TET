<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../models/group.php';
include_once '../middleware/auth.php';

// Authenticate user
$user_id = authenticate();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize group object
$group = new Group($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->name)) {
    // Set group property values
    $group->name = $data->name;
    $group->description = !empty($data->description) ? $data->description : "";
    $group->created_by = $user_id;
    $group->created_at = date('Y-m-d H:i:s');
    
    // Create group
    if($group->create()) {
        // Add creator as a member
        if($group->addMember($user_id)) {
            // Set response code - 201 created
            http_response_code(201);
            
            // Tell the user
            echo json_encode(array(
                "success" => true,
                "message" => "Group was created.",
                "group_id" => $group->id
            ));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Tell the user
            echo json_encode(array(
                "success" => false,
                "message" => "Group was created but failed to add you as a member."
            ));
        }
    } else {
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to create group."
        ));
    }
} else {
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to create group. Data is incomplete."
    ));
}
?>
