<?php
header('Content-Type: application/json');

$debugFile = __DIR__ . '/../debug/login_process.log';
$message = "[" . date('Y-m-d H:i:s') . "] debug_test write\n";
$ok = @file_put_contents($debugFile, $message, FILE_APPEND);

echo json_encode([
    'php_sapi' => php_sapi_name(),
    'php_version' => phpversion(),
    'debug_file' => $debugFile,
    'write_success' => ($ok !== false),
    'bytes_written' => $ok
]);

?>
