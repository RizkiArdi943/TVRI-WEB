<?php
require_once __DIR__ . '/../config/database.php';

class DashboardStats {
    private $db;
    
    public function __construct() {
        global $database;
        $this->db = $database;
    }
    
    public function getTotalCases() {
        $sql = "SELECT COUNT(*) as total FROM cases";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }
    
    public function getTodayCases() {
        $sql = "SELECT COUNT(*) as total FROM cases WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }
    
    public function getThisMonthCases() {
        $sql = "SELECT COUNT(*) as total FROM cases WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $result = $this->db->query($sql);
        return $result[0]['total'] ?? 0;
    }
    
    
    public function getRecentActivities($limit = 5) {
        $sql = "SELECT c.*, cat.name as category_name, u.full_name as reporter_name 
                FROM cases c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                LEFT JOIN users u ON c.reported_by = u.id 
                ORDER BY c.created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$limit]);
    }
    
    public function getAllStats() {
        return [
            'total_cases' => $this->getTotalCases(),
            'today_cases' => $this->getTodayCases(),
            'month_cases' => $this->getThisMonthCases(),
            'recent_activities' => $this->getRecentActivities()
        ];
    }
}
?>
