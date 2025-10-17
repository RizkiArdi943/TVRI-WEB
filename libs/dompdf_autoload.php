<?php
/**
 * Simple autoloader untuk library Dompdf
 * Menggantikan vendor/autoload.php yang tidak tersedia
 */

// Base path untuk library Dompdf
$dompdf_base = __DIR__ . '/dompdf-master';

// Autoload function untuk Dompdf
spl_autoload_register(function ($class) use ($dompdf_base) {
    // Handle namespace Dompdf\
    if (strpos($class, 'Dompdf\\') === 0) {
        $class_file = str_replace('Dompdf\\', '', $class);
        $class_file = str_replace('\\', '/', $class_file);
        $file_path = $dompdf_base . '/src/' . $class_file . '.php';
        
        if (file_exists($file_path)) {
            require_once $file_path;
            return true;
        }
    }
    
    return false;
});

// Include file-file penting yang diperlukan secara manual
$required_files = [
    $dompdf_base . '/src/Helpers.php',
    $dompdf_base . '/src/Exception.php',
    $dompdf_base . '/src/Canvas.php',
    $dompdf_base . '/src/CanvasFactory.php',
    $dompdf_base . '/src/Cellmap.php',
    $dompdf_base . '/src/FontMetrics.php',
    $dompdf_base . '/src/Frame.php',
    $dompdf_base . '/src/Frame/Factory.php',
    $dompdf_base . '/src/Frame/FrameListIterator.php',
    $dompdf_base . '/src/Frame/FrameTree.php',
    $dompdf_base . '/src/Frame/FrameTreeIterator.php',
    $dompdf_base . '/src/Css/Style.php',
    $dompdf_base . '/src/Css/Stylesheet.php',
    $dompdf_base . '/src/Css/Color.php',
    $dompdf_base . '/src/Css/AttributeTranslator.php',
    $dompdf_base . '/src/FrameDecorator/AbstractFrameDecorator.php',
    $dompdf_base . '/src/FrameDecorator/Block.php',
    $dompdf_base . '/src/FrameDecorator/Inline.php',
    $dompdf_base . '/src/FrameDecorator/Text.php',
    $dompdf_base . '/src/FrameDecorator/Image.php',
    $dompdf_base . '/src/FrameDecorator/Table.php',
    $dompdf_base . '/src/FrameDecorator/TableCell.php',
    $dompdf_base . '/src/FrameDecorator/TableRow.php',
    $dompdf_base . '/src/FrameDecorator/TableRowGroup.php',
    $dompdf_base . '/src/FrameDecorator/Page.php',
    $dompdf_base . '/src/FrameDecorator/ListBullet.php',
    $dompdf_base . '/src/FrameDecorator/ListBulletImage.php',
    $dompdf_base . '/src/FrameDecorator/NullFrameDecorator.php',
    $dompdf_base . '/src/FrameReflower/AbstractFrameReflower.php',
    $dompdf_base . '/src/FrameReflower/Block.php',
    $dompdf_base . '/src/FrameReflower/Inline.php',
    $dompdf_base . '/src/FrameReflower/Text.php',
    $dompdf_base . '/src/FrameReflower/Image.php',
    $dompdf_base . '/src/FrameReflower/Table.php',
    $dompdf_base . '/src/FrameReflower/TableCell.php',
    $dompdf_base . '/src/FrameReflower/TableRow.php',
    $dompdf_base . '/src/FrameReflower/TableRowGroup.php',
    $dompdf_base . '/src/FrameReflower/Page.php',
    $dompdf_base . '/src/FrameReflower/ListBullet.php',
    $dompdf_base . '/src/FrameReflower/NullFrameReflower.php',
    $dompdf_base . '/src/Adapter/CPDF.php',
    $dompdf_base . '/src/Adapter/GD.php',
    $dompdf_base . '/src/Adapter/PDFLib.php',
    $dompdf_base . '/lib/Cpdf.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Include main Dompdf class
$dompdf_main = $dompdf_base . '/src/Dompdf.php';
if (file_exists($dompdf_main)) {
    require_once $dompdf_main;
}
