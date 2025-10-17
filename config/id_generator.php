<?php
/**
 * ID Laporan Generator untuk SIPETRA
 * Format: SIPETRA-[KODE_LOKASI]-[YYYYMMDD]-[NOMOR_URUT]
 */

class IDLaporanGenerator {
    private $db;
    
    // Mapping lokasi ke kode
    private $locationCodes = [
        'Transmisi Palangkaraya' => 'PLK',
        'Transmisi Sampit' => 'SMT',
        'Transmisi Pangkalanbun' => 'PGB',
        'Palangkaraya' => 'PLK',
        'Sampit' => 'SMT',
        'Pangkalanbun' => 'PGB',
        'PALANGKARAYA' => 'PLK',
        'SAMPIT' => 'SMT',
        'PANGKALANBUN' => 'PGB'
    ];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Generate ID laporan baru
     */
    public function generateID($location, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        // Get location code
        $locationCode = $this->getLocationCode($location);
        
        // Format date
        $dateFormatted = date('Ymd', strtotime($date));
        
        // Get next sequence number for this date and location
        $sequenceNumber = $this->getNextSequenceNumber($locationCode, $dateFormatted);
        
        // Format sequence number with leading zeros
        $sequenceFormatted = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);
        
        // Generate final ID
        $idLaporan = "SIPETRA-{$locationCode}-{$dateFormatted}-{$sequenceFormatted}";
        
        return $idLaporan;
    }
    
    /**
     * Get location code from location name
     */
    private function getLocationCode($location) {
        // Clean location string
        $location = trim($location);
        
        // Check exact match first
        if (isset($this->locationCodes[$location])) {
            return $this->locationCodes[$location];
        }
        
        // Check case-insensitive match
        foreach ($this->locationCodes as $key => $code) {
            if (strcasecmp($location, $key) === 0) {
                return $code;
            }
        }
        
        // Check partial match
        foreach ($this->locationCodes as $key => $code) {
            if (stripos($location, $key) !== false || stripos($key, $location) !== false) {
                return $code;
            }
        }
        
        // Default to UNK if not found
        return 'UNK';
    }
    
    /**
     * Get next sequence number for given location and date
     */
    private function getNextSequenceNumber($locationCode, $dateFormatted) {
        try {
            // Query to find the highest sequence number for this location and date
            $sql = "SELECT id_laporan FROM cases 
                    WHERE id_laporan LIKE ? 
                    ORDER BY id_laporan DESC 
                    LIMIT 1";
            
            $pattern = "SIPETRA-{$locationCode}-{$dateFormatted}-%";
            $result = $this->db->query($sql, [$pattern]);
            
            if (empty($result)) {
                // No existing records for this date and location
                return 1;
            }
            
            // Extract sequence number from the last ID
            $lastID = $result[0]['id_laporan'];
            $parts = explode('-', $lastID);
            
            if (count($parts) === 4) {
                $lastSequence = (int)$parts[3];
                return $lastSequence + 1;
            }
            
            return 1;
            
        } catch (Exception $e) {
            error_log("Error getting next sequence number: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Validate if ID is unique
     */
    public function isUnique($idLaporan) {
        try {
            $sql = "SELECT COUNT(*) as count FROM cases WHERE id_laporan = ?";
            $result = $this->db->query($sql, [$idLaporan]);
            
            return $result[0]['count'] === 0;
            
        } catch (Exception $e) {
            error_log("Error checking ID uniqueness: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all location codes (for reference)
     */
    public function getLocationCodes() {
        return $this->locationCodes;
    }
    
    /**
     * Add new location code
     */
    public function addLocationCode($location, $code) {
        $this->locationCodes[$location] = $code;
    }
}
?>

