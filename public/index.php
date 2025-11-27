<?php
require_once __DIR__ . '/../includes/config.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_type = $_SESSION['user_type'] ?? '';

// Fetch additional user details for account modal
$user_phone = '';
$user_regno = '';
if (isset($_SESSION['user_id'])) {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT phone_no, reg_no FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_phone = $row['phone_no'] ?? '';
        $user_regno = $row['reg_no'] ?? '';
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Modal Notification -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="modalIcon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <h3 id="modalTitle">Notification</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button id="modalOk" class="btn btn-primary">OK</button>
                <button id="modalCancel" class="btn btn-secondary" style="display:none;">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Text Editor Modal -->
    <div id="textEditorModal" class="modal" style="display:none;">
        <div id="textEditorModalContent" class="modal-content" style="max-width:500px; cursor:move;">
            <div class="modal-header" id="textEditorModalHeader" style="cursor:move; user-select:none;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="4 7 4 4 20 4 20 7"></polyline>
                    <line x1="9" y1="20" x2="15" y2="20"></line>
                    <line x1="12" y1="4" x2="12" y2="20"></line>
                </svg>
                <h3 id="textEditorTitle">Edit Text Properties</h3>
                <button class="modal-close" id="textEditorClose">&times;</button>
            </div>
            <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                <!-- Preview -->
                <div style="margin-bottom:20px; padding:20px; background:#f8f9fa; border-radius:8px; text-align:center; min-height:80px; display:flex; align-items:center; justify-content:center; border:1px solid #dee2e6;">
                    <div id="textEditorPreview" style="transition:all 0.2s;">Sample Text</div>
                </div>

                <!-- Font Family -->
                <div class="form-group">
                    <label for="textEditorFontFamily" style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;">
                            <polyline points="4 7 4 4 20 4 20 7"></polyline>
                            <line x1="9" y1="20" x2="15" y2="20"></line>
                            <line x1="12" y1="4" x2="12" y2="20"></line>
                        </svg>
                        Font Family
                    </label>
                    <select id="textEditorFontFamily" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
                        <option value="Arial">Arial</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="'Times New Roman', serif">Times New Roman</option>
                        <option value="'Courier New', monospace">Courier New</option>
                        <option value="Verdana, sans-serif">Verdana</option>
                        <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                        <option value="Impact, sans-serif">Impact</option>
                    </select>
                </div>

                <!-- Font Size -->
                <div class="form-group">
                    <label for="textEditorFontSize" style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Font Size
                        </span>
                        <span id="textEditorFontSizeValue" style="font-weight:normal; color:#666; font-size:12px;">60px</span>
                    </label>
                    <input type="range" id="textEditorFontSize" min="10" max="120" value="60" style="width:100%;">
                </div>

                <!-- Font Color -->
                <div class="form-group">
                    <label for="textEditorFontColor" style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;">
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                        Font Color
                    </label>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="color" id="textEditorFontColor" value="#000000" style="width:60px; height:36px; border:1px solid #ddd; border-radius:4px; cursor:pointer;">
                        <input type="text" id="textEditorFontColorHex" value="#000000" placeholder="#000000" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:4px; font-size:13px; font-family:monospace;">
                    </div>
                </div>

                <!-- Letter Spacing -->
                <div class="form-group">
                    <label for="textEditorLetterSpacing" style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                        <span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                                <polyline points="12 5 5 12 12 19"></polyline>
                            </svg>
                            Letter Spacing
                        </span>
                        <span id="textEditorLetterSpacingValue" style="font-weight:normal; color:#666; font-size:12px;">0px</span>
                    </label>
                    <input type="range" id="textEditorLetterSpacing" min="-5" max="20" value="0" step="0.5" style="width:100%;">
                </div>

                <!-- Font Style -->
                <div class="form-group">
                    <label style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;">
                            <line x1="19" y1="4" x2="10" y2="4"></line>
                            <line x1="14" y1="20" x2="5" y2="20"></line>
                            <line x1="15" y1="4" x2="9" y2="20"></line>
                        </svg>
                        Font Style
                    </label>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <label style="display:flex; align-items:center; gap:4px; cursor:pointer; padding:6px 12px; border:1px solid #ddd; border-radius:4px; font-size:12px; background:#fff; transition:all 0.2s;">
                            <input type="checkbox" id="textEditorBold" style="cursor:pointer;">
                            <strong>Bold</strong>
                        </label>
                        <label style="display:flex; align-items:center; gap:4px; cursor:pointer; padding:6px 12px; border:1px solid #ddd; border-radius:4px; font-size:12px; background:#fff; transition:all 0.2s;">
                            <input type="checkbox" id="textEditorItalic" style="cursor:pointer;">
                            <em>Italic</em>
                        </label>
                        <label style="display:flex; align-items:center; gap:4px; cursor:pointer; padding:6px 12px; border:1px solid #ddd; border-radius:4px; font-size:12px; background:#fff; transition:all 0.2s;">
                            <input type="checkbox" id="textEditorUnderline" style="cursor:pointer;">
                            <u>Underline</u>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; gap:10px; justify-content:flex-end;">
                <button id="textEditorCancel" class="btn btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancel
                </button>
                <button id="textEditorSave" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Top Navigation Bar -->
    <!-- Help Modal (compact) -->
    <div id="helpModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:520px;">
            <div class="modal-header">
                <h3 style="margin:0;">How this app works</h3>
                <button class="modal-close" id="helpClose" style="font-size:20px;">&times;</button>
            </div>
            <div class="modal-body" style="font-size:13px; color:#444; line-height:1.45; padding-top:8px;">
                <div style="margin-bottom:6px;"><strong>Move:</strong> Click and drag any placeholder.</div>
                <div style="margin-bottom:6px;"><strong>Edit:</strong> Double-click text to open editor.</div>
                <div style="margin-bottom:6px;"><strong>Delete:</strong> Select element and press Delete/Backspace.</div>
                <div style="margin-bottom:6px;"><strong>Zoom:</strong> Use mouse wheel while pressing Ctrl, or use Ctrl + +/- keys.</div>
                <div style="margin-bottom:6px;"><strong>Grid:</strong> Toggle grid from the canvas toolbar when needed.</div>
                <div style="margin-top:8px; color:#666; font-size:12px;">Tip: use the Generate button to export a single certificate or upload an Excel for bulk generation.</div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px; margin-top:10px;">
                <button id="helpGotIt" class="btn btn-primary">Got it</button>
            </div>
        </div>
    </div>

    <!-- Account Details Modal -->
    <div id="accountModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:520px;">
            <div class="modal-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <h3 style="margin:0;">Account Details</h3>
                <button class="modal-close" id="accountClose" style="font-size:20px;">&times;</button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <!-- Non-editable fields -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">Name</label>
                    <input type="text" id="accountName" readonly style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:6px; background:#f5f5f5; color:#666; font-size:14px; cursor:not-allowed;">
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">College Email</label>
                    <input type="text" id="accountEmail" readonly style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:6px; background:#f5f5f5; color:#666; font-size:14px; cursor:not-allowed;">
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">Phone Number</label>
                    <input type="text" id="accountPhone" readonly style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:6px; background:#f5f5f5; color:#666; font-size:14px; cursor:not-allowed;">
                </div>

                <div class="form-group" id="accountRegNoGroup" style="margin-bottom:16px; display:none;">
                    <label style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:block;">Registration Number</label>
                    <input type="text" id="accountRegNo" readonly style="width:100%; padding:10px; border:1px solid #e0e0e0; border-radius:6px; background:#f5f5f5; color:#666; font-size:14px; cursor:not-allowed;">
                </div>

                <div class="divider" style="height:1px; background:#e0e0e0; margin:20px 0;"></div>

                <!-- Password change section -->
                <div style="margin-bottom:16px;">
                    <h4 style="font-size:14px; font-weight:600; color:#67150a; margin-bottom:12px;">Change Password</h4>
                    
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="currentPassword" style="font-size:13px; font-weight:500; color:#333; margin-bottom:6px; display:block;">Current Password</label>
                        <input type="password" id="currentPassword" placeholder="Enter current password" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="newPassword" style="font-size:13px; font-weight:500; color:#333; margin-bottom:6px; display:block;">New Password</label>
                        <input type="password" id="newPassword" placeholder="Enter new password" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="confirmPassword" style="font-size:13px; font-weight:500; color:#333; margin-bottom:6px; display:block;">Confirm New Password</label>
                        <input type="password" id="confirmPassword" placeholder="Confirm new password" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                    </div>

                    <div id="passwordMessage" style="display:none; padding:10px; border-radius:6px; font-size:13px; margin-top:10px;"></div>
                </div>
            </div>
            <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:8px;">
                <button id="accountCancelBtn" class="btn btn-secondary">Cancel</button>
                <button id="changePasswordBtn" class="btn btn-primary">Change Password</button>
            </div>
        </div>
    </div>

    <nav class="top-navbar">
        <div class="navbar-brand">
            <div class="logo-placeholder" title="Click to change logo" style="padding-bottom:10px; padding-top:10px; display:flex;align-items:center;gap:8px;cursor:pointer;">
                <label for="logoUpload" style="display:inline-block; margin:0;">
                    <img id="logoImg" src="assets/MMC-LOGO-2-229x300.png" alt="Logo placeholder"
                        style="max-width:180px; max-height:260px; object-fit:contain; border-radius:4px; transform:translateY(4px);">
                    </label
                <input type="file" id="logoUpload" accept="image/*" style="display:none;">
            </div>
        </div>

            <div class="header">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                <h1>Certificate Generator</h1>
            </div>

        <div class="navbar-user">
            <div class="user-info" id="userInfo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span id="userName"><?php echo htmlspecialchars($user_name); ?></span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-icon">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="dropdown-item" id="accountDetails">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 1v6m0 6v6m-9-9h6m6 0h6" />
                    </svg>
                    Account Details
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item" id="logoutBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Logout
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Left Sidebar - 25% -->
        <div class="sidebar">


    <div class="header-section" style="padding-right:10px;">
        <h3>Edit Panel</h3>
    </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="certNo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <line x1="9" y1="9" x2="15" y2="9" />
                        </svg>
                        Certificate No:
                    </label>
                    <input type="text" id="certNo" placeholder="Enter certificate number">
                </div>

                <div class="form-group">
                    <label for="name">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Name:
                    </label>
                    <input type="text" id="name" placeholder="Enter name">
                </div>

                <div class="form-group">
                    <label for="certifiedFor">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Certified For:
                    </label>
                    <input type="text" id="certifiedFor" placeholder="Enter certification details">
                </div>

                <div class="form-row" style="display:flex; gap:12px; align-items:flex-end;">
                    <div class="form-group" style="flex:1; min-width:0;">
                        <label for="fromDate">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            From Date:
                        </label>
                        <input type="date" id="fromDate" style="width:100%;">
                    </div>

                    <div class="form-group" style="flex:1; min-width:0;">
                        <label for="toDate">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            To Date:
                        </label>
                        <input type="date" id="toDate" style="width:100%;">
                    </div>
                </div>
            

                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button id="insertTextBtn" class="btn btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Insert Text
                    </button>

                    <div id="customTextsContainer" style="display:none; margin:0;"></div>

                    <button id="generateBtn" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        Generate PDF
                    </button>
                </div>

                <div class="divider"></div>

                <!-- Signature Image Uploads -->
                <div style="margin-bottom:16px;">
                    <h3 style="font-size:13px; font-weight:600; color:#67150a; margin-bottom:6px; display:flex; align-items:center; gap:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Signature Images
                    </h3>
                    <p style="font-size:11px; color:#666; margin-bottom:10px; line-height:1.4;">
                        Upload PNG signature images. Click "+" to add more (up to 5).
                    </p>
                    
                    <!-- Signature inputs in vertical order with remove buttons -->
                    <div id="signaturesContainer" style="display:flex; flex-direction:column; gap:10px;">
                        <!-- Signature 1 - always visible, no remove button -->
                        <div class="form-group" style="margin-bottom:0; display:flex; gap:8px; align-items:flex-start;">
                            <div style="flex:1;">
                                <label for="signature1Upload" style="font-size:11px; display:block; margin-bottom:4px;">Signature 1:</label>
                                <input type="file" id="signature1Upload" accept="image/png" style="width:100%; font-size:11px;">
                                <span id="sig1Preview" style="font-size:10px; color:#666; display:block; margin-top:2px;"></span>
                            </div>
                        </div>

                        <!-- Signature 2 - hidden by default -->
                        <div class="form-group" id="sig2Group" style="margin-bottom:0; display:none; gap:8px; align-items:flex-start;">
                            <div style="flex:1;">
                                <label for="signature2Upload" style="font-size:11px; display:block; margin-bottom:4px;">Signature 2:</label>
                                <input type="file" id="signature2Upload" accept="image/png" style="width:100%; font-size:11px;">
                                <span id="sig2Preview" style="font-size:10px; color:#666; display:block; margin-top:2px;"></span>
                            </div>
                            <button type="button" class="btn btn-secondary remove-sig-btn" data-sig="2" title="Remove signature 2" style="padding:6px 10px; height:36px; margin-top:18px; background:#d32f2f; color:white; border-color:#d32f2f;">
                                <strong>−</strong>
                            </button>
                        </div>

                        <!-- Signature 3 - hidden by default -->
                        <div class="form-group" id="sig3Group" style="margin-bottom:0; display:none; gap:8px; align-items:flex-start;">
                            <div style="flex:1;">
                                <label for="signature3Upload" style="font-size:11px; display:block; margin-bottom:4px;">Signature 3:</label>
                                <input type="file" id="signature3Upload" accept="image/png" style="width:100%; font-size:11px;">
                                <span id="sig3Preview" style="font-size:10px; color:#666; display:block; margin-top:2px;"></span>
                            </div>
                            <button type="button" class="btn btn-secondary remove-sig-btn" data-sig="3" title="Remove signature 3" style="padding:6px 10px; height:36px; margin-top:18px; background:#d32f2f; color:white; border-color:#d32f2f;">
                                <strong>−</strong>
                            </button>
                        </div>

                        <!-- Signature 4 - hidden by default -->
                        <div class="form-group" id="sig4Group" style="margin-bottom:0; display:none; gap:8px; align-items:flex-start;">
                            <div style="flex:1;">
                                <label for="signature4Upload" style="font-size:11px; display:block; margin-bottom:4px;">Signature 4:</label>
                                <input type="file" id="signature4Upload" accept="image/png" style="width:100%; font-size:11px;">
                                <span id="sig4Preview" style="font-size:10px; color:#666; display:block; margin-top:2px;"></span>
                            </div>
                            <button type="button" class="btn btn-secondary remove-sig-btn" data-sig="4" title="Remove signature 4" style="padding:6px 10px; height:36px; margin-top:18px; background:#d32f2f; color:white; border-color:#d32f2f;">
                                <strong>−</strong>
                            </button>
                        </div>

                        <!-- Signature 5 - hidden by default -->
                        <div class="form-group" id="sig5Group" style="margin-bottom:0; display:none; gap:8px; align-items:flex-start;">
                            <div style="flex:1;">
                                <label for="signature5Upload" style="font-size:11px; display:block; margin-bottom:4px;">Signature 5:</label>
                                <input type="file" id="signature5Upload" accept="image/png" style="width:100%; font-size:11px;">
                                <span id="sig5Preview" style="font-size:10px; color:#666; display:block; margin-top:2px;"></span>
                            </div>
                            <button type="button" class="btn btn-secondary remove-sig-btn" data-sig="5" title="Remove signature 5" style="padding:6px 10px; height:36px; margin-top:18px; background:#d32f2f; color:white; border-color:#d32f2f;">
                                <strong>−</strong>
                            </button>
                        </div>
                    </div>

                    <!-- Add button -->
                    <div style="margin-top:10px;">
                        <button id="addSignatureBtn" type="button" class="btn btn-secondary" title="Add signature" style="padding:6px 12px;">
                            <strong>＋</strong> Add Signature
                        </button>
                    </div>
                </div>

                <div class="divider"></div>


                <div class="bulk-data-section compact" style="padding:10px 10px; margin-bottom:6px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                            <line x1="12" y1="22.08" x2="12" y2="12" />
                        </svg>
                        <h3 style="font-size:12px; font-weight:600; color:#67150a; margin:0;">For Bulk Data</h3>
                    </div>

                    <div class="bulk-buttons">
                        <a href="Demo Excel/Excel_Blueprint.xlsx" download="Excel_Blueprint.xlsx"
                           class="btn btn-secondary"
                           style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            Template
                        </a>

                           <label for="excelFile"
                               class="btn btn-secondary"
                               style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                            Choose Excel To Upload Data
                        </label>
                        <input type="file" id="excelFile" accept=".xlsx,.xls" style="display: none;">
                        <span id="excel-name" class="file-name excel-name">
                        </span>

                        <button id="generateAllBtn" class="btn btn-primary generate-btn"
                            style="display:none;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <path d="M8 13h8M8 17h8" />
                            </svg>
                            Generate
                        </button>
                    </div>

                    <div id="progress-container" style="display: none; margin-top:8px;">
                        <div style="font-size:11px; color:#67150a; margin-bottom:4px;">
                            <span id="progress-text">Generating... 0/0</span>
                        </div>
                        <div style="width:100%; height:4px; background:#e0e0e0; border-radius:2px; overflow:hidden;">
                            <div id="progress-bar" style="width:0%; height:100%; background:#67150a; transition:width 0.2s;"></div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Replaced large Canvas Controls block with a compact Help button + modal -->
                <div class="instructions-section controls-compact">
                    <div class="controls-left">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div style="font-size:13px; font-weight:600; color:#67150a;">Controls</div>
                    </div>

                    <div>
                        <button id="openHelpBtn" class="btn btn-secondary help-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 1 1 5.83 1c0 1.5-2 2.25-2 2.25"></path>
                                <line x1="12" y1="17" x2="12" y2="17"></line>
                            </svg>
                            Help
                        </button>
                    </div>
                </div>
            </div>
        </div>



        <!-- Right Canvas - 75% -->
        <div class="canvas-area">
            <div class="canvas-header">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                        <line x1="8" y1="21" x2="16" y2="21" />
                        <line x1="12" y1="17" x2="12" y2="21" />
                    </svg>
                    Live Preview
                </h2>
                <div class="zoom-controls">
                    <div class="template-upload">
                    <label for="templateUpload" class="zoom-btn" title="Change Template" style="cursor: pointer; display:inline-flex; align-items:center; gap:8px; position:relative;">
                       
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                        <input type="file" id="templateUpload" accept="image/*" style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;" aria-label="Upload template image">
                    </label>
                    <span style="-webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none; cursor:default;" aria-hidden="true">Change the Template</span>
                    </div>
                    <div class="zoom-divider"></div>
                    <button id="zoomOut" class="zoom-btn" title="Zoom Out (Ctrl + -)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            <line x1="8" y1="11" x2="14" y2="11" />
                        </svg>
                    </button>
                    <span id="zoomLevel" class="zoom-level">100%</span>
                    <button id="zoomIn" class="zoom-btn" title="Zoom In (Ctrl + +)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            <line x1="11" y1="8" x2="11" y2="14" />
                            <line x1="8" y1="11" x2="14" y2="11" />
                        </svg>
                    </button>
                    <button id="zoomReset" class="zoom-btn" title="Reset Zoom (Ctrl + 0)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="canvas-container" id="canvasContainer">
                <canvas id="certificateCanvas"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Set session storage from PHP session
        sessionStorage.setItem('isAuthenticated', 'true');
        sessionStorage.setItem('userId', '<?php echo $_SESSION['user_id']; ?>');
        sessionStorage.setItem('userName', '<?php echo addslashes($user_name); ?>');
        sessionStorage.setItem('userType', '<?php echo $user_type; ?>');
        sessionStorage.setItem('userEmail', '<?php echo addslashes($user_email); ?>');
    </script>
    <script>
        // Help modal open/close handlers
        (function(){
            function $(id){ return document.getElementById(id); }
            var openBtn = $('openHelpBtn');
            var helpModal = $('helpModal');
            var helpClose = $('helpClose');
            var helpGotIt = $('helpGotIt');

            function centerAndShowModal(modal){
                if (!modal) return;
                modal.style.display = 'flex';
                // ensure content is absolutely positioned for dragging
                var content = modal.querySelector('.modal-content');
                if (content) {
                    content.style.position = 'absolute';
                    // center it
                    var left = Math.max((window.innerWidth - content.offsetWidth) / 2, 20);
                    var top = Math.max((window.innerHeight - content.offsetHeight) / 2, 20);
                    content.style.left = left + 'px';
                    content.style.top = top + 'px';
                }
            }

            function hideModal(modal){ if (!modal) return; modal.style.display = 'none'; }

            if (openBtn){ openBtn.addEventListener('click', function(){ centerAndShowModal(helpModal); }); }
            if (helpClose){ helpClose.addEventListener('click', function(){ hideModal(helpModal); }); }
            if (helpGotIt){ helpGotIt.addEventListener('click', function(){ hideModal(helpModal); }); }

            // close on overlay click
            if (helpModal){ helpModal.addEventListener('click', function(e){ if (e.target === helpModal) hideModal(helpModal); }); }

            // Make modal draggable by its header
            function makeModalDraggable(modal){
                if (!modal) return;
                var content = modal.querySelector('.modal-content');
                if (!content) return;
                var header = content.querySelector('.modal-header');
                if (!header) header = content;

                var isDragging = false;
                var startX = 0, startY = 0, origLeft = 0, origTop = 0;

                header.style.cursor = 'move';

                header.addEventListener('mousedown', function(e){
                    isDragging = true;
                    startX = e.clientX;
                    startY = e.clientY;
                    origLeft = parseInt(content.style.left || 0, 10);
                    origTop = parseInt(content.style.top || 0, 10);
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                    e.preventDefault();
                });

                function onMouseMove(e){
                    if (!isDragging) return;
                    var dx = e.clientX - startX;
                    var dy = e.clientY - startY;
                    content.style.left = Math.max(origLeft + dx, 10) + 'px';
                    content.style.top = Math.max(origTop + dy, 10) + 'px';
                }

                function onMouseUp(){
                    isDragging = false;
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                }

                // Touch support
                header.addEventListener('touchstart', function(e){
                    isDragging = true;
                    var t = e.touches[0];
                    startX = t.clientX;
                    startY = t.clientY;
                    origLeft = parseInt(content.style.left || 0, 10);
                    origTop = parseInt(content.style.top || 0, 10);
                    document.addEventListener('touchmove', onTouchMove);
                    document.addEventListener('touchend', onTouchEnd);
                    e.preventDefault();
                });

                function onTouchMove(e){
                    if (!isDragging) return;
                    var t = e.touches[0];
                    var dx = t.clientX - startX;
                    var dy = t.clientY - startY;
                    content.style.left = Math.max(origLeft + dx, 10) + 'px';
                    content.style.top = Math.max(origTop + dy, 10) + 'px';
                }

                function onTouchEnd(){
                    isDragging = false;
                    document.removeEventListener('touchmove', onTouchMove);
                    document.removeEventListener('touchend', onTouchEnd);
                }
            }

            // Initialize draggable for help and account modals
            makeModalDraggable(helpModal);
            var accountModal = document.getElementById('accountModal');
            makeModalDraggable(accountModal);
        })();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
        // Embed PHP user data for JavaScript access
        window.userData = {
            name: <?php echo json_encode($user_name); ?>,
            email: <?php echo json_encode($user_email); ?>,
            phone: <?php echo json_encode($user_phone); ?>,
            regno: <?php echo json_encode($user_regno); ?>
        };
    </script>
    <script src="script.js"></script>
</body>

</html>


