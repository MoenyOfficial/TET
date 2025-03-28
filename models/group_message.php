<?php
class GroupMessage {
    // Database connection and table names
    private $conn;
    private $table_name = "group_messages";
    private $members_table = "group_members";
    
    // Object properties
    public $id;
    public $group_id;
    public $user_id;
    public $content;
    public $created_at;
    
    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Check if user is a member of the group
    function isGroupMember($user_id, $group_id) {
        // Query to check membership
        $query = "SELECT id FROM " . $this->members_table . "
                WHERE user_id = :user_id AND group_id = :group_id
                LIMIT 0,1";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $user_id = htmlspecialchars(strip_tags($user_id));
        $group_id = htmlspecialchars(strip_tags($group_id));
    
        // Bind parameters
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":group_id", $group_id);
    
        // Execute query
        $stmt->execute();
    
        // Get number of rows
        $num = $stmt->rowCount();
    
        // If membership exists
        if($num > 0) {
            return true;
        }
        
        return false;
    }
    
    // Create message
    function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    group_id=:group_id, 
                    user_id=:user_id, 
                    content=:content, 
                    created_at=:created_at";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->group_id = htmlspecialchars(strip_tags($this->group_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));
    
        // Bind values
        $stmt->bindParam(":group_id", $this->group_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":created_at", $this->created_at);
    
        // Execute query
        if($stmt->execute()) {
            return true;
        }
    
        return false;
    }
    
    // Get messages for a group
    function getMessages() {
        // Query to get messages
        $query = "SELECT gm.id, gm.group_id, gm.user_id, u.username, gm.content, gm.created_at
                FROM " . $this->table_name . " gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = :group_id
                ORDER BY gm.created_at ASC";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->group_id = htmlspecialchars(strip_tags($this->group_id));
    
        // Bind parameter
        $stmt->bindParam(":group_id", $this->group_id);
    
        // Execute query
        $stmt->execute();
    
        return $stmt;
    }
}
?>
