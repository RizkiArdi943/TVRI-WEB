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
    
    public function getCasesByStatus() {
        $sql = "SELECT status, COUNT(*) as count FROM cases GROUP BY status";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByCategory() {
        $sql = "SELECT c.name as category_name, COUNT(cs.id) as count 
                FROM categories c 
                LEFT JOIN cases cs ON c.id = cs.category_id 
                GROUP BY c.id, c.name 
                ORDER BY count DESC";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByLocation() {
        $sql = "SELECT location, COUNT(*) as count 
                FROM cases 
                WHERE location IS NOT NULL AND location != '' 
                GROUP BY location 
                ORDER BY count DESC";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByLocationTrend() {
        $sql = "SELECT location, 
                       DATE_FORMAT(created_at, '%Y-%m') as month,
                       COUNT(*) as count 
                FROM cases 
                WHERE location IS NOT NULL AND location != '' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY location, DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY location, month";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByTimePeriod() {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                       COUNT(*) as count 
                FROM cases 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByQuarter() {
        $sql = "SELECT 
                    CASE 
                        WHEN MONTH(created_at) BETWEEN 1 AND 3 THEN 'Q1'
                        WHEN MONTH(created_at) BETWEEN 4 AND 6 THEN 'Q2'
                        WHEN MONTH(created_at) BETWEEN 7 AND 9 THEN 'Q3'
                        ELSE 'Q4'
                    END as quarter,
                    COUNT(*) as count
                FROM cases 
                WHERE YEAR(created_at) = YEAR(NOW())
                GROUP BY quarter
                ORDER BY quarter";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByEquipmentType() {
        $sql = "SELECT equipment_type, COUNT(*) as count 
                FROM cases 
                WHERE equipment_type IS NOT NULL AND equipment_type != '' 
                GROUP BY equipment_type 
                ORDER BY count DESC";
        $result = $this->db->query($sql);
        return $result ?: [];
    }
    
    public function getCasesByEquipmentCriticality() {
        $sql = "SELECT 
                    CASE 
                        WHEN priority = 'High' OR priority = 'Critical' THEN 'Kritis'
                        ELSE 'Non-Kritis'
                    END as criticality,
                    COUNT(*) as count
                FROM cases 
                GROUP BY criticality
                ORDER BY criticality";
        $result = $this->db->query($sql);
        return $result ?: [];
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
            'cases_by_status' => $this->getCasesByStatus(),
            'cases_by_category' => $this->getCasesByCategory(),
            'cases_by_location' => $this->getCasesByLocation(),
            'cases_by_location_trend' => $this->getCasesByLocationTrend(),
            'cases_by_time_period' => $this->getCasesByTimePeriod(),
            'cases_by_quarter' => $this->getCasesByQuarter(),
            'cases_by_equipment_type' => $this->getCasesByEquipmentType(),
            'cases_by_equipment_criticality' => $this->getCasesByEquipmentCriticality(),
            'recent_activities' => $this->getRecentActivities()
        ];
    }
}
?>
