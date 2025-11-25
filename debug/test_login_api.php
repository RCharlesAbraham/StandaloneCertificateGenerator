<?php
// Test what the actual login API returns (raw output)
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login API Raw Output Test</h2>";

$email = "2301722084028@mcc.edu.in";
$password = "123456";

echo "<h3>Simulating POST request to login_process.php</h3>";

// Capture the output
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = $email;
$_POST['password'] = $password;

ob_start();
include __DIR__ . '/public/actions/login_process.php';
$output = ob_get_clean();

echo "<h4>Raw Output (with visible whitespace):</h4>";
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ccc;'>";
echo "Length: " . strlen($output) . " bytes\n";
echo "First 20 chars (hex): ";
for ($i = 0; $i < min(20, strlen($output)); $i++) {
    echo bin2hex($output[$i]) . " ";
}
echo "\n\n";
echo "OUTPUT START ↓\n";
echo htmlspecialchars($output);
echo "\n↑ OUTPUT END";
echo "</pre>";

echo "<h4>Attempting to parse as JSON:</h4>";
$json = json_decode($output);
if ($json === null) {
    echo "<p style='color:red;'>✗ JSON Parse Error: " . json_last_error_msg() . "</p>";
    
    // Find where JSON actually starts
    $json_start = strpos($output, '{');
    if ($json_start !== false && $json_start > 0) {
        echo "<p style='color:orange;'>⚠ Found " . $json_start . " bytes of output BEFORE the JSON starts!</p>";
        echo "<p>Content before JSON:</p>";
        echo "<pre style='background:#ffe6e6; padding:10px;'>";
        echo htmlspecialchars(substr($output, 0, $json_start));
        echo "</pre>";
        
        // Try parsing from where JSON actually starts
        $clean_json = substr($output, $json_start);
        $parsed = json_decode($clean_json);
        if ($parsed !== null) {
            echo "<p style='color:green;'>✓ After removing the prefix, JSON parses correctly:</p>";
            echo "<pre>" . json_encode($parsed, JSON_PRETTY_PRINT) . "</pre>";
        }
    }
} else {
    echo "<p style='color:green;'>✓ JSON parsed successfully!</p>";
    echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
}
?>
