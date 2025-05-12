<?php

class Report {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function generateVisitorReport($startDate, $endDate) {
        $query = "SELECT 
                    DATE(visit_date) as date,
                    COUNT(*) as visitor_count,
                    SUM(ticket_price) as revenue
                 FROM visits 
                 WHERE visit_date BETWEEN ? AND ?
                 GROUP BY DATE(visit_date)
                 ORDER BY date";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateExhibitionReport($exhibitionId = null) {
        $query = "SELECT 
                    e.name as exhibition_name,
                    COUNT(v.id) as visitor_count,
                    SUM(v.ticket_price) as revenue,
                    e.start_date,
                    e.end_date
                 FROM exhibitions e
                 LEFT JOIN visits v ON e.id = v.exhibition_id";
                 
        if ($exhibitionId) {
            $query .= " WHERE e.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$exhibitionId]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function generateFinancialReport($period = 'monthly') {
        $dateFormat = $period === 'monthly' ? '%Y-%m' : '%Y-%m-%d';
        
        $query = "SELECT 
                    DATE_FORMAT(transaction_date, ?) as period,
                    SUM(amount) as total_amount,
                    transaction_type
                 FROM financial_transactions
                 GROUP BY period, transaction_type
                 ORDER BY period DESC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([$dateFormat]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }
} 