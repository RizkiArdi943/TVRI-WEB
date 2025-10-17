<?php
/**
 * Simple PDF generator menggunakan TCPDF atau fallback ke HTML dengan CSS print
 * Untuk mengatasi masalah dengan Dompdf yang kompleks
 */

class SimplePDF {
    private $html;
    private $title;
    
    public function __construct($title = 'Document') {
        $this->title = $title;
        $this->html = '';
    }
    
    public function loadHtml($html) {
        $this->html = $html;
    }
    
    public function setPaper($size = 'A4', $orientation = 'portrait') {
        // Untuk implementasi sederhana, kita hanya simpan setting
        $this->paper_size = $size;
        $this->orientation = $orientation;
    }
    
    public function render() {
        // Render HTML dengan CSS yang dioptimalkan untuk print
        $this->html = $this->addPrintStyles($this->html);
    }
    
    public function output() {
        return $this->html;
    }
    
    private function addPrintStyles($html) {
        $print_css = '
        <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            body {
                font-family: Arial, sans-serif;
                font-size: 11px;
                line-height: 1.3;
                color: #000;
                margin: 0;
                padding: 0;
            }
            h1 {
                color: #000;
                margin: 0 0 10px 0;
                font-size: 18px;
                font-weight: bold;
            }
            .meta {
                font-size: 10px;
                color: #666;
                margin-bottom: 15px;
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
                padding: 3px;
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
                max-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
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
            .no-print {
                display: none !important;
            }
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
        }
        @media screen {
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #333;
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
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
            .print-button i {
                margin-right: 8px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 12px;
                table-layout: fixed;
            }
            th, td {
                border: 1px solid #e5e7eb;
                padding: 8px;
                text-align: left;
                vertical-align: top;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            th {
                background-color: #f9fafb;
                font-weight: 600;
            }
            tr:nth-child(even) {
                background-color: #f9fafb;
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
        }
        </style>';
        
        // Inject CSS dan JavaScript untuk print
        $print_js = '
        <script>
        function printPDF() {
            window.print();
        }
        </script>';
        
        // Tambahkan button print untuk browser
        $print_button = '<button class="print-button no-print" onclick="printPDF()"><i class="fas fa-print"></i> Print / Save as PDF</button>';
        
        // Inject semua ke dalam HTML
        $html = str_replace('</head>', $print_css . '</head>', $html);
        $html = str_replace('</body>', $print_button . $print_js . '</body>', $html);
        
        return $html;
    }
}
?>
