<?php

namespace App\Models;

use PDO;

class Notification {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function createNotification($userId, $type, $message, $link = null) {
        $query = "INSERT INTO notifications (user_id, type, message, link, created_at, is_read) 
                 VALUES (?, ?, ?, ?, NOW(), 0)";
                 
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId, $type, $message, $link]);
    }
    
    public function getUserNotifications($userId, $limit = 10) {
        $query = "SELECT * FROM notifications 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ?";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markAsRead($notificationId) {
        $query = "UPDATE notifications 
                 SET is_read = 1 
                 WHERE id = ?";
                 
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$notificationId]);
    }
    
    public function markAllAsRead($userId) {
        $query = "UPDATE notifications 
                 SET is_read = 1 
                 WHERE user_id = ? AND is_read = 0";
                 
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$userId]);
    }
    
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count 
                 FROM notifications 
                 WHERE user_id = ? AND is_read = 0";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    public function deleteNotification($notificationId) {
        $query = "DELETE FROM notifications WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$notificationId]);
    }
} 