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

// Get search term
$searchTerm = isset($_GET['q']) ? $_GET['q'] : '';

// Search users
$stmt = $user->search($searchTerm, $user_id);
$num = $stmt->rowCount();

// Check if any users found
if($num > 0) {
    // Users array
    $users_arr = array();
    
    // Retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $user_item = array(
            "id" => $id,
            "username" => $username,
            "email" => $email
        );
        
        array_push($users_arr, $user_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show users data in JSON format
    echo json_encode($users_arr);
} else {
    // Set response code - 200 OK
    http_response_code(200);
    
    // Tell the user no users found
    echo json_encode(array());
}
?>