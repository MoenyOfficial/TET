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
if(!$group->isMember($user_id, $group_id)) {
    // Set response code - 403 forbidden
    http_response_code(403);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "You are not a member of this group."
    ));
    exit();
}

// Get members
$stmt = $group->getMembers($group_id);
$num = $stmt->rowCount();

// Check if any members found
if($num > 0) {
    // Members array
    $members_arr = array();
    
    // Retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $member_item = array(
            "id" => $id,
            "username" => $username,
            "email" => $email,
            "joined_at" => $joined_at,
            "is_admin" => ($id == $created_by) ? true : false
        );
        
        array_push($members_arr, $member_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show members data in JSON format
    echo json_encode($members_arr);
} else {
    // Set response code - 200 OK
    http_response_code(200);
    
    // Tell the user no members found
    echo json_encode(array());
}
?>
