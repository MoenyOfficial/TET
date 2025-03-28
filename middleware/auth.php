<?php
// Function to authenticate user via token
function authenticate() {
    // Get HTTP Authorization header
    $headers = getallheaders();
    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Check if token exists
    if (!$auth_header) {
        // Set response code - 401 Unauthorized
        http_response_code(401);
        
        // Tell the user access denied
        echo json_encode(array(
            "success" => false,
            "message" => "Access denied. Token is required."
        ));
        exit();
    }
    
    // Extract token from Authorization header (Bearer token)
    $token = trim(str_replace('Bearer', '', $auth_header));
    
    // Include database and user model
    include_once dirname(__FILE__) . '/../config/database.php';
    include_once dirname(__FILE__) . '/../models/user.php';
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Initialize user object
    $user = new User($db);
    
    // Set token property
    $user->token = $token;
    
    // Validate token
    $user_id = $user->validateToken();
    
    if (!$user_id) {
        // Set response code - 401 Unauthorized
        http_response_code(401);
        
        // Tell the user access denied
        echo json_encode(array(
            "success" => false,
            "message" => "Access denied. Invalid or expired token."
        ));
        exit();
    }
    
    // Return user ID if token is valid
    return $user_id;
}
?>