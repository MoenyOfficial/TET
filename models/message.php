<?php
class Message {
    // Database connection and table name
    private $conn;
    private $table_name = "messages";
    
    // Object properties
    public $id;
    public $sender_id;
    public $receiver_id;
    public $content;
    public $created_at;
    
    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create message
    function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    sender_id=:sender_id, 
                    receiver_id=:receiver_id, 
                    content=:content, 
                    created_at=:created_at";
    
        // Prepare query
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $this->sender_id = htmlspecialchars(strip_tags($this->sender_id));
        $this->receiver_id = htmlspecialchars(strip_tags($this->receiver_id));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));
    
        // Bind values
        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":created_at", $this->created_at);
    
        // Execute query
        if($stmt->execute()) {
            return true;
        }
    
        return false;
    }
    
    // Get messages between two users
    function getMessages($user1, $user2) {
        // Query to get messages
        $query = "SELECT m.id, m.sender_id, m.receiver_id, m.content, m.created_at,
                    u_sender.username as sender_name, u_receiver.username as receiver_name
                FROM " . $this->table_name . " m
                JOIN users u_sender ON m.sender_id = u_sender.id
                JOIN users u_receiver ON m.receiver_id = u_receiver.id
                WHERE (m.sender_id = :user1 AND m.receiver_id = :user2)
                OR (m.sender_id = :user2 AND m.receiver_id = :user1)
                ORDER BY m.created_at ASC";
    
        // Prepare statement
        $stmt = $this->conn->prepare($query);
    
        // Sanitize
        $user1 = htmlspecialchars(strip_tags($user1));
        $user2 = htmlspecialchars(strip_tags($user2));
    
        // Bind parameters
        $stmt->bindParam(":user1", $user1);
        $stmt->bindParam(":user2", $user2);
    
        // Execute query
        $stmt->execute();
    
        return $stmt;
    }
}
?>