<?php
/**
 * System Diagnostics Tool
 * Tests all connections and components
 */

// Start output buffering
ob_start();

// Initialize results array
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'overall_status' => 'success',
    'tests' => []
];

// ==============================================
// TEST 1: PHP Configuration
// ==============================================
$phpTest = [
    'name' => 'PHP Configuration',
    'status' => 'success',
    'details' => [
        'version' => PHP_VERSION,
        'required_version' => '7.4+',
        'version_ok' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'extensions' => []
    ]
];

// Check required extensions
$requiredExtensions = ['mysqli', 'json', 'session', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $phpTest['details']['extensions'][$ext] = $loaded;
    if (!$loaded) {
        $phpTest['status'] = 'error';
        $phpTest['error'] = "Missing extension: $ext";
    }
}

$results['tests']['php'] = $phpTest;
if ($phpTest['status'] === 'error') $results['overall_status'] = 'error';

// ==============================================
// TEST 2: File Structure
// ==============================================
$fileTest = [
    'name' => 'File Structure',
    'status' => 'success',
    'details' => [
        'files' => [],
        'directories' => []
    ]
];

$requiredFiles = [
    'includes/config.php',
    'public/login.php',
    'public/register.php',
    'public/styles.css',
    'public/actions/login_process.php',
    'public/actions/register_process.php',
    'public/actions/logout.php',
    'public/admin/admin_api.php',
    'database_schema.sql'
];

foreach ($requiredFiles as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    
    $fileTest['details']['files'][$file] = [
        'exists' => $exists,
        'readable' => $readable,
        'path' => $path
    ];
    
    if (!$exists || !$readable) {
        $fileTest['status'] = 'warning';
        $fileTest['warning'] = "File issue detected: $file";
    }
}

$requiredDirs = [
    'public/actions',
    'public/admin',
    'public/assets',
    'includes',
    'scripts'
];

foreach ($requiredDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    
    $fileTest['details']['directories'][$dir] = [
        'exists' => $exists,
        'writable' => $writable,
        'path' => $path
    ];
    
    if (!$exists) {
        $fileTest['status'] = 'warning';
        $fileTest['warning'] = "Directory missing: $dir";
    }
}

$results['tests']['files'] = $fileTest;
if ($fileTest['status'] === 'error') $results['overall_status'] = 'error';

// ==============================================
// TEST 3: Config File
// ==============================================
$configTest = [
    'name' => 'Configuration File',
    'status' => 'success',
    'details' => []
];

if (file_exists(__DIR__ . '/includes/config.php')) {
    require_once __DIR__ . '/includes/config.php';
    
    $configTest['details']['file_loaded'] = true;
    $configTest['details']['db_host'] = defined('DB_HOST') ? DB_HOST : 'NOT DEFINED';
    $configTest['details']['db_name'] = defined('DB_NAME') ? DB_NAME : 'NOT DEFINED';
    $configTest['details']['db_user'] = defined('DB_USER') ? DB_USER : 'NOT DEFINED';
    $configTest['details']['db_pass_set'] = defined('DB_PASS');
    
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
        $configTest['status'] = 'error';
        $configTest['error'] = 'Database constants not properly defined';
    }
} else {
    $configTest['status'] = 'error';
    $configTest['error'] = 'Config file not found';
    $configTest['details']['file_loaded'] = false;
}

$results['tests']['config'] = $configTest;
if ($configTest['status'] === 'error') $results['overall_status'] = 'error';

// ==============================================
// TEST 4: Database Connection
// ==============================================
$dbTest = [
    'name' => 'Database Connection',
    'status' => 'success',
    'details' => []
];

if ($configTest['status'] === 'success') {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            $dbTest['status'] = 'error';
            $dbTest['error'] = 'Connection failed: ' . $conn->connect_error;
            $dbTest['details']['connected'] = false;
        } else {
            $dbTest['details']['connected'] = true;
            $dbTest['details']['host_info'] = $conn->host_info;
            $dbTest['details']['server_version'] = $conn->server_info;
            $dbTest['details']['protocol_version'] = $conn->protocol_version;
            
            // Check database exists
            $result = $conn->query("SELECT DATABASE()");
            if ($result) {
                $row = $result->fetch_row();
                $dbTest['details']['current_database'] = $row[0];
            }
            
            // Check tables
            $requiredTables = ['users', 'admins', 'certificate_logs', 'activity_logs', 'admin_logs', 'user_sessions'];
            $dbTest['details']['tables'] = [];
            
            foreach ($requiredTables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                $exists = ($result && $result->num_rows > 0);
                $dbTest['details']['tables'][$table] = ['exists' => $exists];
                
                if ($exists) {
                    // Count rows
                    $countResult = $conn->query("SELECT COUNT(*) as cnt FROM $table");
                    if ($countResult) {
                        $countRow = $countResult->fetch_assoc();
                        $dbTest['details']['tables'][$table]['row_count'] = $countRow['cnt'];
                    }
                } else {
                    $dbTest['status'] = 'warning';
                    $dbTest['warning'] = "Table missing: $table";
                }
            }
            
            // Check admin account
            $adminCheck = $conn->query("SELECT COUNT(*) as cnt FROM admins WHERE username = 'Admin@MCC'");
            if ($adminCheck) {
                $adminRow = $adminCheck->fetch_assoc();
                $dbTest['details']['default_admin_exists'] = ($adminRow['cnt'] > 0);
                
                if ($adminRow['cnt'] === 0) {
                    $dbTest['status'] = 'warning';
                    $dbTest['warning'] = 'Default admin account not found';
                }
            }
            
            $conn->close();
        }
    } catch (Exception $e) {
        $dbTest['status'] = 'error';
        $dbTest['error'] = 'Exception: ' . $e->getMessage();
        $dbTest['details']['connected'] = false;
    }
} else {
    $dbTest['status'] = 'skipped';
    $dbTest['error'] = 'Skipped due to config errors';
}

