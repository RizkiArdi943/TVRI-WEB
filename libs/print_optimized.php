<?php
/**
 * Print Optimized - Library untuk generate HTML yang dioptimalkan untuk print to PDF
 * Menggunakan pendekatan browser print to PDF yang lebih reliable
 */

class PrintOptimized {
    private $title;
    private $data;
    private $headers;
    
    public function __construct($title = 'Document') {
        $this->title = $title;
        $this->data = [];
        $this->headers = [];
    }
    
    public function loadHtml($html) {
        // Parse HTML untuk ekstrak data
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
        // Generate HTML yang dioptimalkan untuk print
        return $this->generatePrintOptimizedHtml();
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
                $this->headers = array_map('strip_tags', $headers[1]);
            }
            
            // Extract rows
            if (preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $table_html, $tbody_matches)) {
                preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbody_matches[1], $rows);
                $this->data = [];
                
                foreach ($rows[1] as $row_html) {
                    preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row_html, $cells);
                    $row_data = array_map('strip_tags', $cells[1]);
                    $this->data[] = $row_data;
                }
            }
        }
    }
    
    private function generatePrintOptimizedHtml() {
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $this->title . '</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
            background: white;
        }
        
        h1 {
            font-size: 18px;
            margin: 0 0 10px 0;
            font-weight: bold;
            color: #000;
            text-align: center;
        }
        
        .meta {
            font-size: 9px;
            color: #666;
            margin-bottom: 15px;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9px;
            table-layout: fixed;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
        }
        
        td {
            font-size: 8px;
        }
        
        .col-id { width: 12%; }
        .col-title { width: 18%; }
        .col-desc { width: 20%; }
        .col-location { width: 15%; }
        .col-category { width: 10%; }
        .col-status { width: 8%; }
        .col-priority { width: 8%; }
        .col-reporter { width: 12%; }
        .col-created { width: 8%; }
        .col-updated { width: 8%; }
        
        /* Page break handling */
        .page-break {
            page-break-before: always;
        }
        
        tbody tr {
            page-break-inside: avoid;
        }
        
        thead {
            display: table-header-group;
        }
        
        tfoot {
            display: table-footer-group;
        }
        
        /* Print styles */
        @media print {
            body {
                font-size: 9px;
            }
            
            table {
                font-size: 8px;
            }
            
            th, td {
                padding: 2px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* Screen styles */
        @media screen {
            body {
                font-size: 13px;
                max-width: 95vw; /* perbesar area pratinjau agar tabel lebih besar */
                margin: 0 auto;
                padding: 24px;
            }
            
            .print-button {
                background: #dc2626;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                margin: 20px 0;
                font-size: 14px;
                font-weight: 500;
            }
            
            .print-button:hover {
                background: #b91c1c;
                transform: translateY(-1px);
            }
            
            table {
                font-size: 13px; /* perbesar font tabel saat pratinjau */
            }
            
            th, td {
                padding: 8px; /* tambah padding agar lebih mudah dibaca */
            }
        }
    </style>
</head>
<body>
    <h1>' . $this->title . '</h1>
    <div class="meta">Diunduh: ' . date('d/m/Y H:i') . ' WIB</div>
    
    <button class="print-button no-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    
    <table>
        <thead>
            <tr>';
        
        // Add headers
        if (!empty($this->headers)) {
            $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
            foreach ($this->headers as $index => $header) {
                $class = isset($colClasses[$index]) ? $colClasses[$index] : '';
                $html .= '<th class="' . $class . '">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
            }
        }
        
        $html .= '
            </tr>
        </thead>
        <tbody>';
        
        // Add data rows
        if (!empty($this->data)) {
            $colClasses = ['col-id', 'col-title', 'col-desc', 'col-location', 'col-category', 'col-status', 'col-priority', 'col-reporter', 'col-created', 'col-updated'];
            foreach ($this->data as $row) {
                $html .= '<tr>';
                foreach ($row as $index => $cell) {
                    $class = isset($colClasses[$index]) ? $colClasses[$index] : '';
                    $html .= '<td class="' . $class . '">' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $html .= '</tr>';
            }
        }
        
        $html .= '
        </tbody>
    </table>
    
    <script>
        // Auto print jika diakses dari mobile atau parameter print=1
        if (window.location.search.includes("print=1") || window.innerWidth < 768) {
            window.print();
        }
    </script>
</body>
</html>';
        
        return $html;
    }
}
?>
