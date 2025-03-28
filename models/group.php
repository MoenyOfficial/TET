<?php
class Group {
    // Database connection and table names
    private $conn;
    private $table_name = "groups";
    private $members_table = "group_members";
    
    // Object properties
    public $id;
    public $name;
    public $description;
    public $created_by;
    public $created_at;
    
    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create group
    function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name=:name, 
                    description=:description, 
                    created_by=:created_by, 
                    created_at=:created_at";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));
    
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":created_by", $this->created_by);
        $stmt->bindParam(":created_at", $this->created_at);
    
        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
    
        return false;
    }
    
    // Add member to group
    function addMember($user_id) {
        // Query to insert record
        $query = "INSERT INTO " . $this->members_table . "
                SET
                    group_id=:group_id, 
                    user_id=:user_id, 
                    joined_at=:joined_at";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $user_id = htmlspecialchars(strip_tags($user_id));
        $joined_at = date('Y-m-d H:i:s');
    
        // Bind values
        $stmt->bindParam(":group_id", $this->id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":joined_at", $joined_at);
    
        // Execute query
        return $stmt->execute();
    }
    
    // Get all groups a user is a member of
    function getUserGroups($user_id) {
        // Query to get all groups
        $query = "SELECT g.id, g.name, g.description, g.created_at,
                    (SELECT COUNT(*) FROM " . $this->members_table . " WHERE group_id = g.id) as member_count
                FROM " . $this->table_name . " g
                JOIN " . $this->members_table . " gm ON g.id = gm.group_id
                WHERE gm.user_id = :user_id
                ORDER BY g.created_at DESC";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Bind parameter
        $stmt->bindParam(":user_id", $user_id);
    
        // Execute query
        $stmt->execute();
    
        return $stmt;
    }
    
    // Get group details
    function getDetails() {
        // Query to read single record
        $query = "SELECT g.*, 
                    (SELECT COUNT(*) FROM " . $this->members_table . " WHERE group_id = g.id) as member_count
                FROM " . $this->table_name . " g
                WHERE g.id = ?
                LIMIT 0,1";
    
        // Prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // Bind id of group
        $stmt->bindParam(1, $this->id);
    
        // Execute query
        $stmt->execute();
    
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Set values to object properties
        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->member_count = $row['member_count'];
            return true;
        }
        
        return false;
    }
    
    // Check if user is a member of the group
    function isMember($user_id, $group_id) {
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
    
    // Get members of a group
    function getMembers($group_id) {
        // Query to get members
        $query = "SELECT u.id, u.username, u.email, gm.joined_at, g.created_by
                FROM users u
                JOIN " . $this->members_table . " gm ON u.id = gm.user_id
                JOIN " . $this->table_name . " g ON gm.group_id = g.id
                WHERE gm.group_id = :group_id
                ORDER BY gm.joined_at ASC";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $group_id = htmlspecialchars(strip_tags($group_id));
    
        // Bind parameter
        $stmt->bindParam(":group_id", $group_id);
    
        // Execute query
        $stmt->execute();
    
        return $stmt;
    }
}
?>