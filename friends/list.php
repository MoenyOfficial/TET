<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../models/user.php';
include_once '../middleware/auth.php';

// Authenticate user
$user_id = authenticate();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Set ID property of user to be read
$user->id = $user_id;

// Read the details of user
if($user->readOne()) {
    // Create array
    $user_arr = array(
        "id" => $user->id,
        "username" => $user->username,
        "email" => $user->email,
        "created_at" => $user->created_at
    );

    // Set response code - 200 OK
    http_response_code(200);

    // Make it json format
    echo json_encode($user_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);

    // Tell the user user does not exist
    echo json_encode(array(
        "success" => false,
        "message" => "User does not exist."
    ));
}
?>