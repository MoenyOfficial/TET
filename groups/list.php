<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

// Get groups
$stmt = $group->getUserGroups($user_id);
$num = $stmt->rowCount();

// Check if any groups found
if($num > 0) {
    // Groups array
    $groups_arr = array();
    
    // Retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $group_item = array(
            "id" => $id,
            "name" => $name,
            "description" => $description,
            "created_at" => $created_at,
            "member_count" => $member_count
        );
        
        array_push($groups_arr, $group_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show groups data in JSON format
    echo json_encode($groups_arr);
} else {
    // Set response code - 200 OK
    http_response_code(200);
    
    // Tell the user no groups found
    echo json_encode(array());
}
?>