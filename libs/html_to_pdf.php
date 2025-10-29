<?php
/**
 * HTML to PDF Converter - Library sederhana untuk convert HTML ke PDF
 * Menggunakan pendekatan yang lebih reliable untuk memastikan semua data muncul
 */

class HtmlToPDF {
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
        $this->paper_size = $size;
        $this->orientation = $orientation;
    }
    
    public function render() {
        // Optimize HTML untuk PDF
        $this->html = $this->optimizeHtmlForPdf($this->html);
    }
    
    public function output() {
        return $this->html;
    }
    
    private function optimizeHtmlForPdf($html) {
        // Tambahkan CSS yang dioptimalkan untuk PDF
        $pdf_css = '
        <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: bold;
            color: #000;
        }
        .meta {
            font-size: 9px;
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
        }
        </style>';
        
        // Tambahkan JavaScript untuk print
        $print_js = '
        <script>
        function printPDF() {
            window.print();
        }
        </script>';
        
        // Tambahkan button print
        $print_button = '<button onclick="printPDF()" style="background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 10px 0;">Print / Save as PDF</button>';
        
        // Inject CSS dan JavaScript
        $html = str_replace('</head>', $pdf_css . '</head>', $html);
        $html = str_replace('</body>', $print_button . $print_js . '</body>', $html);
        
        return $html;
    }
}
?>
