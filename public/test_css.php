<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test - Certificate Generator</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .status-ok { background: #e8f5e9; border-color: #4caf50; }
        .status-fail { background: #ffebee; border-color: #f44336; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 style="color: #67150a;">CSS Test Page</h1>
        <p>This page tests if styles.css is loading correctly.</p>

        <div id="results"></div>

        <h2 style="margin-top: 30px;">Test Components:</h2>

        <!-- Test Login Card -->
        <div class="login-card" style="margin: 20px 0;">
            <h3>Login Card Test</h3>
            <div class="form-group">
                <label>Email</label>
                <input type="email" placeholder="test@example.com">
            </div>
            <button class="btn btn-primary">Test Button</button>
        </div>

        <!-- Test Register Components -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>Radio Button Test</h3>
            <div class="user-type-options">
                <div class="radio-option">
                    <input type="radio" name="test" id="test1" checked>
                    <label for="test1">Option 1</label>
                </div>
                <div class="radio-option">
                    <input type="radio" name="test" id="test2">
                    <label for="test2">Option 2</label>
                </div>
            </div>
        </div>

        <!-- Test Alert -->
        <div id="alertBox">
            <div class="alert alert-success">Success alert test</div>
            <div class="alert alert-error">Error alert test</div>
        </div>

        <hr style="margin: 30px 0;">

        <h2>File Checks:</h2>
        <div id="fileChecks"></div>
    </div>

    <script>
        // Check if CSS is loaded
        const results = document.getElementById('results');
        const fileChecks = document.getElementById('fileChecks');

        // Test 1: Check if styles are applied
        function checkCSS() {
            const testBtn = document.querySelector('.btn-primary');
            const computedStyle = window.getComputedStyle(testBtn);
            const bgColor = computedStyle.backgroundColor;
            
            let html = '<div class="test-item">';
            
            if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                html += '<div class="status-ok">✅ CSS Loaded Successfully!</div>';
                html += '<p>Button background color detected: ' + bgColor + '</p>';
            } else {
                html += '<div class="status-fail">❌ CSS NOT Loading!</div>';
                html += '<p>styles.css file is not being applied.</p>';
            }
            
            html += '</div>';
            results.innerHTML = html;
        }

        // Test 2: Check file accessibility
        async function checkFiles() {
            const files = [
                'styles.css',
                'script.js',
                'actions/login_process.php',
                'actions/register_process.php'
            ];

            let html = '';

            for (const file of files) {
                try {
                    const response = await fetch(file, { method: 'HEAD' });
                    if (response.ok) {
                        html += `<div class="test-item status-ok">✅ ${file} - Accessible</div>`;
                    } else {
                        html += `<div class="test-item status-fail">❌ ${file} - Status: ${response.status}</div>`;
                    }
                } catch (error) {
                    html += `<div class="test-item status-fail">❌ ${file} - Error: ${error.message}</div>`;
                }
            }

            fileChecks.innerHTML = html;
        }

        // Run tests
        window.addEventListener('load', () => {
            setTimeout(checkCSS, 500);
            checkFiles();
        });
    </script>
</body>
</html>
