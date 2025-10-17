<?php
/**
 * Basic PDF generator yang menghasilkan PDF binary langsung
 * Menggunakan pendekatan yang lebih sederhana
 */

class BasicPDF {
    private $content;
    private $title;
    
    public function __construct($title = 'Document') {
        $this->title = $title;
        $this->content = [];
    }
    
    public function loadHtml($html) {
        // Parse HTML sederhana untuk ekstrak data
        $this->parseHtml($html);
    }
    
    public function setPaper($size = 'A4', $orientation = 'portrait') {
        $this->paper_size = $size;
        $this->orientation = $orientation;
    }
    
    public function render() {
        // Render content
    }
    
    public function output() {
        // Generate PDF binary sederhana
        return $this->generateSimplePDF();
    }
    
    private function parseHtml($html) {
        // Extract title
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $html, $matches)) {
            $this->title = strip_tags($matches[1]);
        }
        
        // Extract table data
        if (preg_match('/<table[^>]*>(.*?)<\/table>/is', $html, $matches)) {
            $table_html = $matches[1];
            
            // Extract headers
            if (preg_match('/<thead[^>]*>(.*?)<\/thead>/is', $table_html, $thead_matches)) {
                preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $thead_matches[1], $headers);
                $this->content['headers'] = array_map('strip_tags', $headers[1]);
            }
            
            // Extract rows
            if (preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $table_html, $tbody_matches)) {
                preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbody_matches[1], $rows);
                $this->content['rows'] = [];
                
                foreach ($rows[1] as $row_html) {
                    preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row_html, $cells);
                    $this->content['rows'][] = array_map('strip_tags', $cells[1]);
                }
            }
        }
    }
    
    private function generateSimplePDF() {
        // Generate PDF menggunakan format yang lebih baik
        // Ini adalah implementasi yang menghasilkan file PDF dengan data yang jelas
        
        $pdf_content = "%PDF-1.4\n";
        $pdf_content .= "1 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Catalog\n";
        $pdf_content .= "/Pages 2 0 R\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n\n";
        
        $pdf_content .= "2 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Pages\n";
        $pdf_content .= "/Kids [3 0 R]\n";
        $pdf_content .= "/Count 1\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n\n";
        
        // Page content dengan layout yang lebih baik
        $page_content = "BT\n";
        $page_content .= "/F1 16 Tf\n";
        $page_content .= "50 750 Td\n";
        $page_content .= "(" . $this->title . ") Tj\n";
        $page_content .= "0 -30 Td\n";
        $page_content .= "ET\n";
        
        // Add date info
        $page_content .= "BT\n";
        $page_content .= "/F1 10 Tf\n";
        $page_content .= "50 700 Td\n";
        $page_content .= "(Diunduh: " . date('d/m/Y H:i') . " WIB) Tj\n";
        $page_content .= "0 -20 Td\n";
        $page_content .= "ET\n";
        
        // Add table headers dengan layout yang lebih baik
        if (isset($this->content['headers'])) {
            $x = 50;
            $y = 660;
            $page_content .= "BT\n";
            $page_content .= "/F1 9 Tf\n";
            
            // Header dengan column yang lebih lebar
            $column_widths = [80, 120, 150, 100, 60, 50, 50, 80, 60, 60];
            $current_x = $x;
            
            foreach ($this->content['headers'] as $index => $header) {
                $page_content .= $current_x . " " . $y . " Td\n";
                $page_content .= "(" . substr($header, 0, 20) . ") Tj\n";
                $current_x += $column_widths[$index] ?? 60;
            }
            $page_content .= "ET\n";
            
            // Draw line under headers
            $page_content .= "50 650 m 550 650 l S\n";
        }
        
        // Add table rows dengan layout yang lebih baik
        if (isset($this->content['rows'])) {
            $y = 630;
            $column_widths = [80, 120, 150, 100, 60, 50, 50, 80, 60, 60];
            
            foreach ($this->content['rows'] as $rowIndex => $row) {
                if ($y < 50) break; // Prevent overflow
                
                $page_content .= "BT\n";
                $page_content .= "/F1 7 Tf\n";
                
                $current_x = 50;
                foreach ($row as $index => $cell) {
                    $page_content .= $current_x . " " . $y . " Td\n";
                    // Potong text sesuai dengan lebar kolom
                    $max_chars = ($column_widths[$index] ?? 60) / 4; // Approximate chars per width
                    $cell_text = substr($cell, 0, $max_chars);
                    $page_content .= "(" . $cell_text . ") Tj\n";
                    $current_x += $column_widths[$index] ?? 60;
                }
                $page_content .= "ET\n";
                
                $y -= 15; // Reduce line height untuk lebih banyak data
            }
        }
        
        $pdf_content .= "3 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Page\n";
        $pdf_content .= "/Parent 2 0 R\n";
        $pdf_content .= "/MediaBox [0 0 612 792]\n";
        $pdf_content .= "/Contents 4 0 R\n";
        $pdf_content .= "/Resources << /Font << /F1 5 0 R >> >>\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n\n";
        
        $pdf_content .= "4 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Length " . strlen($page_content) . "\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "stream\n";
        $pdf_content .= $page_content;
        $pdf_content .= "endstream\n";
        $pdf_content .= "endobj\n\n";
        
        // Font object
        $pdf_content .= "5 0 obj\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Type /Font\n";
        $pdf_content .= "/Subtype /Type1\n";
        $pdf_content .= "/BaseFont /Helvetica\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "endobj\n\n";
        
        $pdf_content .= "xref\n";
        $pdf_content .= "0 6\n";
        $pdf_content .= "0000000000 65535 f \n";
        $pdf_content .= "0000000009 00000 n \n";
        $pdf_content .= "0000000058 00000 n \n";
        $pdf_content .= "0000000115 00000 n \n";
        $pdf_content .= "0000000274 00000 n \n";
        $pdf_content .= "0000000381 00000 n \n";
        $pdf_content .= "trailer\n";
        $pdf_content .= "<<\n";
        $pdf_content .= "/Size 6\n";
        $pdf_content .= "/Root 1 0 R\n";
        $pdf_content .= ">>\n";
        $pdf_content .= "startxref\n";
        $pdf_content .= "447\n";
        $pdf_content .= "%%EOF\n";
        
        return $pdf_content;
    }
}
?>
