<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include files
include_once '../config/database.php';
include_once '../config/auth.php';
include_once '../models/user.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->email) && !empty($data->password)) {
    // Set user email
    $user->email = $data->email;
    
    // Check if email exists
    if($user->emailExists()) {
        // Verify password
        if(password_verify($data->password, $user->password)) {
            // Generate token
            $user->token = generateToken();
            $user->token_expiry = generateTokenExpiry();
            
            // Update user token
            if($user->updateToken()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Create response array
                $response = array(
                    "success" => true,
                    "message" => "Login successful.",
                    "user" => array(
                        "id" => $user->id,
                        "username" => $user->username,
                        "email" => $data->email
                    ),
                    "token" => $user->token
                );
                
                // Send response
                echo json_encode($response);
                exit();
            } else {
                // Set response code - 500 Internal Server Error
                http_response_code(500);
                
                // Tell the user
                echo json_encode(array(
                    "success" => false,
                    "message" => "Unable to update token."
                ));
                exit();
            }
        } else {
            // Set response code - 401 Unauthorized
            http_response_code(401);
            
            // Tell the user
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid credentials."
            ));
            exit();
        }
    } else {
        // Set response code - 401 Unauthorized
        http_response_code(401);
        
        // Tell the user
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid credentials."
        ));
        exit();
    }
} else {
    // Set response code - 400 Bad Request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to login. Data is incomplete."
    ));
}
?>