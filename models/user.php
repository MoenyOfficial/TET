<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // Object properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $token;
    public $token_expiry;
    public $created_at;
    
    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create user
    function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username=:username, 
                    email=:email, 
                    password=:password, 
                    created_at=:created_at";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));
    
        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        
        // Hash the password before saving
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);
        
        $stmt->bindParam(":created_at", $this->created_at);
    
        // Execute query
        if($stmt->execute()) {
            return true;
        }
    
        return false;
    }
    
    // Check if email exists
    function emailExists() {
        // Query to check if email exists
        $query = "SELECT id, username, password
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";
    
        // Prepare the query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));
    
        // Bind given email value
        $stmt->bindParam(1, $this->email);
    
        // Execute the query
        $stmt->execute();
    
        // Get number of rows
        $num = $stmt->rowCount();
    
        // If email exists, assign values to object properties for easy access and use for php sessions
        if($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Assign values to object properties
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
    
            // Return true because email exists in the database
            return true;
        }
    
        // Return false if email does not exist in the database
        return false;
    }
    
    // Update user token
    function updateToken() {
        // Query to update token
        $query = "UPDATE " . $this->table_name . "
                SET
                    token = :token,
                    token_expiry = :token_expiry
                WHERE id = :id";
    
        // Prepare the query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->token = htmlspecialchars(strip_tags($this->token));
        $this->token_expiry = htmlspecialchars(strip_tags($this->token_expiry));
        $this->id = htmlspecialchars(strip_tags($this->id));
    
        // Bind the values
        $stmt->bindParam(':token', $this->token);
        $stmt->bindParam(':token_expiry', $this->token_expiry);
        $stmt->bindParam(':id', $this->id);
    
        // Execute the query
        if($stmt->execute()) {
            return true;
        }
    
        return false;
    }
    
    // Validate token
    function validateToken() {
        // Query to check if token is valid
        $query = "SELECT id, token_expiry
                FROM " . $this->table_name . "
                WHERE token = ?
                LIMIT 0,1";
    
        // Prepare the query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->token = htmlspecialchars(strip_tags($this->token));
    
        // Bind given token value
        $stmt->bindParam(1, $this->token);
    
        // Execute the query
        $stmt->execute();
    
        // Get number of rows
        $num = $stmt->rowCount();
    
        // If token exists
        if($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Check if token is still valid
            if(isTokenValid($row['token_expiry'])) {
                return $row['id'];
            }
        }
    
        return false;
    }
    
    // Get user by ID
    function readOne() {
        // Query to read single record
        $query = "SELECT id, username, email, created_at
                FROM " . $this->table_name . "
                WHERE id = ?
                LIMIT 0,1";
    
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // Bind id of user
        $stmt->bindParam(1, $this->id);
    
        // Execute query
        $stmt->execute();
    
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Set values to object properties
        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
    
    // Search users
    function search($searchTerm, $current_user_id) {
        // Query to search users
        $query = "SELECT id, username, email
                FROM " . $this->table_name . "
                WHERE (username LIKE :search OR email LIKE :search)
                AND id != :current_user_id
                LIMIT 0,10";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $searchTerm = htmlspecialchars(strip_tags($searchTerm));
        $current_user_id = htmlspecialchars(strip_tags($current_user_id));
        
        // Add wildcards
        $searchTerm = "%{$searchTerm}%";
    
        // Bind parameters
        $stmt->bindParam(":search", $searchTerm);
        $stmt->bindParam(":current_user_id", $current_user_id);
    
        // Execute query
        $stmt->execute();
    
        return $stmt;
    }
}
?>