$results['tests']['database'] = $dbTest;
if ($dbTest['status'] === 'error') $results['overall_status'] = 'error';

// ==============================================
// TEST 5: Session Functionality
// ==============================================
$sessionTest = [
    'name' => 'Session Management',
    'status' => 'success',
    'details' => []
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionTest['details']['session_started'] = (session_status() === PHP_SESSION_ACTIVE);
$sessionTest['details']['session_id'] = session_id();
$sessionTest['details']['session_name'] = session_name();
$sessionTest['details']['save_path'] = session_save_path();

// Test session write
$_SESSION['diagnostic_test'] = 'test_value_' . time();
$sessionTest['details']['can_write'] = isset($_SESSION['diagnostic_test']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionTest['status'] = 'error';
    $sessionTest['error'] = 'Cannot start session';
}

$results['tests']['session'] = $sessionTest;
if ($sessionTest['status'] === 'error') $results['overall_status'] = 'error';

// ==============================================
// TEST 6: CSS Files
// ==============================================
$cssTest = [
    'name' => 'CSS Files',
    'status' => 'success',
    'details' => [
        'files' => []
    ]
];

$cssFiles = [
    'public/styles.css'
];

foreach ($cssFiles as $cssFile) {
    $path = __DIR__ . '/' . $cssFile;
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    $size = $exists ? filesize($path) : 0;
    
    $cssTest['details']['files'][$cssFile] = [
        'exists' => $exists,
        'readable' => $readable,
        'size' => $size,
        'size_readable' => $size > 0 ? round($size / 1024, 2) . ' KB' : '0 KB'
    ];
    
    if (!$exists || $size === 0) {
        $cssTest['status'] = 'warning';
        $cssTest['warning'] = "CSS file issue: $cssFile";
    }
}

$results['tests']['css'] = $cssTest;

// ==============================================
// TEST 7: JavaScript Functionality
// ==============================================
$jsTest = [
    'name' => 'JavaScript Files',
    'status' => 'success',
    'details' => [
        'note' => 'JavaScript is client-side. Check browser console for errors.',
        'inline_scripts' => []
    ]
];

// Check if pages have inline scripts
$pagesWithJS = [
    'public/login.php',
    'public/register.php'
];

foreach ($pagesWithJS as $page) {
    $path = __DIR__ . '/' . $page;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $hasScript = (strpos($content, '<script>') !== false || strpos($content, '<script ') !== false);
        $hasFetch = (strpos($content, 'fetch(') !== false);
        
        $jsTest['details']['inline_scripts'][$page] = [
            'has_script_tags' => $hasScript,
            'uses_fetch_api' => $hasFetch
        ];
    }
}

$results['tests']['javascript'] = $jsTest;

// ==============================================
// TEST 8: API Endpoints
// ==============================================
$apiTest = [
    'name' => 'API Endpoints',
    'status' => 'success',
    'details' => [
        'endpoints' => []
    ]
];

$apiEndpoints = [
    'public/actions/login_process.php',
    'public/actions/register_process.php',
    'public/actions/logout.php',
    'public/actions/log_certificate.php',
    'public/admin/admin_api.php',
    'public/admin/admin_login_process.php'
];

foreach ($apiEndpoints as $endpoint) {
    $path = __DIR__ . '/' . $endpoint;
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    
    $apiTest['details']['endpoints'][$endpoint] = [
        'exists' => $exists,
        'readable' => $readable,
        'path' => $path
    ];
    
    if (!$exists) {
        $apiTest['status'] = 'warning';
        $apiTest['warning'] = "API endpoint missing: $endpoint";
    }
}

$results['tests']['api'] = $apiTest;

// ==============================================
// Clean output buffer
// ==============================================
ob_end_clean();

// ==============================================
// Output Results
// ==============================================
header('Content-Type: application/json; charset=utf-8');
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
