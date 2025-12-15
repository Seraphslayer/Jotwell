<?php
if (!isset($_GET['path'])) {
    http_response_code(400);
    echo "Missing 'path' parameter.";
    exit;
}

$pdfPath = $_GET['path'];
// Remove any leading slashes and normalize path
$pdfPath = ltrim($pdfPath, '/\\');
$pdfPath = preg_replace('/\.+/', '', $pdfPath); // Prevent directory traversal
// Fix: Use basename without extension for safety, then append .pdf
$base = pathinfo($_GET['path'], PATHINFO_FILENAME);
$pdfPath = 'uploads/' . $base . '.pdf';
$pdfPath = __DIR__ . '/' . $pdfPath;

if (!file_exists($pdfPath)) {
    http_response_code(404);
    echo "PDF file not found at: $pdfPath";
    exit;
}

// Use pdftotext (poppler-utils) or fallback to shell command
$text = '';
$output = [];
$return_var = 0;
$cmd = "pdftotext -f 1 -l 1 " . escapeshellarg($pdfPath) . " -";
exec($cmd . " 2>&1", $output, $return_var);
if ($return_var !== 0) {
    http_response_code(500);
    echo "pdftotext error: " . implode("\n", $output);
    exit;
}
if (is_array($output) && count($output) > 0) {
    $text = implode("\n", $output);
}

if (empty($text)) {
    http_response_code(500);
    echo "Failed to extract text from PDF. pdftotext output: " . implode("\n", $output);
    exit;
}

header('Content-Type: application/json');
echo json_encode(["text" => $text]);
exit;
?>