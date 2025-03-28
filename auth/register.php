<?php
// Headers
header("Access-Control-Allow-Origin: https://test.wesveld.nl");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include files
include_once '../config/database.php';
include_once '../models/user.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password)
){
    // Set user property values
    $user->username = $data->username;
    $user->email = $data->email;
    $user->password = $data->password;
    $user->created_at = date('Y-m-d H:i:s');
    
    // Check if email already exists
    if($user->emailExists()) {
        // Set response code - 400 bad request
        http_response_code(400);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "Email already exists."
        ));
        exit();
    }
    
    // Create the user
    if($user->create()){
        // Set response code - 201 created
        http_response_code(201);
        
        // Tell the user
        echo json_encode(array(
            "success" => true,
            "message" => "User was created."
        ));
    }
    else{
        // Set response code - 503 service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "Unable to create user."
        ));
    }
}
else{
    // Set response code - 400 bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to create user. Data is incomplete."
    ));
}
?>