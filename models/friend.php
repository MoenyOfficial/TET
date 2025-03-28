<?php
class Friend {
    // Database connection and table name
    private $conn;
    private $table_name = "friends";
    
    // Object properties
    public $id;
    public $user_id;
    public $friend_id;
    
    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all friends of a user
    function getFriends($user_id) {
        // Query to get all friends
        $query = "SELECT u.id, u.username, u.email
                FROM users u
                JOIN " . $this->table_name . " f ON u.id = f.friend_id
                WHERE f.user_id = :user_id
                UNION
                SELECT u.id, u.username, u.email
                FROM users u
                JOIN " . $this->table_name . " f ON u.id = f.user_id
                WHERE f.friend_id = :user_id";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Bind parameter
        $stmt->bindParam(":user_id", $user_id);
    
        // Execute query
        $stmt->execute();
    
        return $stmt;
    }
    
    // Check if two users are already friends
    function checkFriendship() {
        // Query to check friendship
        $query = "SELECT id FROM " . $this->table_name . "
                WHERE (user_id = :user_id AND friend_id = :friend_id)
                OR (user_id = :friend_id AND friend_id = :user_id)
                LIMIT 0,1";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->friend_id = htmlspecialchars(strip_tags($this->friend_id));
    
        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":friend_id", $this->friend_id);
    
        // Execute query
        $stmt->execute();
    
        // Get number of rows
        $num = $stmt->rowCount();
    
        // If friendship exists
        if($num > 0) {
            return true;
        }
        
        return false;
    }
    
    // Add friend
    function addFriend() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id=:user_id, 
                    friend_id=:friend_id";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->friend_id = htmlspecialchars(strip_tags($this->friend_id));
    
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":friend_id", $this->friend_id);
    
        // Execute query
        if($stmt->execute()) {
            return true;
        }
    
        return false;
    }
    
    // Remove friend
    function removeFriend() {
        // Query to delete friendship
        $query = "DELETE FROM " . $this->table_name . "
                WHERE (user_id = :user_id AND friend_id = :friend_id)
                OR (user_id = :friend_id AND friend_id = :user_id)";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->friend_id = htmlspecialchars(strip_tags($this->friend_id));
    
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":friend_id", $this->friend_id);
    
        // Execute query
        if($stmt->execute()) {
            return true;
        }
    
        return false;
    }
}
?>
