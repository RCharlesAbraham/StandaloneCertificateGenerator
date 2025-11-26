console.log('🚀 Certificate Generator Script Loading...');

// Get DOM elements (with null checks for login page)
const canvas = document.getElementById('certificateCanvas');
const ctx = canvas ? canvas.getContext('2d') : null;
const generateBtn = document.getElementById('generateBtn');

console.log('📊 DOM Elements:', {
    canvas: !!canvas,
    ctx: !!ctx,
    generateBtn: !!generateBtn
});

// Form inputs
const certNoInput = document.getElementById('certNo');
const nameInput = document.getElementById('name');
const certifiedForInput = document.getElementById('certifiedFor');
const fromDateInput = document.getElementById('fromDate');
const toDateInput = document.getElementById('toDate');
const sig1Input = document.getElementById('sig1');
const sig2Input = document.getElementById('sig2');
const sig3Input = document.getElementById('sig3');
const excelFileInput = document.getElementById('excelFile');

// Store uploaded signature images - SIMPLIFIED: 3 positions (left/center/right)
let signatures = {
    sigLeft: null,      // From Signature 1 upload -> Left position
    sigCenter: null,    // From Signature 2 upload -> Center position
    sigRight: null      // From Signature 3 upload -> Right position
};

// Template image
let templateImage = null;

// Store Excel data for batch processing
let excelData = [];
let currentRowIndex = 0;
let bulkGenerationCancelled = false;

// Text placeholders with draggable positions and sizes (will be loaded from localStorage or config.json)
let textPlaceholders = {};

// Load configuration from localStorage first, then fallback to config.json
const loadConfig = async () => {
    // Try to load from localStorage first
    const savedConfig = localStorage.getItem('certificateConfig');
    if (savedConfig) {
        try {
            const config = JSON.parse(savedConfig);
            textPlaceholders = config.textPlaceholders || textPlaceholders;
            // Load grouping setting if present
            if (typeof config.groupingEnabled !== 'undefined') {
                groupingEnabled = !!config.groupingEnabled;
            }
            console.log('âœ… Configuration loaded from localStorage');
            console.log('ðŸ” DEBUG: Sample placeholder positions:', {
                sig1GroupName: textPlaceholders.sig1GroupName,
                sig1GroupDesignation: textPlaceholders.sig1GroupDesignation
            });
            // Don't render yet - wait for template to load
            // Update grouping toggle UI if present
            const groupingToggleEl = document.getElementById('groupingToggle');
            if (groupingToggleEl) groupingToggleEl.checked = groupingEnabled;
            return;
        } catch (error) {
            console.error('âŒ Error parsing localStorage config:', error);
        }
    }

    // Fallback to config.json if no localStorage data
    try {
        const response = await fetch('config.json');
        const config = await response.json();
        textPlaceholders = config.textPlaceholders || textPlaceholders;
        if (typeof config.groupingEnabled !== 'undefined') {
            groupingEnabled = !!config.groupingEnabled;
        }
        console.log('âœ… Configuration loaded from config.json');
        // Save to localStorage for future use
        saveConfig();
        // Don't render yet - wait for template to load
    } catch (error) {
        console.error('âŒ Error loading config.json, using default values:', error);
        // Fallback to default values if config.json is not available
        textPlaceholders = {
            certNo: { x: 0.849, y: 0.121, fontSize: 25, label: 'Certificate No', varName: '{{VAR2}}', dragging: false },
            name: { x: 0.663, y: 0.538, fontSize: 60, label: 'Name', varName: '{{VAR1}}', dragging: false },
            certifiedFor: { x: 0.417, y: 0.622, fontSize: 30, label: 'Certified For', varName: '{{VAR3}}', dragging: false },
            fromDate: { x: 0.734, y: 0.658, fontSize: 28, label: 'From Date', varName: '{{VAR7}}', dragging: false },
            toDate: { x: 0.891, y: 0.658, fontSize: 28, label: 'To Date', varName: '{{VAR8}}', dragging: false },
            sig1: { x: 0.430, y: 0.800, width: 170, height: 74, label: 'Signature 1', varName: '{{VAR4}}', dragging: false },
            sig1Name: { x: 0.430, y: 0.880, fontSize: 14, label: 'Sig1 Name', varName: '{{SIG1_NAME}}', dragging: false },
            sig1Title: { x: 0.430, y: 0.900, fontSize: 12, label: 'Sig1 Title', varName: '{{SIG1_TITLE}}', dragging: false },
            sig1Org: { x: 0.430, y: 0.920, fontSize: 12, label: 'Sig1 Org', varName: '{{SIG1_ORG}}', dragging: false },
            sig2: { x: 0.639, y: 0.804, width: 170, height: 74, label: 'Signature 2', varName: '{{VAR5}}', dragging: false },
            sig2Name: { x: 0.639, y: 0.880, fontSize: 14, label: 'Sig2 Name', varName: '{{SIG2_NAME}}', dragging: false },
            sig2Title: { x: 0.639, y: 0.900, fontSize: 12, label: 'Sig2 Title', varName: '{{SIG2_TITLE}}', dragging: false },
            sig2Org: { x: 0.639, y: 0.920, fontSize: 12, label: 'Sig2 Org', varName: '{{SIG2_ORG}}', dragging: false },
            sig3: { x: 0.848, y: 0.803, width: 170, height: 74, label: 'Signature 3', varName: '{{VAR6}}', dragging: false },
            sig3Name: { x: 0.848, y: 0.880, fontSize: 14, label: 'Sig3 Name', varName: '{{SIG3_NAME}}', dragging: false },
            sig3Title: { x: 0.848, y: 0.900, fontSize: 12, label: 'Sig3 Title', varName: '{{SIG3_TITLE}}', dragging: false },
            sig3Org: { x: 0.848, y: 0.920, fontSize: 12, label: 'Sig3 Org', varName: '{{SIG3_ORG}}', dragging: false }
        };
        console.log('📋 Using default textPlaceholders:', Object.keys(textPlaceholders).length, 'items');
        saveConfig();
        // Don't render yet - wait for template to load
    }
};

// Save configuration to localStorage automatically
const saveConfig = () => {
    const config = {
        textPlaceholders: textPlaceholders,
        groupingEnabled: !!groupingEnabled
    };

    localStorage.setItem('certificateConfig', JSON.stringify(config, null, 2));
    console.log('ðŸ’¾ Configuration auto-saved');

    // Show visual feedback
    showSaveIndicator();
};

// Show save indicator (brief visual feedback)
const showSaveIndicator = () => {
    // Create or reuse indicator
    let indicator = document.getElementById('saveIndicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'saveIndicator';
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            z-index: 10000;
            display: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        indicator.innerHTML = '? Saved!';
        document.body.appendChild(indicator);
    }

    // Show and hide with animation
    indicator.style.display = 'block';
    indicator.style.animation = 'fadeIn 0.3s';

    setTimeout(() => {
        indicator.style.animation = 'fadeOut 0.3s';
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 300);
    }, 1500);
};

let selectedPlaceholder = null;
let selectedPlaceholders = []; // For multi-select
let isDragging = false;
let isResizing = false;
let dragOffsetX = 0;
let dragOffsetY = 0;
let lineEndpointDragging = null; // 'start', 'end', 'both', or null

// Drag select variables
let isSelectingBox = false;
let selectBoxStart = { x: 0, y: 0 };
let selectBoxEnd = { x: 0, y: 0 };
let dragStarted = false; // Flag to track if drag has started (moved at least 5 pixels)

// Grouping option for Layout 1 (persisted)
let groupingEnabled = true;

// Zoom and pan variables
let zoomLevel = 0.5; // Default 50% actual zoom (shown as 100%)
let panX = 0;
let panY = 0;
let isPanning = false;
let lastPanX = 0;
let lastPanY = 0;

// Calibration mode
let calibrationMode = false;
let showGrid = false;

// Load template image
const loadTemplate = () => {
    if (!canvas) return;

    templateImage = new Image();
    templateImage.onload = async () => {
        // Set canvas size to match template
        canvas.width = templateImage.width;
        canvas.height = templateImage.height;

        console.log('ðŸ” DEBUG: Canvas dimensions set to:', canvas.width, 'x', canvas.height);
        console.log('ðŸ” DEBUG: Template dimensions:', templateImage.width, 'x', templateImage.height);

        // Apply zoom transform immediately
        canvas.style.transform = `scale(${zoomLevel})`;

        // Force a reflow to ensure canvas dimensions and transform are applied
        void canvas.offsetHeight;

        // Wait for fonts to be ready before rendering
        try {
            await document.fonts.ready;
            console.log('âœ… Fonts loaded');
        } catch (e) {
            console.log('âš ï¸ Font loading check failed, continuing anyway');
        }

        // Additional reflow after fonts load
        void canvas.offsetHeight;

        // Double render strategy: render twice to ensure everything is settled
        requestAnimationFrame(() => {
            console.log('ðŸ” DEBUG: First render pass');
            renderCertificate();

            // Second render after a delay to ensure all CSS is applied
            setTimeout(() => {
                console.log('ðŸ” DEBUG: Second render pass, canvas size:', canvas.width, 'x', canvas.height);
                console.log('ðŸ” DEBUG: Canvas getBoundingClientRect:', canvas.getBoundingClientRect());
                console.log('ðŸ” DEBUG: Zoom level:', zoomLevel);
                renderCertificate();
            }, 100);
        });
    };
    templateImage.onerror = async () => {
        // If template not found, create a blank certificate
        canvas.width = 1122;
        canvas.height = 794;

        // Force a reflow
        void canvas.offsetHeight;

        // Wait for fonts
        try {
            await document.fonts.ready;
        } catch (e) {
            console.log('âš ï¸ Font loading check failed');
        }

        requestAnimationFrame(() => {
            renderCertificate();
        });
    };
    templateImage.src = 'assets/MainPlaceholderCertificate.jpg';
};

// Render certificate on canvas
// Helper function to apply text styling from placeholder properties
const applyTextStyle = (placeholder, defaultFont = 'Arial') => {
    const fontFamily = placeholder.fontFamily || defaultFont;
    const fontSize = placeholder.fontSize || 20;
    const bold = placeholder.bold ? 'bold ' : '';
    const italic = placeholder.italic ? 'italic ' : '';

    ctx.font = `${italic}${bold}${fontSize}px ${fontFamily}`;
    ctx.fillStyle = placeholder.color || '#000000';

    // Letter spacing is applied per character (not supported natively in canvas)
    if (placeholder.letterSpacing) {
        ctx.letterSpacing = `${placeholder.letterSpacing}px`;
    } else {
        ctx.letterSpacing = '0px';
    }
};

const renderCertificate = () => {
    if (!canvas || !ctx) return;

    // Check if textPlaceholders is loaded
    if (!textPlaceholders || !textPlaceholders.certNo) {
        console.log('⚠️ textPlaceholders not loaded yet, skipping render');
        return;
    }

    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw template image or background
    if (templateImage && templateImage.complete) {
        ctx.drawImage(templateImage, 0, 0);
    } else {
        // Draw white background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // Draw grid if enabled
    if (showGrid) {
        ctx.strokeStyle = 'rgba(255, 0, 0, 0.3)';
        ctx.lineWidth = 1;

        // Vertical lines every 10%
        for (let i = 0; i <= 10; i++) {
            const x = (canvas.width / 10) * i;
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();

            // Draw percentage label
            ctx.fillStyle = 'rgba(255, 0, 0, 0.7)';
            ctx.font = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(`${i * 10}%`, x, 15);
        }

        // Horizontal lines every 10%
        for (let i = 0; i <= 10; i++) {
            const y = (canvas.height / 10) * i;
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();

            // Draw percentage label
            ctx.fillStyle = 'rgba(255, 0, 0, 0.7)';
            ctx.font = '10px Arial';
            ctx.textAlign = 'left';
            ctx.fillText(`${i * 10}%`, 5, y - 5);
        }
    }

    // Set text properties
    ctx.fillStyle = '#000000';
    ctx.textAlign = 'center';

    // Draw Certificate Number
    applyTextStyle(textPlaceholders.certNo, 'Arial');
    const certNo = certNoInput.value || textPlaceholders.certNo.varName;
    const certNoX = canvas.width * textPlaceholders.certNo.x;
    const certNoY = canvas.height * textPlaceholders.certNo.y;
    ctx.fillText(certNo, certNoX, certNoY);

    // Draw Name
    applyTextStyle(textPlaceholders.name, 'Georgia, serif');
    const name = nameInput.value || textPlaceholders.name.varName;
    const nameX = canvas.width * textPlaceholders.name.x;
    const nameY = canvas.height * textPlaceholders.name.y;
    ctx.fillText(name, nameX, nameY);

    // Draw Certified For
    applyTextStyle(textPlaceholders.certifiedFor, 'Arial');
    const certifiedFor = certifiedForInput.value || textPlaceholders.certifiedFor.varName;
    const certForX = canvas.width * textPlaceholders.certifiedFor.x;
    const certForY = canvas.height * textPlaceholders.certifiedFor.y;
    ctx.fillText(certifiedFor, certForX, certForY);

    // Draw From Date
    applyTextStyle(textPlaceholders.fromDate, 'Arial');
    const fromDate = fromDateInput.value ? formatDate(fromDateInput.value) : textPlaceholders.fromDate.varName;
    const fromDateX = canvas.width * textPlaceholders.fromDate.x;
    const fromDateY = canvas.height * textPlaceholders.fromDate.y;
    ctx.fillText(fromDate, fromDateX, fromDateY);

    // Draw To Date
    applyTextStyle(textPlaceholders.toDate, 'Arial');
    const toDate = toDateInput.value ? formatDate(toDateInput.value) : textPlaceholders.toDate.varName;
    const toDateX = canvas.width * textPlaceholders.toDate.x;
    const toDateY = canvas.height * textPlaceholders.toDate.y;
    ctx.fillText(toDate, toDateX, toDateY);

    // Draw custom text fields
    customTextFields.forEach(fieldId => {
        if (textPlaceholders[fieldId]) {
            const placeholder = textPlaceholders[fieldId];
            applyTextStyle(placeholder, 'Arial');
            const text = getPlaceholderText(fieldId);
            const x = canvas.width * placeholder.x;
            const y = canvas.height * placeholder.y;
            ctx.fillText(text, x, y);
        }
    });

    // Draw signature groups based on slider layout - each position independently movable
    // Debug: show which signature image objects are currently set
    console.debug('Rendering signatures:', {
        sigLeft: !!signatures.sigLeft,
        sigCenter: !!signatures.sigCenter,
        sigRight: !!signatures.sigRight
    });
    // Initialize signature group placeholders for each layout position
    const initSignatureGroups = () => {
        // Layout 1 - Single signature (centered)
        if (!textPlaceholders.sig1GroupName) {
            textPlaceholders.sig1GroupName = { x: 0.5, y: 0.85, fontSize: 27, label: 'Sig1 Name', varName: '', dragging: false };
            textPlaceholders.sig1GroupDesignation = { x: 0.5, y: 0.89, fontSize: 22, label: 'Sig1 Designation', varName: '', dragging: false };
            textPlaceholders.sig1GroupCollege = { x: 0.5, y: 0.93, fontSize: 22, label: 'Sig1 College', varName: '', dragging: false };
        }

        // Layout 1 - Signature image placeholder (movable, scale-only)
        if (!textPlaceholders.sig1Image) {
            textPlaceholders.sig1Image = { x: 0.5, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig1 Image', dragging: false };
        }

        // Layout 2 - Double signature (left at 25%, right at 75%)
        if (!textPlaceholders.sig2GroupName) {
            textPlaceholders.sig2GroupName = { x: 0.25, y: 0.85, fontSize: 27, label: 'Sig2 Name', varName: '', dragging: false };
            textPlaceholders.sig2GroupDesignation = { x: 0.25, y: 0.89, fontSize: 22, label: 'Sig2 Designation', varName: '', dragging: false };
            textPlaceholders.sig2GroupCollege = { x: 0.25, y: 0.93, fontSize: 22, label: 'Sig2 College', varName: '', dragging: false };
        }

        // Layout 2 - Left signature image placeholder
        if (!textPlaceholders.sig2LeftImage) {
            textPlaceholders.sig2LeftImage = { x: 0.25, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig2 Left Image', dragging: false };
        }

        // Layout 2 - Double signature second position (right at 75%)
        if (!textPlaceholders.sig2bGroupName) {
            textPlaceholders.sig2bGroupName = { x: 0.75, y: 0.85, fontSize: 27, label: 'Sig2b Name', varName: '', dragging: false };
            textPlaceholders.sig2bGroupDesignation = { x: 0.75, y: 0.89, fontSize: 22, label: 'Sig2b Designation', varName: '', dragging: false };
            textPlaceholders.sig2bGroupCollege = { x: 0.75, y: 0.93, fontSize: 22, label: 'Sig2b College', varName: '', dragging: false };
        }

        // Layout 2 - Right signature image placeholder
        if (!textPlaceholders.sig2RightImage) {
            textPlaceholders.sig2RightImage = { x: 0.75, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig2 Right Image', dragging: false };
        }

        // Layout 3 - Triple signature (left at 17%, center at 50%, right at 83%)
        if (!textPlaceholders.sig3GroupName) {
            textPlaceholders.sig3GroupName = { x: 0.17, y: 0.85, fontSize: 27, label: 'Sig3 Name', varName: '', dragging: false };
            textPlaceholders.sig3GroupDesignation = { x: 0.17, y: 0.89, fontSize: 22, label: 'Sig3 Designation', varName: '', dragging: false };
            textPlaceholders.sig3GroupCollege = { x: 0.17, y: 0.93, fontSize: 22, label: 'Sig3 College', varName: '', dragging: false };
        }

        // Layout 3 - Left signature image placeholder
        if (!textPlaceholders.sig3LeftImage) {
            textPlaceholders.sig3LeftImage = { x: 0.17, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig3 Left Image', dragging: false };
        }

        // Layout 3 - Triple signature middle position
        if (!textPlaceholders.sig3bGroupName) {
            textPlaceholders.sig3bGroupName = { x: 0.5, y: 0.85, fontSize: 27, label: 'Sig3b Name', varName: '', dragging: false };
            textPlaceholders.sig3bGroupDesignation = { x: 0.5, y: 0.89, fontSize: 22, label: 'Sig3b Designation', varName: '', dragging: false };
            textPlaceholders.sig3bGroupCollege = { x: 0.5, y: 0.93, fontSize: 22, label: 'Sig3b College', varName: '', dragging: false };
        }

        // Layout 3 - Center signature image placeholder
        if (!textPlaceholders.sig3CenterImage) {
            textPlaceholders.sig3CenterImage = { x: 0.5, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig3 Center Image', dragging: false };
        }

        // Layout 3 - Triple signature right position
        if (!textPlaceholders.sig3cGroupName) {
            textPlaceholders.sig3cGroupName = { x: 0.83, y: 0.85, fontSize: 27, label: 'Sig3c Name', varName: '', dragging: false };
            textPlaceholders.sig3cGroupDesignation = { x: 0.83, y: 0.89, fontSize: 22, label: 'Sig3c Designation', varName: '', dragging: false };
            textPlaceholders.sig3cGroupCollege = { x: 0.83, y: 0.93, fontSize: 22, label: 'Sig3c College', varName: '', dragging: false };
        }

        // Layout 3 - Right signature image placeholder
        if (!textPlaceholders.sig3RightImage) {
            textPlaceholders.sig3RightImage = { x: 0.83, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig3 Right Image', dragging: false };
        }
    };

    // Don't auto-initialize signature groups - they will be created on demand
    // initSignatureGroups();

    // Helper function to draw a signature group
    const drawSignatureGroup = (nameKey, desigKey, collegeKey) => {
        const namePlaceholder = textPlaceholders[nameKey];
        const desigPlaceholder = textPlaceholders[desigKey];
        const collegePlaceholder = textPlaceholders[collegeKey];

        if (!namePlaceholder || !desigPlaceholder || !collegePlaceholder) return;

        // Font sizes are stored in `textPlaceholders` and are now permanent (no UI size boxes).

        // Set text alignment to center
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Name
        applyTextStyle(namePlaceholder, 'Georgia, serif');
        const nameText = getPlaceholderText(nameKey);
        ctx.fillText(nameText, canvas.width * namePlaceholder.x, canvas.height * namePlaceholder.y);

        // Designation
        applyTextStyle(desigPlaceholder, 'Georgia, serif');
        const desigText = getPlaceholderText(desigKey);
        ctx.fillText(desigText, canvas.width * desigPlaceholder.x, canvas.height * desigPlaceholder.y);

        // College
        applyTextStyle(collegePlaceholder, 'Georgia, serif');
        const collegeText = getPlaceholderText(collegeKey);
        ctx.fillText(collegeText, canvas.width * collegePlaceholder.x, canvas.height * collegePlaceholder.y);
    };

    // Helper function to draw signature image using placeholder position and scale
    const drawSignatureImage = (sigImage, imagePlaceholder) => {
        if (sigImage && imagePlaceholder) {
            const x = canvas.width * imagePlaceholder.x;
            const y = canvas.height * imagePlaceholder.y;
            // Base size for signature images (aspect ratio preserved)
            const baseWidth = 120;
            const baseHeight = 60;
            // Apply scale from placeholder
            const imgWidth = baseWidth * imagePlaceholder.scale;
            const imgHeight = baseHeight * imagePlaceholder.scale;
            ctx.drawImage(sigImage, x - imgWidth / 2, y - imgHeight / 2, imgWidth, imgHeight);
        }
    };

    // SIMPLIFIED: Draw all 3 signature positions if images exist (no complex layout switching)
    // Debug: show which signature image objects are currently set
    console.debug('Rendering signatures:', {
        sigLeft: !!signatures.sigLeft,
        sigCenter: !!signatures.sigCenter,
        sigRight: !!signatures.sigRight
    });

    // Left signature (from Signature 1 upload)
    if (signatures.sigLeft && textPlaceholders.sigLeftImage) {
        drawSignatureImage(signatures.sigLeft, textPlaceholders.sigLeftImage);
    }
    // Center signature (from Signature 2 upload)
    if (signatures.sigCenter && textPlaceholders.sigCenterImage) {
        drawSignatureImage(signatures.sigCenter, textPlaceholders.sigCenterImage);
    }
    // Right signature (from Signature 3 upload)
    if (signatures.sigRight && textPlaceholders.sigRightImage) {
        drawSignatureImage(signatures.sigRight, textPlaceholders.sigRightImage);
    }

    // Additional signatures if provided (4 and 5)
    if (signatures.sig4 && textPlaceholders.sig4Image) {
        drawSignatureImage(signatures.sig4, textPlaceholders.sig4Image);
    }
    if (signatures.sig5 && textPlaceholders.sig5Image) {
        drawSignatureImage(signatures.sig5, textPlaceholders.sig5Image);
    }

    // Draw signature details (names, titles, orgs)
    const drawSignatureDetails = (nameKey, titleKey, orgKey) => {
        if (textPlaceholders[nameKey]) {
            const namePlaceholder = textPlaceholders[nameKey];
            ctx.font = `bold ${namePlaceholder.fontSize}px Arial`;
            const nameText = getPlaceholderText(nameKey);
            if (nameText && nameText !== namePlaceholder.varName) {
                const nameX = canvas.width * namePlaceholder.x;
                const nameY = canvas.height * namePlaceholder.y;
                ctx.fillText(nameText, nameX, nameY);
            }
        }

        if (textPlaceholders[titleKey]) {
            const titlePlaceholder = textPlaceholders[titleKey];
            ctx.font = `${titlePlaceholder.fontSize}px Arial`;
            const titleText = getPlaceholderText(titleKey);
            if (titleText && titleText !== titlePlaceholder.varName) {
                const titleX = canvas.width * titlePlaceholder.x;
                const titleY = canvas.height * titlePlaceholder.y;
                ctx.fillText(titleText, titleX, titleY);
            }
        }

        if (textPlaceholders[orgKey]) {
            const orgPlaceholder = textPlaceholders[orgKey];
            ctx.font = `${orgPlaceholder.fontSize}px Arial`;
            const orgText = getPlaceholderText(orgKey);
            if (orgText && orgText !== orgPlaceholder.varName) {
                const orgX = canvas.width * orgPlaceholder.x;
                const orgY = canvas.height * orgPlaceholder.y;
                ctx.fillText(orgText, orgX, orgY);
            }
        }
    };

    // Old signature drawing code removed - now using layout-specific signature images above

    // Draw lines
    Object.keys(textPlaceholders).forEach(key => {
        const placeholder = textPlaceholders[key];
        if (placeholder.type === 'line') {
            ctx.strokeStyle = placeholder.color || '#000000';
            ctx.lineWidth = placeholder.thickness || 2;
            ctx.lineCap = placeholder.lineCap || 'round'; // Apply rounded caps
            ctx.setLineDash([]);

            const x1 = canvas.width * placeholder.x1;
            const y1 = canvas.height * placeholder.y1;
            const x2 = canvas.width * placeholder.x2;
            const y2 = canvas.height * placeholder.y2;

            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.stroke();
        }
    });

    // Draw bounding boxes for selected placeholder
    if (selectedPlaceholder) {
        ctx.strokeStyle = '#67150a';
        ctx.lineWidth = 2;
        ctx.setLineDash([5, 5]);

        const placeholder = textPlaceholders[selectedPlaceholder];

        if (placeholder.type === 'line') {
            // Line - draw endpoint handles
            const x1 = canvas.width * placeholder.x1;
            const y1 = canvas.height * placeholder.y1;
            const x2 = canvas.width * placeholder.x2;
            const y2 = canvas.height * placeholder.y2;

            // Draw selection outline around the line
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.stroke();

            // Draw endpoint handles
            drawResizeHandle(x1, y1); // Start point
            drawResizeHandle(x2, y2); // End point
        } else if (placeholder.type === 'signatureImage') {
            // Signature image - draw box around image with resize handle
            const x = canvas.width * placeholder.x;
            const y = canvas.height * placeholder.y;
            const baseWidth = 120;
            const baseHeight = 60;
            const imgWidth = baseWidth * placeholder.scale;
            const imgHeight = baseHeight * placeholder.scale;

            ctx.strokeRect(x - imgWidth / 2, y - imgHeight / 2, imgWidth, imgHeight);

            // Draw resize handle at bottom-right corner
            drawResizeHandle(x + imgWidth / 2, y + imgHeight / 2);
        } else {
            const x = canvas.width * placeholder.x;
            const y = canvas.height * placeholder.y;

            if (placeholder.fontSize) {
                // Text placeholder - draw box around text
                const text = getPlaceholderText(selectedPlaceholder);
                ctx.font = `${placeholder.fontSize}px Arial`;
                const metrics = ctx.measureText(text);
                const width = metrics.width + 20;
                const height = placeholder.fontSize + 10;

                ctx.strokeRect(x - width / 2, y - height + 5, width, height);

                // Draw corner resize handles for text
                drawResizeHandle(x - width / 2, y - height + 5); // Top-left
                drawResizeHandle(x + width / 2, y - height + 5); // Top-right
                drawResizeHandle(x - width / 2, y + 5); // Bottom-left
                drawResizeHandle(x + width / 2, y + 5); // Bottom-right
            } else {
                // Image placeholder - draw box
                ctx.strokeRect(x - placeholder.width / 2, y - placeholder.height, placeholder.width, placeholder.height);

                // Draw corner resize handles for images
                drawResizeHandle(x - placeholder.width / 2, y - placeholder.height); // Top-left
                drawResizeHandle(x + placeholder.width / 2, y - placeholder.height); // Top-right
                drawResizeHandle(x - placeholder.width / 2, y); // Bottom-left
                drawResizeHandle(x + placeholder.width / 2, y); // Bottom-right
            }
        }

        ctx.setLineDash([]);
    }

    // Draw multi-selected elements
    if (selectedPlaceholders.length > 0) {
        ctx.strokeStyle = '#00a8ff';
        ctx.lineWidth = 2;
        ctx.setLineDash([3, 3]);

        selectedPlaceholders.forEach(key => {
            const placeholder = textPlaceholders[key];
            if (!placeholder) return;

            const x = canvas.width * placeholder.x;
            const y = canvas.height * placeholder.y;

            if (placeholder.type === 'line') {
                const x1 = canvas.width * placeholder.x1;
                const y1 = canvas.height * placeholder.y1;
                const x2 = canvas.width * placeholder.x2;
                const y2 = canvas.height * placeholder.y2;

                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.stroke();
            } else if (placeholder.fontSize) {
                const text = getPlaceholderText(key);
                ctx.font = `${placeholder.fontSize}px Arial`;
                const metrics = ctx.measureText(text);
                const width = metrics.width + 20;
                const height = placeholder.fontSize + 10;
                ctx.strokeRect(x - width / 2, y - height + 5, width, height);
            } else {
                ctx.strokeRect(x - placeholder.width / 2, y - placeholder.height, placeholder.width, placeholder.height);
            }
        });

        ctx.setLineDash([]);
    }

    // Draw selection box if active
    if (isSelectingBox) {
        const minX = Math.min(selectBoxStart.x, selectBoxEnd.x);
        const maxX = Math.max(selectBoxStart.x, selectBoxEnd.x);
        const minY = Math.min(selectBoxStart.y, selectBoxEnd.y);
        const maxY = Math.max(selectBoxStart.y, selectBoxEnd.y);

        ctx.fillStyle = 'rgba(0, 168, 255, 0.1)';
        ctx.fillRect(minX, minY, maxX - minX, maxY - minY);

        ctx.strokeStyle = '#00a8ff';
        ctx.lineWidth = 2;
        ctx.setLineDash([5, 5]);
        ctx.strokeRect(minX, minY, maxX - minX, maxY - minY);
        ctx.setLineDash([]);
    }
};

// Draw resize handle
const drawResizeHandle = (x, y) => {
    // No-op: resize handles/size boxes disabled per user preference
    return;
};

// Get placeholder text
const getPlaceholderText = (key) => {
    switch (key) {
        case 'certNo': return certNoInput?.value || textPlaceholders.certNo?.varName || '';
        case 'name': return nameInput?.value || textPlaceholders.name?.varName || '';
        case 'certifiedFor': return certifiedForInput?.value || textPlaceholders.certifiedFor?.varName || '';
        case 'fromDate': return fromDateInput?.value ? formatDate(fromDateInput.value) : textPlaceholders.fromDate?.varName || '';
        case 'toDate': return toDateInput?.value ? formatDate(toDateInput.value) : textPlaceholders.toDate?.varName || '';

        // Signature group texts - Layout 1 (Single)
        case 'sig1GroupName':
            return document.getElementById('sig1Name')?.value || textPlaceholders[key]?.varName || '';
        case 'sig1GroupDesignation':
            return document.getElementById('sig1Designation')?.value || textPlaceholders[key]?.varName || '';
        case 'sig1GroupCollege':
            return document.getElementById('sig1College')?.value || textPlaceholders[key]?.varName || '';

        // Layout 2 (Double) - Left
        case 'sig2GroupName':
            return document.getElementById('sig2LeftName')?.value || textPlaceholders[key]?.varName || '';
        case 'sig2GroupDesignation':
            return document.getElementById('sig2LeftDesignation')?.value || textPlaceholders[key]?.varName || '';
        case 'sig2GroupCollege':
            return document.getElementById('sig2LeftCollege')?.value || textPlaceholders[key]?.varName || '';

        // Layout 2 (Double) - Right
        case 'sig2bGroupName':
            return document.getElementById('sig2RightName')?.value || textPlaceholders[key]?.varName || '';
        case 'sig2bGroupDesignation':
            return document.getElementById('sig2RightDesignation')?.value || textPlaceholders[key]?.varName || '';
        case 'sig2bGroupCollege':
            return document.getElementById('sig2RightCollege')?.value || textPlaceholders[key]?.varName || '';

        // Layout 3 (Triple) - Left
        case 'sig3GroupName':
            return document.getElementById('sig3LeftName')?.value || textPlaceholders[key]?.varName || '';
        case 'sig3GroupDesignation':
            return document.getElementById('sig3LeftDesignation')?.value || textPlaceholders[key]?.varName || '';
        case 'sig3GroupCollege':
            return document.getElementById('sig3LeftCollege')?.value || textPlaceholders[key]?.varName || '';

        // Layout 3 (Triple) - Center
        case 'sig3bGroupName':
            return document.getElementById('sig3CenterName')?.value || textPlaceholders[key]?.varName || '';
        case 'sig3bGroupDesignation':
            return document.getElementById('sig3CenterDesignation')?.value || textPlaceholders[key]?.varName || '';
        case 'sig3bGroupCollege':
            return document.getElementById('sig3CenterCollege')?.value || textPlaceholders[key]?.varName || '';

        // Layout 3 (Triple) - Right
        case 'sig3cGroupName':
            return document.getElementById('sig3RightName')?.value || textPlaceholders[key]?.varName || '';
        case 'sig3cGroupDesignation':
            return document.getElementById('sig3RightDesignation')?.value || textPlaceholders[key]?.varName || '';
        case 'sig3cGroupCollege':
            return document.getElementById('sig3RightCollege')?.value || textPlaceholders[key]?.varName || '';

        // Old signature placeholders (no longer actively used, return empty or varName)
        case 'sig1Name':
        case 'sig1Title':
        case 'sig1Org':
        case 'sig2Name':
        case 'sig2Title':
        case 'sig2Org':
        case 'sig3Name':
        case 'sig3Title':
        case 'sig3Org':
            return textPlaceholders[key]?.varName || '';
        default: {
            // Handle custom text fields
            if (key.startsWith('customText')) {
                const input = document.getElementById(key);
                return input ? (input.value || textPlaceholders[key]?.varName || '') : '';
            }
            return '';
        }
    }
};// Format date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const month = date.toLocaleDateString('en-US', { month: 'short' });
    const day = date.getDate();
    const year = date.getFullYear();
    return `${month} ${day}, ${year}`;
};

// Handle signature uploads
const handleSignatureUpload = (inputElement, signatureKey, nameSpan) => {
    inputElement.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures[signatureKey] = img;
                    renderCertificate();
                    nameSpan.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
};

// Handle Excel upload
if (excelFileInput) {
    excelFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            console.log('ðŸ“ Excel file selected:', file.name);
            document.getElementById('excel-name').textContent = file.name;
            const reader = new FileReader();
            reader.onload = (event) => {
                try {
                    console.log('ðŸ“Š Reading Excel data...');
                    const data = new Uint8Array(event.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
                    console.log('âœ… Excel parsed, rows:', jsonData.length);

                    // Store all data rows (skip header)
                    excelData = jsonData.slice(1).filter(row => row.length > 0 && row[0]);
                    console.log('ðŸ’¾ Stored data rows:', excelData.length);

                    if (excelData.length > 0) {
                        // Load first row into form
                        const values = excelData[0];
                        currentRowIndex = 0;

                        // Map Excel columns to form fields
                        // Expected columns: Certificate No, Name, Certified For, From Date, To Date
                        if (values[0]) certNoInput.value = values[0];
                        if (values[1]) nameInput.value = values[1];
                        if (values[2]) certifiedForInput.value = values[2];
                        if (values[3]) fromDateInput.value = formatDateForInput(values[3]);
                        if (values[4]) toDateInput.value = formatDateForInput(values[4]);

                        renderCertificate();

                        // Show the "Generate All PDFs" button
                        document.getElementById('generateAllBtn').style.display = 'flex';
                        console.log('âœ… Generate All button shown');

                        showModal(`Excel data loaded successfully!\nFound ${excelData.length} certificate(s) to generate.`, 'Data Loaded', 'success');
                    } else {
                        showModal('No data found in Excel file.', 'No Data', 'warning');
                    }
                } catch (error) {
                    console.error('âŒ Error processing Excel data:', error);
                    showModal('Error processing Excel data: ' + error.message, 'Error', 'error');
                }
            };
            reader.readAsArrayBuffer(file);
        }
    });
}

// Format date for input field (YYYY-MM-DD)
const formatDateForInput = (excelDate) => {
    // Excel dates can be in various formats
    if (typeof excelDate === 'number') {
        // Excel serial date number
        const date = new Date((excelDate - 25569) * 86400 * 1000);
        return date.toISOString().split('T')[0];
    } else if (typeof excelDate === 'string') {
        // Try to parse string date
        const date = new Date(excelDate);
        if (!isNaN(date.getTime())) {
            return date.toISOString().split('T')[0];
        }
    }
    return '';
};

// Function to generate a single PDF
const generateSinglePDF = (certNo, name, certifiedFor, fromDate, toDate) => {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({
        orientation: 'landscape',
        unit: 'px',
        format: [canvas.width, canvas.height]
    });

    // Convert canvas to image and add to PDF
    const imgData = canvas.toDataURL('image/png');
    pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);

    // Generate filename
    const filename = `Certificate_${certNo || 'Draft'}.pdf`;
    return { pdf, filename };
};

// Generate single PDF (current form data)
if (generateBtn) {
    generateBtn.addEventListener('click', async () => {
        const certNo = certNoInput.value;
        const name = nameInput.value;
        const certifiedFor = certifiedForInput.value;
        const fromDate = fromDateInput.value;
        const toDate = toDateInput.value;

        const { pdf, filename } = generateSinglePDF(certNo, name, certifiedFor, fromDate, toDate);

        pdf.save(filename);

        // Log to database
        try {
            await fetch('log_certificate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    certificate_no: certNo,
                    recipient_name: name,
                    certified_for: certifiedFor,
                    from_date: fromDate,
                    to_date: toDate,
                    generation_type: 'single',
                    bulk_count: 1,
                    template_used: templateImage ? 'custom' : 'default'
                })
            });
        } catch (error) {
            console.error('Failed to log certificate:', error);
        }

        showModal('PDF generated successfully!', 'Success', 'success');
    });
}

// Template upload handler
const templateUploadBtn = document.getElementById('templateUpload');
if (templateUploadBtn) {
    templateUploadBtn.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    templateImage = img;
                    canvas.width = img.width;
                    canvas.height = img.height;
                    renderCertificate();
                    showModal(`Template changed successfully!\nSize: ${img.width}x${img.height}px`, 'Template Updated', 'success');
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Generate all PDFs from Excel data (individual PDFs in a ZIP)
const generateAllBtn = document.getElementById('generateAllBtn');
if (generateAllBtn) {
    generateAllBtn.addEventListener('click', async () => {
        console.log('ðŸŽ¯ Generate All clicked, data rows:', excelData.length);

        if (excelData.length === 0) {
            showModal('No data loaded. Please upload an Excel file first.', 'No Data', 'warning');
            return;
        }

        // Confirm before proceeding
        const confirmed = await showModal(`Generate ${excelData.length} individual certificates?\n\nThis will create separate PDF files compressed into a ZIP archive.`, 'Confirm Generation', 'info', true);
        if (!confirmed) {
            console.log('âŒ User cancelled generation');
            return;
        }

        console.log('âœ… Starting bulk generation...');

        // Reset cancel flag
        bulkGenerationCancelled = false;

        // Show progress modal
        const progressModal = showProgressModal(excelData.length);
        document.getElementById('generateAllBtn').disabled = true;

        // Track start time for ETA calculation
        const startTime = Date.now();

        // Create ZIP file
        const zip = new JSZip();
        const { jsPDF } = window.jspdf;

        try {
            for (let i = 0; i < excelData.length; i++) {
                // Check if cancelled
                if (bulkGenerationCancelled) {
                    console.log('âŒ Bulk generation cancelled by user');
                    hideProgressModal();
                    document.getElementById('generateAllBtn').disabled = false;
                    showModal('Bulk generation cancelled.', 'Cancelled', 'info');
                    return;
                }

                const row = excelData[i];
                console.log(`ðŸ“„ Processing certificate ${i + 1}/${excelData.length}:`, row[1]);

                // Update form fields with current row data
                const certNo = row[0] || '';
                const name = row[1] || '';
                const certifiedFor = row[2] || '';
                const fromDate = formatDateForInput(row[3]);
                const toDate = formatDateForInput(row[4]);

                certNoInput.value = certNo;
                nameInput.value = name;
                certifiedForInput.value = certifiedFor;
                fromDateInput.value = fromDate;
                toDateInput.value = toDate;

                // Update signature fields if they exist in Excel
                // Expected columns: 5-13 for signature details (3 signatures x 3 fields each)
                // Layout 1 (Single): columns 5, 6, 7
                if (document.getElementById('sig1Name')) document.getElementById('sig1Name').value = row[5] || '';
                if (document.getElementById('sig1Designation')) document.getElementById('sig1Designation').value = row[6] || '';
                if (document.getElementById('sig1College')) document.getElementById('sig1College').value = row[7] || '';

                // Layout 2 (Double): columns 5-10
                if (document.getElementById('sig2LeftName')) document.getElementById('sig2LeftName').value = row[5] || '';
                if (document.getElementById('sig2LeftDesignation')) document.getElementById('sig2LeftDesignation').value = row[6] || '';
                if (document.getElementById('sig2LeftCollege')) document.getElementById('sig2LeftCollege').value = row[7] || '';
                if (document.getElementById('sig2RightName')) document.getElementById('sig2RightName').value = row[8] || '';
                if (document.getElementById('sig2RightDesignation')) document.getElementById('sig2RightDesignation').value = row[9] || '';
                if (document.getElementById('sig2RightCollege')) document.getElementById('sig2RightCollege').value = row[10] || '';

                // Layout 3 (Triple): columns 5-13
                if (document.getElementById('sig3LeftName')) document.getElementById('sig3LeftName').value = row[5] || '';
                if (document.getElementById('sig3LeftDesignation')) document.getElementById('sig3LeftDesignation').value = row[6] || '';
                if (document.getElementById('sig3LeftCollege')) document.getElementById('sig3LeftCollege').value = row[7] || '';
                if (document.getElementById('sig3CenterName')) document.getElementById('sig3CenterName').value = row[8] || '';
                if (document.getElementById('sig3CenterDesignation')) document.getElementById('sig3CenterDesignation').value = row[9] || '';
                if (document.getElementById('sig3CenterCollege')) document.getElementById('sig3CenterCollege').value = row[10] || '';
                if (document.getElementById('sig3RightName')) document.getElementById('sig3RightName').value = row[11] || '';
                if (document.getElementById('sig3RightDesignation')) document.getElementById('sig3RightDesignation').value = row[12] || '';
                if (document.getElementById('sig3RightCollege')) document.getElementById('sig3RightCollege').value = row[13] || '';

                // Render the certificate with current data
                renderCertificate();

                // Wait a bit for canvas to update
                await new Promise(resolve => setTimeout(resolve, 100));

                // Create individual PDF for this certificate
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [canvas.width, canvas.height]
                });

                // Convert canvas to image and add to PDF
                const imgData = canvas.toDataURL('image/png');
                pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);

                // Get PDF as blob and add to ZIP
                const pdfBlob = pdf.output('blob');
                const sanitizedName = (name || `Certificate_${i + 1}`).replace(/[^a-z0-9]/gi, '_');
                const sanitizedCertNo = (certNo || `cert_${i + 1}`).replace(/[^a-z0-9]/gi, '_');
                zip.file(`${sanitizedCertNo}_${sanitizedName}.pdf`, pdfBlob);

                // Calculate ETA
                const elapsed = Date.now() - startTime;
                const avgTimePerCert = elapsed / (i + 1);
                const remaining = excelData.length - (i + 1);
                const etaMs = avgTimePerCert * remaining;
                const etaSeconds = Math.ceil(etaMs / 1000);

                // Update progress modal with ETA
                updateProgressModal(i + 1, excelData.length, null, etaSeconds);

                // Small delay to ensure smooth rendering
                await new Promise(resolve => setTimeout(resolve, 50));
            }

            // Check if cancelled before creating ZIP
            if (bulkGenerationCancelled) {
                console.log('âŒ Bulk generation cancelled before ZIP creation');
                hideProgressModal();
                document.getElementById('generateAllBtn').disabled = false;
                showModal('Bulk generation cancelled.', 'Cancelled', 'info');
                return;
            }

            // Generate ZIP file with progress callback
            console.log('ðŸ“¦ Creating ZIP archive...');

            const zipBlob = await zip.generateAsync({
                type: 'blob',
                compression: 'DEFLATE',
                compressionOptions: { level: 3 } // Reduced from 6 to 3 for faster compression
            }, (metadata) => {
                // Update progress during ZIP creation
                const zipProgress = Math.round(metadata.percent);
                updateProgressModal(excelData.length, excelData.length, `Creating ZIP archive... ${zipProgress}%`, null);
            });

            // Generate filename with timestamp
            const timestamp = new Date().toISOString().split('T')[0].replace(/-/g, '');
            const filename = `Certificates_Batch_${timestamp}_${excelData.length}files.zip`;

            // Download ZIP file
            const link = document.createElement('a');
            link.href = URL.createObjectURL(zipBlob);
            link.download = filename;
            link.click();
            URL.revokeObjectURL(link.href);

            // Log bulk generation to database
            try {
                await fetch('log_certificate.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        certificate_no: 'BULK',
                        recipient_name: `Batch of ${excelData.length}`,
                        certified_for: 'Multiple Recipients',
                        generation_type: 'bulk',
                        bulk_count: excelData.length,
                        template_used: templateImage ? 'custom' : 'default'
                    })
                });
            } catch (error) {
                console.error('Failed to log bulk generation:', error);
            }

            // Hide progress modal
            hideProgressModal();
            console.log('âœ… Bulk generation complete!');
            document.getElementById('generateAllBtn').disabled = false;
            showModal(`Successfully generated ${excelData.length} certificates!\n\nFile: ${filename}`, 'Batch Complete', 'success');

        } catch (error) {
            console.error('âŒ Error during bulk generation:', error);
            hideProgressModal();
            document.getElementById('generateAllBtn').disabled = false;
            showModal('Error generating certificates: ' + error.message, 'Generation Error', 'error');
        }
    });
}// Add event listeners for real-time updates (only on index page)
if (certNoInput) certNoInput.addEventListener('input', renderCertificate);
if (nameInput) nameInput.addEventListener('input', renderCertificate);
if (certifiedForInput) certifiedForInput.addEventListener('input', renderCertificate);
if (fromDateInput) fromDateInput.addEventListener('change', renderCertificate);
if (toDateInput) toDateInput.addEventListener('change', renderCertificate);

// Font size inputs with config save
const certNoFontSizeInput = document.getElementById('certNoFontSize');
const nameFontSizeInput = document.getElementById('nameFontSize');
const certifiedForFontSizeInput = document.getElementById('certifiedForFontSize');
const fromDateFontSizeInput = document.getElementById('fromDateFontSize');
const toDateFontSizeInput = document.getElementById('toDateFontSize');

if (certNoFontSizeInput) {
    certNoFontSizeInput.addEventListener('input', () => {
        const newSize = parseInt(certNoFontSizeInput.value) || 20;
        if (textPlaceholders.certNo) {
            textPlaceholders.certNo.fontSize = newSize;
            saveConfig();
            renderCertificate();
        }
    });
}

if (nameFontSizeInput) {
    nameFontSizeInput.addEventListener('input', () => {
        const newSize = parseInt(nameFontSizeInput.value) || 60;
        if (textPlaceholders.name) {
            textPlaceholders.name.fontSize = newSize;
            saveConfig();
            renderCertificate();
        }
    });
}

if (certifiedForFontSizeInput) {
    certifiedForFontSizeInput.addEventListener('input', () => {
        const newSize = parseInt(certifiedForFontSizeInput.value) || 30;
        if (textPlaceholders.certifiedFor) {
            textPlaceholders.certifiedFor.fontSize = newSize;
            saveConfig();
            renderCertificate();
        }
    });
}

if (fromDateFontSizeInput) {
    fromDateFontSizeInput.addEventListener('input', () => {
        const newSize = parseInt(fromDateFontSizeInput.value) || 28;
        if (textPlaceholders.fromDate) {
            textPlaceholders.fromDate.fontSize = newSize;
            saveConfig();
            renderCertificate();
        }
    });
}

if (toDateFontSizeInput) {
    toDateFontSizeInput.addEventListener('input', () => {
        const newSize = parseInt(toDateFontSizeInput.value) || 28;
        if (textPlaceholders.toDate) {
            textPlaceholders.toDate.fontSize = newSize;
            saveConfig();
            renderCertificate();
        }
    });
}

// Signature image upload handlers
const signature1Upload = document.getElementById('signature1Upload');
const signature2Upload = document.getElementById('signature2Upload');
const signature3Upload = document.getElementById('signature3Upload');
const signature4Upload = document.getElementById('signature4Upload');
const signature5Upload = document.getElementById('signature5Upload');

// Add-signature button to reveal more inputs progressively (up to 5)
const addSignatureBtn = document.getElementById('addSignatureBtn');
let visibleSignatures = 1; // currently only signature1 shown
const maxSignatures = 5;

if (addSignatureBtn) {
    addSignatureBtn.addEventListener('click', () => {
        // Show next hidden signature input
        if (visibleSignatures < maxSignatures) {
            visibleSignatures++;
            const groupId = `sig${visibleSignatures}Group`;
            const grp = document.getElementById(groupId);
            if (grp) {
                grp.style.display = 'flex'; // show as flex to align with remove button
            }
        }

        // If we've reached max, hide the add button
        if (visibleSignatures >= maxSignatures) {
            addSignatureBtn.style.display = 'none';
        }
    });
}

// Remove signature functionality
document.addEventListener('click', (e) => {
    if (e.target.closest('.remove-sig-btn')) {
        const btn = e.target.closest('.remove-sig-btn');
        const sigNum = btn.dataset.sig;
        const groupId = `sig${sigNum}Group`;
        const group = document.getElementById(groupId);
        
        if (group) {
            // Hide the group
            group.style.display = 'none';
            
            // Clear the file input and preview
            const fileInput = document.getElementById(`signature${sigNum}Upload`);
            const preview = document.getElementById(`sig${sigNum}Preview`);
            if (fileInput) fileInput.value = '';
            if (preview) {
                preview.textContent = '';
                preview.style.color = '#666';
            }
            
            // Clear the signature from memory
            if (sigNum === '1') signatures.sigLeft = null;
            else if (sigNum === '2') signatures.sigCenter = null;
            else if (sigNum === '3') signatures.sigRight = null;
            else if (sigNum === '4') signatures.sig4 = null;
            else if (sigNum === '5') signatures.sig5 = null;
            
            // Clear placeholder
            const placeholderKey = sigNum === '1' ? 'sigLeftImage' : 
                                   sigNum === '2' ? 'sigCenterImage' : 
                                   sigNum === '3' ? 'sigRightImage' : 
                                   sigNum === '4' ? 'sig4Image' : 'sig5Image';
            if (textPlaceholders[placeholderKey]) {
                delete textPlaceholders[placeholderKey];
            }
            
            // Update visible count
            visibleSignatures--;
            
            // Show add button if under max
            if (visibleSignatures < maxSignatures && addSignatureBtn) {
                addSignatureBtn.style.display = 'inline-flex';
            }
            
            // Save and re-render
            saveConfig();
            renderCertificate();
        }
    }
});

// Helper: choose signature layout that displays the most uploaded images
function updateSignatureLayoutAfterUpload() {
    // Count how many images would be shown for each layout
    const layout1Count = signatures.sig1 ? 1 : 0;
    const layout2Count = (signatures.sig2Left ? 1 : 0) + (signatures.sig2Right ? 1 : 0);
    const layout3Count = (signatures.sig3Left ? 1 : 0) + (signatures.sig3Center ? 1 : 0) + (signatures.sig3Right ? 1 : 0);

    // Debug: log counts
    console.debug('Signature counts:', { layout1Count, layout2Count, layout3Count, currentLayout: signatureLayout });

    // Determine which layout shows the most images
    const maxCount = Math.max(layout1Count, layout2Count, layout3Count);

    // If current layout already has the max count, keep it to avoid unnecessary jumps
    const currentCount = signatureLayout === 1 ? layout1Count : (signatureLayout === 2 ? layout2Count : layout3Count);
    if (currentCount === maxCount && maxCount > 0) return;

    if (maxCount === layout3Count && maxCount > 0) {
        signatureLayout = 3;
    } else if (maxCount === layout2Count && maxCount > 0) {
        signatureLayout = 2;
    } else if (maxCount === layout1Count && maxCount > 0) {
        signatureLayout = 1;
    } else {
        // no signatures uploaded; default to 1
        signatureLayout = 1;
    }
    console.debug('Chosen signatureLayout:', signatureLayout);
}

// SIMPLIFIED: Each sidebar input maps to exactly one position (left/center/right)
// signature1Upload -> LEFT position
// signature2Upload -> CENTER position  
// signature3Upload -> RIGHT position

if (signature1Upload) {
    signature1Upload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type === 'image/png') {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    // Store as LEFT signature
                    signatures.sigLeft = img;
                    if (!textPlaceholders.sigLeftImage) {
                        textPlaceholders.sigLeftImage = { x: 0.25, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Left Signature', dragging: false };
                    }
                    saveConfig();
                    document.getElementById('sig1Preview').textContent = '✓ ' + file.name;
                    document.getElementById('sig1Preview').style.color = '#4CAF50';
                    renderCertificate();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a PNG image file.');
        }
    });
}

if (signature2Upload) {
    signature2Upload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type === 'image/png') {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    // Store as CENTER signature
                    signatures.sigCenter = img;
                    if (!textPlaceholders.sigCenterImage) {
                        textPlaceholders.sigCenterImage = { x: 0.5, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Center Signature', dragging: false };
                    }
                    saveConfig();
                    document.getElementById('sig2Preview').textContent = '✓ ' + file.name;
                    document.getElementById('sig2Preview').style.color = '#4CAF50';
                    renderCertificate();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a PNG image file.');
        }
    });
}

if (signature3Upload) {
    signature3Upload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type === 'image/png') {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    // Store as RIGHT signature
                    signatures.sigRight = img;
                    if (!textPlaceholders.sigRightImage) {
                        textPlaceholders.sigRightImage = { x: 0.75, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Right Signature', dragging: false };
                    }
                    saveConfig();
                    document.getElementById('sig3Preview').textContent = '✓ ' + file.name;
                    document.getElementById('sig3Preview').style.color = '#4CAF50';
                    renderCertificate();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a PNG image file.');
        }
    });
}

// Extra signature handlers (4 and 5)
if (signature4Upload) {
    signature4Upload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type === 'image/png') {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    // Store as signature 4
                    signatures.sig4 = img;
                    if (!textPlaceholders.sig4Image) {
                        // default position: slightly left of center-right
                        textPlaceholders.sig4Image = { x: 0.35, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Signature 4', dragging: false };
                    }
                    saveConfig();
                    const preview = document.getElementById('sig4Preview');
                    if (preview) {
                        preview.textContent = '✓ ' + file.name;
                        preview.style.color = '#4CAF50';
                    }
                    renderCertificate();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a PNG image file.');
        }
    });
}

if (signature5Upload) {
    signature5Upload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type === 'image/png') {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    // Store as signature 5
                    signatures.sig5 = img;
                    if (!textPlaceholders.sig5Image) {
                        // default position: slightly right of center-left
                        textPlaceholders.sig5Image = { x: 0.65, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Signature 5', dragging: false };
                    }
                    saveConfig();
                    const preview = document.getElementById('sig5Preview');
                    if (preview) {
                        preview.textContent = '✓ ' + file.name;
                        preview.style.color = '#4CAF50';
                    }
                    renderCertificate();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a PNG image file.');
        }
    });
}

// Note: Signature text inputs have been removed from the UI
// Text inputs are now only for certificate details (Name, Certified For, Dates)

// Signature font sizes are now permanent and set via `textPlaceholders`.
// The UI number inputs have been removed; sizes are no longer editable from the sidebar.

// Signature layout is fixed at single layout (slider removed from UI)
let signatureLayout = 1;

// Old signature details listeners removed - now using layout-specific inputs above

// Setup layout-specific signature uploads
const sig1UploadInput = document.getElementById('sig1Upload');
const sig2LeftUploadInput = document.getElementById('sig2LeftUpload');
const sig2RightUploadInput = document.getElementById('sig2RightUpload');
const sig3LeftUploadInput = document.getElementById('sig3LeftUpload');
const sig3CenterUploadInput = document.getElementById('sig3CenterUpload');
const sig3RightUploadInput = document.getElementById('sig3RightUpload');

if (sig1UploadInput) {
    sig1UploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures.sig1 = img;
                    if (!textPlaceholders.sig1Image) {
                        textPlaceholders.sig1Image = { x: 0.5, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig1 Image', dragging: false };
                        saveConfig();
                    }
                    updateSignatureLayoutAfterUpload();
                    renderCertificate();
                    const fileName = document.getElementById('sig1FileName');
                    if (fileName) fileName.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

if (sig2LeftUploadInput) {
    sig2LeftUploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures.sig2Left = img;
                    if (!textPlaceholders.sig2LeftImage) {
                        textPlaceholders.sig2LeftImage = { x: 0.25, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig2 Left Image', dragging: false };
                        saveConfig();
                    }
                    updateSignatureLayoutAfterUpload();
                    renderCertificate();
                    const fileName = document.getElementById('sig2LeftFileName');
                    if (fileName) fileName.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

if (sig2RightUploadInput) {
    sig2RightUploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures.sig2Right = img;
                    if (!textPlaceholders.sig2RightImage) {
                        textPlaceholders.sig2RightImage = { x: 0.75, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig2 Right Image', dragging: false };
                        saveConfig();
                    }
                    updateSignatureLayoutAfterUpload();
                    renderCertificate();
                    const fileName = document.getElementById('sig2RightFileName');
                    if (fileName) fileName.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

if (sig3LeftUploadInput) {
    sig3LeftUploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures.sig3Left = img;
                    if (!textPlaceholders.sig3LeftImage) {
                        textPlaceholders.sig3LeftImage = { x: 0.17, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig3 Left Image', dragging: false };
                        saveConfig();
                    }
                    updateSignatureLayoutAfterUpload();
                    renderCertificate();
                    const fileName = document.getElementById('sig3LeftFileName');
                    if (fileName) fileName.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

if (sig3CenterUploadInput) {
    sig3CenterUploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures.sig3Center = img;
                    if (!textPlaceholders.sig3CenterImage) {
                        textPlaceholders.sig3CenterImage = { x: 0.5, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig3 Center Image', dragging: false };
                        saveConfig();
                    }
                    updateSignatureLayoutAfterUpload();
                    renderCertificate();
                    const fileName = document.getElementById('sig3CenterFileName');
                    if (fileName) fileName.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

if (sig3RightUploadInput) {
    sig3RightUploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    signatures.sig3Right = img;
                    if (!textPlaceholders.sig3RightImage) {
                        textPlaceholders.sig3RightImage = { x: 0.83, y: 0.78, scale: 1.0, type: 'signatureImage', label: 'Sig3 Right Image', dragging: false };
                        saveConfig();
                    }
                    updateSignatureLayoutAfterUpload();
                    renderCertificate();
                    const fileName = document.getElementById('sig3RightFileName');
                    if (fileName) fileName.textContent = file.name;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Handle checkbox to use pre-loaded signature images
const handleSignatureCheckbox = (checkboxId, signatureKey, imageName) => {
    const checkbox = document.getElementById(checkboxId);
    if (checkbox) {
        checkbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                const img = new Image();
                img.onload = () => {
                    signatures[signatureKey] = img;
                    renderCertificate();
                    const nameSpan = document.getElementById(`${signatureKey}-name`);
                    if (nameSpan) {
                        nameSpan.textContent = imageName;
                    }
                };
                img.src = `signature-images/${imageName}`;
            } else {
                // Uncheck - remove the signature
                delete signatures[signatureKey];
                renderCertificate();
                const nameSpan = document.getElementById(`${signatureKey}-name`);
                if (nameSpan) {
                    nameSpan.textContent = '';
                }
            }
        });
    }
};

// Map signature names based on name input field placeholders
// sig1: Frank.png (MR.S. FRANKLIN RAJ)
// sig2: Aarthi.png (DR. C. AARTHI RAM)
// sig3: Wilson.png (DR.P.WILSON)
handleSignatureCheckbox('sig1UseImage', 'sig1', 'Frank.png');
handleSignatureCheckbox('sig2UseImage', 'sig2', 'Aarthi.png');
handleSignatureCheckbox('sig3UseImage', 'sig3', 'Wilson.png');

// Signature layout functions removed (slider UI removed from HTML)

// Check if mouse is over resize handle
const isOverResizeHandle = (mouseX, mouseY, handleX, handleY) => {
    return Math.abs(mouseX - handleX) < 10 && Math.abs(mouseY - handleY) < 10;
};

// Get resize handles for a placeholder
const getResizeHandles = (key) => {
    const placeholder = textPlaceholders[key];
    const x = canvas.width * placeholder.x;
    const y = canvas.height * placeholder.y;

    if (placeholder.fontSize) {
        const text = getPlaceholderText(key);
        ctx.font = `${placeholder.fontSize}px Arial`;
        const metrics = ctx.measureText(text);
        const width = metrics.width + 20;
        const height = placeholder.fontSize + 10;

        return {
            topLeft: { x: x - width / 2, y: y - height + 5 },
            topRight: { x: x + width / 2, y: y - height + 5 },
            bottomLeft: { x: x - width / 2, y: y + 5 },
            bottomRight: { x: x + width / 2, y: y + 5 }
        };
    } else {
        return {
            topLeft: { x: x - placeholder.width / 2, y: y - placeholder.height },
            topRight: { x: x + placeholder.width / 2, y: y - placeholder.height },
            bottomLeft: { x: x - placeholder.width / 2, y: y },
            bottomRight: { x: x + placeholder.width / 2, y: y }
        };
    }
};

// Canvas mouse events for dragging and resizing
let resizeHandle = null;

if (canvas) {
    canvas.addEventListener('mousedown', (e) => {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const mouseX = (e.clientX - rect.left) * scaleX;
        const mouseY = (e.clientY - rect.top) * scaleY;

        // First check if clicking on resize handle of selected placeholder
        // DISABLED: Resize by mouse is disabled
        /*
        if (selectedPlaceholder) {
            const handles = getResizeHandles(selectedPlaceholder);

            for (let [handleName, handle] of Object.entries(handles)) {
                if (isOverResizeHandle(mouseX, mouseY, handle.x, handle.y)) {
                    isResizing = true;
                    resizeHandle = handleName;
                    canvas.style.cursor = 'nwse-resize';
                    console.log(`Resizing ${selectedPlaceholder} from ${handleName}`);
                    return;
                }
            }
        }
        */

        // Check if clicking on any placeholder
        let foundPlaceholder = false;
        for (let key in textPlaceholders) {
            const placeholder = textPlaceholders[key];

            // Allow signature images to be selected, moved, and resized
            if (placeholder.type === 'signatureImage') {
                const px = canvas.width * placeholder.x;
                const py = canvas.height * placeholder.y;
                const baseWidth = 120;
                const baseHeight = 60;
                const imgWidth = baseWidth * placeholder.scale;
                const imgHeight = baseHeight * placeholder.scale;

                // Check if clicking on resize handle (bottom-right corner)
                const handleSize = 12;
                const handleX = px + imgWidth / 2;
                const handleY = py + imgHeight / 2;

                if (Math.abs(mouseX - handleX) < handleSize && Math.abs(mouseY - handleY) < handleSize) {
                    selectedPlaceholder = key;
                    isResizing = true;
                    resizeHandle = 'signature';
                    canvas.style.cursor = 'nwse-resize';
                    console.log(`Resizing signature image: ${key}`);
                    foundPlaceholder = true;
                    break;
                }

                // Check if clicking inside the image (for dragging)
                if (mouseX >= px - imgWidth / 2 && mouseX <= px + imgWidth / 2 &&
                    mouseY >= py - imgHeight / 2 && mouseY <= py + imgHeight / 2) {
                    selectedPlaceholder = key;
                    isDragging = true;
                    dragOffsetX = mouseX - px;
                    dragOffsetY = mouseY - py;
                    canvas.style.cursor = 'move';
                    renderCertificate();
                    console.log(`Selected signature image: ${key}`);
                    foundPlaceholder = true;
                    break;
                }
            } else if (placeholder.type === 'line') {
                // Line hit detection - check endpoints first, then line body
                const x1 = canvas.width * placeholder.x1;
                const y1 = canvas.height * placeholder.y1;
                const x2 = canvas.width * placeholder.x2;
                const y2 = canvas.height * placeholder.y2;

                // Check if clicking on start point
                if (Math.abs(mouseX - x1) < 10 && Math.abs(mouseY - y1) < 10) {
                    selectedPlaceholder = key;
                    isDragging = true;
                    dragOffsetX = 0;
                    dragOffsetY = 0;
                    lineEndpointDragging = 'start';
                    canvas.style.cursor = 'move';
                    renderCertificate();
                    console.log(`Selected line ${key} start point`);
                    foundPlaceholder = true;
                    break;
                }

                // Check if clicking on end point
                if (Math.abs(mouseX - x2) < 10 && Math.abs(mouseY - y2) < 10) {
                    selectedPlaceholder = key;
                    isDragging = true;
                    dragOffsetX = 0;
                    dragOffsetY = 0;
                    lineEndpointDragging = 'end';
                    canvas.style.cursor = 'move';
                    renderCertificate();
                    console.log(`Selected line ${key} end point`);
                    foundPlaceholder = true;
                    break;
                }

                // Check if clicking on line body (distance from point to line)
                const lineLength = Math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2);
                const distance = Math.abs((y2 - y1) * mouseX - (x2 - x1) * mouseY + x2 * y1 - y2 * x1) / lineLength;

                if (distance < 10 && mouseX >= Math.min(x1, x2) - 10 && mouseX <= Math.max(x1, x2) + 10 &&
                    mouseY >= Math.min(y1, y2) - 10 && mouseY <= Math.max(y1, y2) + 10) {
                    selectedPlaceholder = key;
                    isDragging = true;
                    dragOffsetX = mouseX - (x1 + x2) / 2;
                    dragOffsetY = mouseY - (y1 + y2) / 2;
                    lineEndpointDragging = 'both';
                    canvas.style.cursor = 'move';
                    renderCertificate();
                    console.log(`Selected line ${key}`);
                    foundPlaceholder = true;
                    break;
                }
            } else {
                // Regular text/image placeholder hit detection
                const px = canvas.width * placeholder.x;
                const py = canvas.height * placeholder.y;
                const dx = Math.abs(mouseX - px);
                const dy = Math.abs(mouseY - py);

                // Dynamic hit detection area based on element type - REDUCED for better precision
                let hitArea = 40; // reduced from 100 for images
                if (placeholder.fontSize) {
                    // For text, scale hit area based on font size - reduced multiplier
                    hitArea = Math.max(20, placeholder.fontSize * 1.2);
                }

                if (dx < hitArea && dy < hitArea) {
                    selectedPlaceholder = key;
                    isDragging = true;
                    dragOffsetX = mouseX - px;
                    dragOffsetY = mouseY - py;
                    lineEndpointDragging = null;
                    canvas.style.cursor = 'move';
                    renderCertificate();
                    console.log(`Selected: ${key}`);
                    foundPlaceholder = true;
                    break;
                }
            }
        }

        // If no placeholder found, prepare for drag selection box
        if (!foundPlaceholder) {
            selectedPlaceholder = null;
            selectedPlaceholders = [];
            dragStarted = false; // Reset drag started flag
            selectBoxStart = { x: mouseX, y: mouseY };
            selectBoxEnd = { x: mouseX, y: mouseY };
            // Don't change cursor yet - wait for actual drag
            renderCertificate();
        }
    });

    canvas.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const mouseX = (e.clientX - rect.left) * scaleX;
        const mouseY = (e.clientY - rect.top) * scaleY;

        // Check if we should start drag selection (moved at least 15 pixels from start)
        if (!isSelectingBox && !dragStarted && (selectBoxStart.x > 0 || selectBoxStart.y > 0)) {
            const dx = Math.abs(mouseX - selectBoxStart.x);
            const dy = Math.abs(mouseY - selectBoxStart.y);
            if (dx > 15 || dy > 15) {
                dragStarted = true;
                isSelectingBox = true;
                canvas.style.cursor = 'crosshair';
                console.log('ðŸ”· Drag selection started');
            }
        }

        if (isSelectingBox) {
            // Update selection box end point
            selectBoxEnd = { x: mouseX, y: mouseY };
            renderCertificate();
            return;
        }

        if (isResizing && selectedPlaceholder && resizeHandle) {
            const placeholder = textPlaceholders[selectedPlaceholder];

            if (resizeHandle === 'signature' && placeholder.type === 'signatureImage') {
                // Resize signature image by adjusting scale
                const px = canvas.width * placeholder.x;
                const py = canvas.height * placeholder.y;
                const distanceX = Math.abs(mouseX - px);
                const distanceY = Math.abs(mouseY - py);
                const distance = Math.max(distanceX, distanceY);
                // Calculate scale based on distance from center (base size 120x60)
                const newScale = Math.max(0.3, Math.min(3.0, distance / 60));
                placeholder.scale = newScale;
                console.log(`${selectedPlaceholder} scale: ${newScale.toFixed(2)}`);
                renderCertificate();
            } else if (placeholder.fontSize) {
                // Resize text by changing font size based on vertical distance
                const centerY = canvas.height * placeholder.y;
                const distance = Math.abs(mouseY - centerY);
                const newFontSize = Math.max(8, Math.min(100, Math.round(distance / 3)));
                placeholder.fontSize = newFontSize;
                console.log(`${selectedPlaceholder} fontSize: ${newFontSize}`);
                renderCertificate();
            } else {
                // Resize image based on handle position
                const centerX = canvas.width * placeholder.x;
                const centerY = canvas.height * placeholder.y;
                const newWidth = Math.abs(mouseX - centerX) * 2;
                const newHeight = Math.abs(mouseY - centerY) * 2;
                placeholder.width = Math.max(20, Math.min(500, Math.round(newWidth)));
                placeholder.height = Math.max(20, Math.min(300, Math.round(newHeight)));
                console.log(`${selectedPlaceholder} size: ${placeholder.width}x${placeholder.height}`);
                renderCertificate();
            }
        } else if (isDragging && selectedPlaceholder) {
            const placeholder = textPlaceholders[selectedPlaceholder];

            if (placeholder.type === 'signatureImage') {
                // Move signature image - no resizing, just position
                const newX = (mouseX - dragOffsetX) / canvas.width;
                const newY = (mouseY - dragOffsetY) / canvas.height;
                placeholder.x = Math.max(0, Math.min(1, newX));
                placeholder.y = Math.max(0, Math.min(1, newY));
                renderCertificate();
                console.log(`${selectedPlaceholder}: x=${placeholder.x.toFixed(3)}, y=${placeholder.y.toFixed(3)}`);
            } else if (placeholder.type === 'line') {
                // Handle line dragging
                const newX = mouseX / canvas.width;
                const newY = mouseY / canvas.height;

                if (lineEndpointDragging === 'start') {
                    // Move start point
                    placeholder.x1 = Math.max(0, Math.min(1, newX));
                    placeholder.y1 = Math.max(0, Math.min(1, newY));
                } else if (lineEndpointDragging === 'end') {
                    // Move end point
                    placeholder.x2 = Math.max(0, Math.min(1, newX));
                    placeholder.y2 = Math.max(0, Math.min(1, newY));
                } else if (lineEndpointDragging === 'both') {
                    // Move entire line
                    const centerX = (placeholder.x1 + placeholder.x2) / 2;
                    const centerY = (placeholder.y1 + placeholder.y2) / 2;
                    const targetX = (mouseX - dragOffsetX) / canvas.width;
                    const targetY = (mouseY - dragOffsetY) / canvas.height;
                    const deltaX = targetX - centerX;
                    const deltaY = targetY - centerY;

                    placeholder.x1 = Math.max(0, Math.min(1, placeholder.x1 + deltaX));
                    placeholder.y1 = Math.max(0, Math.min(1, placeholder.y1 + deltaY));
                    placeholder.x2 = Math.max(0, Math.min(1, placeholder.x2 + deltaX));
                    placeholder.y2 = Math.max(0, Math.min(1, placeholder.y2 + deltaY));
                }
            } else {
                const newX = (mouseX - dragOffsetX) / canvas.width;
                const newY = (mouseY - dragOffsetY) / canvas.height;

                // Always move the entire signature text group (name/designation/college)
                // for the active layout when any of that group's placeholders is dragged.
                const layoutGroups = {
                    1: [
                        ['sig1GroupName', 'sig1GroupDesignation', 'sig1GroupCollege']
                    ],
                    2: [
                        ['sig2GroupName', 'sig2GroupDesignation', 'sig2GroupCollege'],
                        ['sig2bGroupName', 'sig2bGroupDesignation', 'sig2bGroupCollege']
                    ],
                    3: [
                        ['sig3GroupName', 'sig3GroupDesignation', 'sig3GroupCollege'],
                        ['sig3bGroupName', 'sig3bGroupDesignation', 'sig3bGroupCollege'],
                        ['sig3cGroupName', 'sig3cGroupDesignation', 'sig3cGroupCollege']
                    ]
                };

                let movedGroup = false;
                if (layoutGroups[signatureLayout]) {
                    const groups = layoutGroups[signatureLayout];
                    for (let grp of groups) {
                        if (grp.includes(selectedPlaceholder)) {
                            const deltaX = newX - textPlaceholders[selectedPlaceholder].x;
                            const deltaY = newY - textPlaceholders[selectedPlaceholder].y;

                            grp.forEach(key => {
                                if (textPlaceholders[key]) {
                                    textPlaceholders[key].x = Math.max(0, Math.min(1, textPlaceholders[key].x + deltaX));
                                    textPlaceholders[key].y = Math.max(0, Math.min(1, textPlaceholders[key].y + deltaY));
                                }
                            });

                            movedGroup = true;
                            break;
                        }
                    }
                }

                if (!movedGroup) {
                    // Non-signature items move independently
                    textPlaceholders[selectedPlaceholder].x = Math.max(0, Math.min(1, newX));
                    textPlaceholders[selectedPlaceholder].y = Math.max(0, Math.min(1, newY));
                }
            }

            renderCertificate();

            // Log position for debugging
            console.log(`${selectedPlaceholder}: x=${textPlaceholders[selectedPlaceholder].x.toFixed(3)}, y=${textPlaceholders[selectedPlaceholder].y.toFixed(3)}`);
        } else {
            // Check if hovering over resize handles - DISABLED
            let overHandle = false;
            /*
            if (selectedPlaceholder) {
                const handles = getResizeHandles(selectedPlaceholder);
                for (let [handleName, handle] of Object.entries(handles)) {
                    if (isOverResizeHandle(mouseX, mouseY, handle.x, handle.y)) {
                        overHandle = true;
                        canvas.style.cursor = 'nwse-resize';
                        break;
                    }
                }
            }
            */

            if (!overHandle) {
                // Check if hovering over any placeholder
                let hovering = false;
                for (let key in textPlaceholders) {
                    const placeholder = textPlaceholders[key];
                    const px = canvas.width * placeholder.x;
                    const py = canvas.height * placeholder.y;
                    const dx = Math.abs(mouseX - px);
                    const dy = Math.abs(mouseY - py);

                    // Dynamic hit detection area based on element type
                    let hitArea = 100; // default for images
                    if (placeholder.fontSize) {
                        // For text, scale hit area based on font size
                        hitArea = Math.max(50, placeholder.fontSize * 3);
                    }

                    if (dx < hitArea && dy < hitArea) {
                        hovering = true;
                        break;
                    }
                }
                canvas.style.cursor = hovering ? 'pointer' : 'default';
            }
        }
    });

    canvas.addEventListener('mouseup', () => {
        if (isSelectingBox) {
            // Finalize selection box and find all elements inside
            const minX = Math.min(selectBoxStart.x, selectBoxEnd.x);
            const maxX = Math.max(selectBoxStart.x, selectBoxEnd.x);
            const minY = Math.min(selectBoxStart.y, selectBoxEnd.y);
            const maxY = Math.max(selectBoxStart.y, selectBoxEnd.y);

            selectedPlaceholders = [];

            for (let key in textPlaceholders) {
                const placeholder = textPlaceholders[key];
                const px = canvas.width * placeholder.x;
                const py = canvas.height * placeholder.y;

                // Check if element is inside selection box
                if (px >= minX && px <= maxX && py >= minY && py <= maxY) {
                    selectedPlaceholders.push(key);
                }
            }

            isSelectingBox = false;
            dragStarted = false;
            selectBoxStart = { x: 0, y: 0 }; // Reset selection box start
            selectBoxEnd = { x: 0, y: 0 }; // Reset selection box end
            canvas.style.cursor = 'default';
            console.log(`âœ… Selected ${selectedPlaceholders.length} elements: ${selectedPlaceholders.join(', ')}`);
            renderCertificate();
            return;
        }

        // Reset drag selection variables
        dragStarted = false;
        isSelectingBox = false;
        selectBoxStart = { x: 0, y: 0 }; // Reset on every mouseup
        selectBoxEnd = { x: 0, y: 0 };

        if (isDragging || isResizing) {
            isDragging = false;
            isResizing = false;
            resizeHandle = null;
            lineEndpointDragging = null;
            canvas.style.cursor = 'default';

            // Log final position/size
            if (selectedPlaceholder) {
                const p = textPlaceholders[selectedPlaceholder];
                if (p.type === 'signatureImage') {
                    console.log(`✅ Final ${selectedPlaceholder}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, scale=${p.scale.toFixed(2)}`);
                } else if (p.type === 'line') {
                    console.log(`✅ Final ${selectedPlaceholder}: x1=${p.x1.toFixed(3)}, y1=${p.y1.toFixed(3)}, x2=${p.x2.toFixed(3)}, y2=${p.y2.toFixed(3)}`);
                } else if (p.fontSize) {
                    console.log(`✅ Final ${selectedPlaceholder}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, fontSize=${p.fontSize}`);
                } else {
                    console.log(`✅ Final ${selectedPlaceholder}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, width=${p.width}, height=${p.height}`);
                }
            }

            // Auto-save configuration after any change
            saveConfig();
        }
    });

    canvas.addEventListener('dblclick', (e) => {
        const rect = canvas.getBoundingClientRect();
        const mouseX = (e.clientX - rect.left) / canvas.width;
        const mouseY = (e.clientY - rect.top) / canvas.height;

        // Double click handler - open text editor modal for text placeholders
        if (selectedPlaceholder && textPlaceholders[selectedPlaceholder]) {
            const placeholder = textPlaceholders[selectedPlaceholder];

            if (placeholder.fontSize) {
                // Open text editor modal for text placeholders
                openTextEditorModal(selectedPlaceholder);
            } else if (placeholder.type === 'signatureImage') {
                // Signature images - no modal, just info
                showModal('Signature images can be moved by dragging and resized using the corner handle.', 'Signature Image', 'info');
            } else {
                // Other types (images) - prompt for dimensions
                const newWidth = prompt(`Enter width for ${placeholder.label}:`, placeholder.width);
                const newHeight = prompt(`Enter height for ${placeholder.label}:`, placeholder.height);
                if (newWidth && !isNaN(newWidth) && newHeight && !isNaN(newHeight)) {
                    placeholder.width = parseInt(newWidth);
                    placeholder.height = parseInt(newHeight);
                    renderCertificate();
                    console.log(`${selectedPlaceholder} size: ${newWidth}x${newHeight}`);
                    saveConfig();
                }
            }
        }
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Don't intercept keyboard shortcuts when typing in input fields
    const activeElement = document.activeElement;
    if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
        return;
    }

    if (!selectedPlaceholder) return;

    const step = e.shiftKey ? 0.001 : 0.005;
    const placeholder = textPlaceholders[selectedPlaceholder];
    let configChanged = false;

    switch (e.key) {
        case 'Delete':
        case 'Backspace':
            console.log('Delete key pressed. Selected:', selectedPlaceholder);

            if (!selectedPlaceholder) {
                console.log('Cannot delete - nothing selected');
                break;
            }

            const placeholder = textPlaceholders[selectedPlaceholder];
            if (!placeholder) break;

            // Check if it's a deletable type
            const isLineType = placeholder.type === 'line';
            const isCustomText = selectedPlaceholder.startsWith('customText');
            const isSignatureGroup = selectedPlaceholder.includes('Group') ||
                selectedPlaceholder.includes('Image') ||
                selectedPlaceholder.startsWith('sig');
            const isProtectedField = ['certNo', 'name', 'certifiedFor', 'fromDate', 'toDate'].includes(selectedPlaceholder);

            if (isProtectedField) {
                showModal('Cannot delete core certificate fields. You can only delete signature groups, lines, and custom text.', 'Cannot Delete', 'warning');
                e.preventDefault();
                break;
            }

            if (isLineType || isCustomText || isSignatureGroup) {
                console.log('Deleting:', selectedPlaceholder);
                const placeholderLabel = placeholder.label || selectedPlaceholder;

                // Delete the placeholder
                delete textPlaceholders[selectedPlaceholder];

                // Remove from custom text fields array if applicable
                if (isCustomText) {
                    customTextFields = customTextFields.filter(id => id !== selectedPlaceholder);
                    const formGroup = document.getElementById(`${selectedPlaceholder}-group`);
                    if (formGroup) formGroup.remove();

                    // Hide container if no custom fields
                    if (customTextFields.length === 0) {
                        customTextsContainer.style.display = 'none';
                    }
                }

                // Deselect and re-render
                selectedPlaceholder = null;
                renderCertificate();
                saveConfig();

                showModal(`${placeholderLabel} deleted successfully.`, 'Item Deleted', 'success');
                e.preventDefault();
            } else {
                console.log('Cannot delete - not a deletable type');
            }
            break;
        case 'Escape':
            selectedPlaceholder = null;
            renderCertificate();
            break;
        case 'ArrowLeft':
            if (placeholder.type === 'line') break; // Skip for lines
            placeholder.x -= step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'ArrowRight':
            if (placeholder.type === 'line') break; // Skip for lines
            placeholder.x += step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'ArrowUp':
            if (placeholder.type === 'line') break; // Skip for lines
            placeholder.y -= step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'ArrowDown':
            if (placeholder.type === 'line') break; // Skip for lines
            placeholder.y += step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case '+':
        case '=':
            if (placeholder.fontSize) {
                placeholder.fontSize += 1;
                renderCertificate();
                configChanged = true;
                e.preventDefault();
            }
            break;
        case '-':
        case '_':
            if (placeholder.fontSize && placeholder.fontSize > 1) {
                placeholder.fontSize -= 1;
                renderCertificate();
                configChanged = true;
                e.preventDefault();
            }
            break;
    }

    // Auto-save after keyboard adjustments
    if (configChanged) {
        saveConfig();
    }
});

// Calibration tools
const toggleGridBtn = document.getElementById('toggleGrid');
const copyPositionsBtn = document.getElementById('copyPositions');
const deleteSelectedBtn = document.getElementById('deleteSelected');

if (toggleGridBtn) {
    toggleGridBtn.addEventListener('click', () => {
        showGrid = !showGrid;
        renderCertificate();
        console.log(`Grid ${showGrid ? 'enabled' : 'disabled'}`);
    });
}

if (deleteSelectedBtn) {
    deleteSelectedBtn.addEventListener('click', () => {
        if (!selectedPlaceholder) {
            showModal('Please select an element on the canvas first.', 'Nothing Selected', 'warning');
            return;
        }

        const placeholder = textPlaceholders[selectedPlaceholder];
        if (!placeholder) return;

        // Check if it's a protected field
        const isProtectedField = ['certNo', 'name', 'certifiedFor', 'fromDate', 'toDate'].includes(selectedPlaceholder);

        if (isProtectedField) {
            showModal('Cannot delete core certificate fields. You can only delete signature groups, lines, and custom text.', 'Cannot Delete', 'warning');
            return;
        }

        const placeholderLabel = placeholder.label || selectedPlaceholder;

        // Confirm deletion
        showModal(`Are you sure you want to delete "${placeholderLabel}"?`, 'Confirm Delete', 'warning').then(() => {
            // Delete the placeholder
            delete textPlaceholders[selectedPlaceholder];

            // Remove from custom text fields array if applicable
            if (selectedPlaceholder.startsWith('customText')) {
                customTextFields = customTextFields.filter(id => id !== selectedPlaceholder);
                const formGroup = document.getElementById(`${selectedPlaceholder}-group`);
                if (formGroup) formGroup.remove();

                if (customTextFields.length === 0) {
                    customTextsContainer.style.display = 'none';
                }
            }

            // Deselect and re-render
            selectedPlaceholder = null;
            renderCertificate();
            saveConfig();

            showModal(`${placeholderLabel} deleted successfully.`, 'Deleted', 'success');
        }).catch(() => {
            // User cancelled
        });
    });
}

if (copyPositionsBtn) {
    copyPositionsBtn.addEventListener('click', () => {
        // Manual save (already auto-saved, but provides user feedback)
        saveConfig();

        // Show current positions in console
        console.log('Current placeholder positions:');
        for (let key in textPlaceholders) {
            const p = textPlaceholders[key];
            if (p.fontSize) {
                console.log(`${key}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, fontSize=${p.fontSize}`);
            } else {
                console.log(`${key}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, width=${p.width}, height=${p.height}`);
            }
        }

        showModal('Configuration saved!\n\nYour placeholder positions are automatically saved.\nCheck the console for current position values.', 'Configuration Saved', 'success');
    });
}

// Zoom functionality
const updateZoom = () => {
    const zoomLevelEl = document.getElementById('zoomLevel');
    if (canvas && zoomLevelEl) {
        canvas.style.transform = `scale(${zoomLevel})`;
        // Display zoom: 50% actual = 100% displayed
        const displayZoom = Math.round((zoomLevel / 0.5) * 100);
        zoomLevelEl.textContent = `${displayZoom}%`;
    }
};

const zoomInBtn = document.getElementById('zoomIn');
const zoomOutBtn = document.getElementById('zoomOut');
const zoomResetBtn = document.getElementById('zoomReset');

if (zoomInBtn) {
    zoomInBtn.addEventListener('click', () => {
        zoomLevel = Math.min(zoomLevel + 0.05, 1.5); // Max 300% displayed (1.5 actual)
        updateZoom();
    });
}

if (zoomOutBtn) {
    zoomOutBtn.addEventListener('click', () => {
        zoomLevel = Math.max(zoomLevel - 0.05, 0.15); // Min 30% displayed (0.15 actual)
        updateZoom();
    });
}

if (zoomResetBtn) {
    zoomResetBtn.addEventListener('click', () => {
        zoomLevel = 0.5; // Reset to 100% displayed (0.5 actual)
        updateZoom();
    });
}

// Mouse wheel zoom
const canvasContainer = document.getElementById('canvasContainer');
if (canvasContainer) {
    canvasContainer.addEventListener('wheel', (e) => {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.05 : 0.05;
            zoomLevel = Math.max(0.15, Math.min(1.5, zoomLevel + delta));
            updateZoom();
        }
    }, { passive: false });
}

// Keyboard zoom shortcuts
document.addEventListener('keydown', (e) => {
    // Don't intercept when typing in input fields
    const activeElement = document.activeElement;
    if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
        return;
    }

    if ((e.ctrlKey || e.metaKey) && !selectedPlaceholder) {
        if (e.key === '=' || e.key === '+') {
            e.preventDefault();
            zoomLevel = Math.min(zoomLevel + 0.05, 1.5);
            updateZoom();
        } else if (e.key === '-' || e.key === '_') {
            e.preventDefault();
            zoomLevel = Math.max(zoomLevel - 0.05, 0.15);
            updateZoom();
        } else if (e.key === '0') {
            e.preventDefault();
            zoomLevel = 0.5; // Reset to 100% displayed
            updateZoom();
        }
    }
});

// Modal Notification System
const notificationModal = document.getElementById('notificationModal');
const modalTitle = document.getElementById('modalTitle');
const modalMessage = document.getElementById('modalMessage');
const modalIcon = document.getElementById('modalIcon');
const modalOk = document.getElementById('modalOk');
const modalCancel = document.getElementById('modalCancel');
const modalClose = document.getElementById('modalClose');

let modalResolve = null;

const showModal = (message, title = 'Notification', type = 'info', showCancel = false) => {
    return new Promise((resolve) => {
        if (!notificationModal || !modalTitle || !modalMessage || !modalIcon || !modalOk) {
            console.error('Modal elements not found');
            resolve(true);
            return;
        }

        modalResolve = resolve;
        modalTitle.textContent = title;
        modalMessage.textContent = message;

        // Reset header class
        const header = document.querySelector('.modal-header');
        if (!header) {
            resolve(true);
            return;
        }
        header.className = 'modal-header';

        // Set icon based on type
        if (type === 'success') {
            header.classList.add('success');
            modalIcon.innerHTML = '<circle cx="12" cy="12" r="10"></circle><path d="M9 12l2 2 4-4"></path>';
        } else if (type === 'error') {
            header.classList.add('error');
            modalIcon.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>';
        } else if (type === 'warning') {
            header.classList.add('warning');
            modalIcon.innerHTML = '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
        } else {
            modalIcon.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>';
        }

        // Show/hide cancel button
        if (modalCancel) {
            if (showCancel) {
                modalCancel.style.display = 'inline-flex';
            } else {
                modalCancel.style.display = 'none';
            }
        }

        notificationModal.classList.add('show');
    });
};

const hideModal = (result = true) => {
    if (notificationModal) {
        notificationModal.classList.remove('show');
    }
    if (modalResolve) {
        modalResolve(result);
        modalResolve = null;
    }
};

if (modalOk) modalOk.addEventListener('click', () => hideModal(true));
if (modalCancel) modalCancel.addEventListener('click', () => hideModal(false));
if (modalClose) modalClose.addEventListener('click', () => hideModal(false));

// Close modal when clicking outside
if (notificationModal) {
    notificationModal.addEventListener('click', (e) => {
        if (e.target === notificationModal) {
            hideModal(false);
        }
    });
}

// Progress Modal for Bulk Generation
let progressModalElement = null;

const showProgressModal = (totalCount) => {
    // Remove existing modal if present
    if (progressModalElement) {
        progressModalElement.remove();
        progressModalElement = null;
    }

    // Create new progress modal
    progressModalElement = document.createElement('div');
    progressModalElement.className = 'modal show';
    progressModalElement.style.cssText = 'display: flex !important; z-index: 10001;';
    progressModalElement.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header" style="background: #00a8ff; color: white;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <h3>Generating Certificates</h3>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div id="progressModalContent">
                    <p id="progressModalText" style="margin-bottom: 8px; font-size: 14px; color: #444;">
                        Generating certificate 0 of ${totalCount}...
                    </p>
                    <p id="progressModalETA" style="margin-bottom: 16px; font-size: 12px; color: #666;">
                        Estimated time remaining: Calculating...
                    </p>
                    <div style="width: 100%; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; margin-bottom: 16px;">
                        <div id="progressModalBar" style="width: 0%; height: 100%; background: #00a8ff; transition: width 0.3s;"></div>
                    </div>
                    <p id="progressModalPercent" style="text-align: center; font-weight: 600; color: #00a8ff; font-size: 18px;">0%</p>
                </div>
                <div id="progressModalConfirm" style="display: none;">
                    <p style="margin-bottom: 16px; font-size: 14px; color: #444; text-align: center;">
                        Are you sure you want to cancel?<br>
                        <span style="font-size: 12px; color: #666;">Progress will be lost.</span>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="progressModalCancel" class="btn btn-secondary">Cancel</button>
                <button id="progressModalConfirmYes" class="btn btn-primary" style="display: none;">Yes, Cancel</button>
                <button id="progressModalConfirmNo" class="btn btn-secondary" style="display: none;">No, Continue</button>
            </div>
        </div>
    `;
    document.body.appendChild(progressModalElement);

    // Add button handlers
    setTimeout(() => {
        const cancelBtn = document.getElementById('progressModalCancel');
        const confirmYesBtn = document.getElementById('progressModalConfirmYes');
        const confirmNoBtn = document.getElementById('progressModalConfirmNo');
        const progressContent = document.getElementById('progressModalContent');
        const confirmContent = document.getElementById('progressModalConfirm');

        if (cancelBtn) {
            cancelBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Show confirmation in same modal
                progressContent.style.display = 'none';
                confirmContent.style.display = 'block';
                cancelBtn.style.display = 'none';
                confirmYesBtn.style.display = 'inline-block';
                confirmNoBtn.style.display = 'inline-block';
            };
        }

        if (confirmYesBtn) {
            confirmYesBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();

                bulkGenerationCancelled = true;
                console.log('ðŸ›‘ User confirmed cancellation');
            };
        }

        if (confirmNoBtn) {
            confirmNoBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Return to progress view
                progressContent.style.display = 'block';
                confirmContent.style.display = 'none';
                cancelBtn.style.display = 'inline-block';
                confirmYesBtn.style.display = 'none';
                confirmNoBtn.style.display = 'none';
            };
        }
    }, 100);

    return progressModalElement;
};

const updateProgressModal = (current, total, customMessage = null, etaSeconds = null) => {
    const textEl = document.getElementById('progressModalText');
    const barEl = document.getElementById('progressModalBar');
    const percentEl = document.getElementById('progressModalPercent');
    const etaEl = document.getElementById('progressModalETA');

    const progress = Math.round((current / total) * 100);

    if (textEl) {
        textEl.textContent = customMessage || `Generating certificate ${current} of ${total}...`;
    }
    if (barEl) {
        barEl.style.width = `${progress}%`;
    }
    if (percentEl) {
        percentEl.textContent = `${progress}%`;
    }
    if (etaEl && etaSeconds !== null) {
        if (etaSeconds > 60) {
            const minutes = Math.floor(etaSeconds / 60);
            const seconds = etaSeconds % 60;
            etaEl.textContent = `Estimated time remaining: ${minutes}m ${seconds}s`;
        } else if (etaSeconds > 0) {
            etaEl.textContent = `Estimated time remaining: ${etaSeconds}s`;
        } else {
            etaEl.textContent = 'Almost done...';
        }
    } else if (etaEl && customMessage) {
        etaEl.textContent = '';
    }
};

const hideProgressModal = () => {
    if (progressModalElement) {
        progressModalElement.style.display = 'none';
    }
};

// Old signature management code removed - now using progressive reveal system above

// Custom text fields management
let customTextFields = [];
let customTextCounter = 0;
const insertTextBtn = document.getElementById('insertTextBtn');
const customTextsContainer = document.getElementById('customTextsContainer');

// Add new custom text field
if (insertTextBtn && customTextsContainer) {
    insertTextBtn.addEventListener('click', () => {
        customTextCounter++;
        const fieldId = `customText${customTextCounter}`;

        // Add to textPlaceholders with default position
        textPlaceholders[fieldId] = {
            x: 0.5,
            y: 0.5 + (customTextCounter * 0.05),
            fontSize: 24,
            label: `Custom Text ${customTextCounter}`,
            varName: `{{CUSTOM${customTextCounter}}}`,
            dragging: false
        };

        // Create form group
        const formGroup = document.createElement('div');
        formGroup.className = 'form-group';
        formGroup.id = `${fieldId}-group`;
        formGroup.innerHTML = `
        <label for="${fieldId}" style="display:flex; align-items:center; gap:6px; justify-content:space-between;">
            <span style="display:flex; align-items:center; gap:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="4 7 4 4 20 4 20 7"></polyline>
                    <line x1="9" y1="20" x2="15" y2="20"></line>
                    <line x1="12" y1="4" x2="12" y2="20"></line>
                </svg>
                Custom Text ${customTextCounter}
            </span>
            <button type="button" class="remove-text-btn" data-field="${fieldId}" title="Remove" style="background:#dc3545; color:white; border:none; border-radius:4px; width:24px; height:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </label>
        <input type="text" id="${fieldId}" placeholder="Enter custom text">
    `;

        customTextsContainer.appendChild(formGroup);
        customTextsContainer.style.display = 'block';
        customTextFields.push(fieldId);

        // Add input listener for real-time update
        document.getElementById(fieldId).addEventListener('input', renderCertificate);

        // Add remove button listener
        formGroup.querySelector('.remove-text-btn').addEventListener('click', (e) => {
            const fieldToRemove = e.currentTarget.dataset.field;
            removeCustomTextField(fieldToRemove);
        });

        renderCertificate();
        saveConfig();

        showModal(`Custom Text ${customTextCounter} added! You can now drag and position it on the canvas.`, 'Text Added', 'success');
    });
}

// Remove custom text field
const removeCustomTextField = (fieldId) => {
    // Remove from DOM
    const formGroup = document.getElementById(`${fieldId}-group`);
    if (formGroup) formGroup.remove();

    // Remove from textPlaceholders
    delete textPlaceholders[fieldId];

    // Remove from customTextFields array
    customTextFields = customTextFields.filter(id => id !== fieldId);

    // Hide container if no custom fields
    if (customTextFields.length === 0) {
        customTextsContainer.style.display = 'none';
    }

    // Deselect if this was selected
    if (selectedPlaceholder === fieldId) {
        selectedPlaceholder = null;
    }

    renderCertificate();
    saveConfig();
};

// Top Navigation Bar - User Menu (only on index page)
const userInfo = document.getElementById('userInfo');
const dropdownMenu = document.getElementById('dropdownMenu');
const userName = document.getElementById('userName');
const logoutBtn = document.getElementById('logoutBtn');
const accountDetailsBtn = document.getElementById('accountDetails');

if (userInfo && dropdownMenu && userName && logoutBtn && accountDetailsBtn) {
    // Display logged-in user's name
    const currentUser = sessionStorage.getItem('userName') || sessionStorage.getItem('userEmail');
    if (currentUser) {
        userName.textContent = currentUser;
    }

    // Toggle dropdown menu
    userInfo.addEventListener('click', (e) => {
        e.stopPropagation();
        userInfo.classList.toggle('active');
        dropdownMenu.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
        userInfo.classList.remove('active');
        dropdownMenu.classList.remove('active');
    });

    // Prevent dropdown from closing when clicking inside
    dropdownMenu.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Account Details
    accountDetailsBtn.addEventListener('click', () => {
        // Populate fields from window.userData (embedded by PHP)
        document.getElementById('accountName').value = window.userData.name || '';
        document.getElementById('accountEmail').value = window.userData.email || '';
        document.getElementById('accountPhone').value = window.userData.phone || '';
        document.getElementById('accountRegNo').value = window.userData.regno || '';
        
        // Show/hide registration number field based on whether it exists
        const regNoGroup = document.getElementById('accountRegNoGroup');
        if (window.userData.regno) {
            regNoGroup.style.display = 'block';
        } else {
            regNoGroup.style.display = 'none';
        }
        
        // Clear password fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('passwordMessage').style.display = 'none';
        
        // Show modal (centered)
        var _accountModal = document.getElementById('accountModal');
        var _acctContent = _accountModal.querySelector('.modal-content');
        if (_acctContent) {
            _acctContent.style.position = 'absolute';
            var left = Math.max((window.innerWidth - _acctContent.offsetWidth) / 2, 20);
            var top = Math.max((window.innerHeight - _acctContent.offsetHeight) / 2, 20);
            _acctContent.style.left = left + 'px';
            _acctContent.style.top = top + 'px';
        }
        _accountModal.style.display = 'flex';
        
        // Close dropdown
        dropdownMenu.classList.remove('active');
        userInfo.classList.remove('active');
    });

    // Logout
    logoutBtn.addEventListener('click', async () => {
        const confirmed = await showModal('Are you sure you want to logout?', 'Confirm Logout', 'warning', true);
        if (confirmed) {
            // Call logout API
            try {
                await fetch('actions/logout.php', { method: 'POST' });
            } catch (error) {
                console.error('Logout error:', error);
            }

            // Clear session storage
            sessionStorage.removeItem('isAuthenticated');
            sessionStorage.removeItem('userId');
            sessionStorage.removeItem('userName');
            sessionStorage.removeItem('userType');
            sessionStorage.removeItem('userEmail');

            showModal('Logged out successfully!', 'Logout', 'success').then(() => {
                window.location.href = 'login.php';
            });
        }
    });

    // Account Modal handlers
    const accountModal = document.getElementById('accountModal');
    const accountClose = document.getElementById('accountClose');
    const accountCancelBtn = document.getElementById('accountCancelBtn');
    const changePasswordBtn = document.getElementById('changePasswordBtn');

    // Close account modal
    accountClose.addEventListener('click', () => {
        accountModal.style.display = 'none';
    });

    accountCancelBtn.addEventListener('click', () => {
        accountModal.style.display = 'none';
    });

    // Close when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === accountModal) {
            accountModal.style.display = 'none';
        }
    });

    // Change password handler
    changePasswordBtn.addEventListener('click', async () => {
        const currentPassword = document.getElementById('currentPassword').value.trim();
        const newPassword = document.getElementById('newPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();
        const passwordMessage = document.getElementById('passwordMessage');

        // Reset message
        passwordMessage.style.display = 'none';

        // Validation
        if (!currentPassword) {
            passwordMessage.textContent = 'Please enter your current password';
            passwordMessage.style.background = '#fee';
            passwordMessage.style.color = '#c00';
            passwordMessage.style.display = 'block';
            return;
        }

        if (!newPassword) {
            passwordMessage.textContent = 'Please enter a new password';
            passwordMessage.style.background = '#fee';
            passwordMessage.style.color = '#c00';
            passwordMessage.style.display = 'block';
            return;
        }

        if (newPassword.length < 6) {
            passwordMessage.textContent = 'New password must be at least 6 characters';
            passwordMessage.style.background = '#fee';
            passwordMessage.style.color = '#c00';
            passwordMessage.style.display = 'block';
            return;
        }

        if (newPassword !== confirmPassword) {
            passwordMessage.textContent = 'New passwords do not match';
            passwordMessage.style.background = '#fee';
            passwordMessage.style.color = '#c00';
            passwordMessage.style.display = 'block';
            return;
        }

        // Disable button
        changePasswordBtn.disabled = true;
        changePasswordBtn.textContent = 'Changing...';

        try {
            const response = await fetch('actions/update_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            });

            const data = await response.json();

            if (data.success) {
                passwordMessage.textContent = 'Password changed successfully!';
                passwordMessage.style.background = '#efe';
                passwordMessage.style.color = '#070';
                passwordMessage.style.display = 'block';

                // Clear fields
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';

                // Close modal after 2 seconds
                setTimeout(() => {
                    accountModal.style.display = 'none';
                }, 2000);
            } else {
                passwordMessage.textContent = data.message || 'Failed to change password';
                passwordMessage.style.background = '#fee';
                passwordMessage.style.color = '#c00';
                passwordMessage.style.display = 'block';
            }
        } catch (error) {
            console.error('Password change error:', error);
            passwordMessage.textContent = 'An error occurred. Please try again.';
            passwordMessage.style.background = '#fee';
            passwordMessage.style.color = '#c00';
            passwordMessage.style.display = 'block';
        } finally {
            // Re-enable button
            changePasswordBtn.disabled = false;
            changePasswordBtn.textContent = 'Change Password';
        }
    });
}

// Initialize main app only if on index page
if (canvas && ctx) {
    console.log('🎨 Canvas found, initializing app...');
    console.log('📏 Initial canvas size:', canvas.width, 'x', canvas.height);
    updateZoom(); // Apply zoom transform FIRST before anything else

    // Load config first, then template
    (async () => {
        await loadConfig(); // Wait for config to load
        console.log('✅ Config loaded, now loading template');
        console.log('📋 textPlaceholders ready:', !!textPlaceholders.certNo);
        loadTemplate(); // Load template to set canvas dimensions (render happens in template.onload)
    })();
} else {
    console.log('❌ Canvas or ctx not found - canvas:', !!canvas, 'ctx:', !!ctx);
}

// ============================================
// LOGIN PAGE FUNCTIONALITY
// ============================================

// Check if we're on the login page
if (window.location.pathname.includes('login.php')) {
    // Check if already logged in
    (function () {
        const isLoggedIn = sessionStorage.getItem('isAuthenticated');
        if (isLoggedIn === 'true') {
            window.location.href = 'index.php';
        }
    })();

    // Simple client-side mock login handler
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const user = document.getElementById('username').value.trim();
            const pass = document.getElementById('password').value;

            if (!user || !pass) {
                await showModal('Please enter both username and password.', 'Login Required', 'warning');
                return;
            }

            // Mock success: set authentication flag
            sessionStorage.setItem('isAuthenticated', 'true');
            sessionStorage.setItem('currentUser', user);
            sessionStorage.setItem('loginTime', new Date().toISOString());

            // Store username if remember checked
            if (document.getElementById('remember').checked) {
                localStorage.setItem('mockUser', user);
            } else {
                localStorage.removeItem('mockUser');
            }

            // Redirect to index (authenticated)
            await showModal('Signed in successfully! Redirecting to the app...', 'Welcome', 'success');
            window.location.href = 'index.php';
        });
    }

    // Prefill username if remembered
    (function () {
        const saved = localStorage.getItem('mockUser');
        if (saved) {
            const usernameInput = document.getElementById('username');
            if (usernameInput) usernameInput.value = saved;
        }
    })();
}

// ============================================
// INDEX PAGE AUTH CHECK
// ============================================

// Check authentication before loading the main app
if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/')) {
    (function () {
        const isLoggedIn = sessionStorage.getItem('isAuthenticated');
        const userId = sessionStorage.getItem('userId');

        if (isLoggedIn !== 'true' || !userId) {
            if (typeof showModal === 'function') {
                showModal('Please log in to access the Certificate Generator.', 'Authentication Required', 'warning').then(() => {
                    window.location.href = 'login.php';
                });
            } else {
                window.location.href = 'login.php';
            }
        }
    })();
}

// ============================================
// Text Editor Modal
// ============================================
let currentEditingPlaceholder = null;
let textEditorOriginalValues = {};

// Get modal elements
const textEditorModal = document.getElementById('textEditorModal');
const textEditorModalContent = document.getElementById('textEditorModalContent');
const textEditorModalHeader = document.getElementById('textEditorModalHeader');
const textEditorClose = document.getElementById('textEditorClose');
const textEditorTitle = document.getElementById('textEditorTitle');
const textEditorPreview = document.getElementById('textEditorPreview');
const textEditorFontFamily = document.getElementById('textEditorFontFamily');
const textEditorFontSize = document.getElementById('textEditorFontSize');
const textEditorFontSizeValue = document.getElementById('textEditorFontSizeValue');
const textEditorFontColor = document.getElementById('textEditorFontColor');
const textEditorFontColorHex = document.getElementById('textEditorFontColorHex');
const textEditorLetterSpacing = document.getElementById('textEditorLetterSpacing');
const textEditorLetterSpacingValue = document.getElementById('textEditorLetterSpacingValue');
const textEditorBold = document.getElementById('textEditorBold');
const textEditorItalic = document.getElementById('textEditorItalic');
const textEditorUnderline = document.getElementById('textEditorUnderline');
const textEditorSave = document.getElementById('textEditorSave');
const textEditorCancel = document.getElementById('textEditorCancel');

// Make modal draggable
let isDraggingModal = false;
let modalOffsetX = 0;
let modalOffsetY = 0;

if (textEditorModalHeader) {
    textEditorModalHeader.addEventListener('mousedown', (e) => {
        isDraggingModal = true;
        modalOffsetX = e.clientX - textEditorModalContent.offsetLeft;
        modalOffsetY = e.clientY - textEditorModalContent.offsetTop;
        textEditorModalHeader.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', (e) => {
        if (isDraggingModal) {
            const newX = e.clientX - modalOffsetX;
            const newY = e.clientY - modalOffsetY;
            textEditorModalContent.style.left = newX + 'px';
            textEditorModalContent.style.top = newY + 'px';
            textEditorModalContent.style.position = 'fixed';
            textEditorModalContent.style.margin = '0';
        }
    });

    document.addEventListener('mouseup', () => {
        if (isDraggingModal) {
            isDraggingModal = false;
            textEditorModalHeader.style.cursor = 'move';
        }
    });
}

// Update preview as values change
function updateTextEditorPreview() {
    if (!textEditorPreview) return;

    const fontFamily = textEditorFontFamily.value;
    const fontSize = textEditorFontSize.value + 'px';
    const color = textEditorFontColor.value;
    const letterSpacing = textEditorLetterSpacing.value + 'px';
    const bold = textEditorBold.checked ? 'bold' : 'normal';
    const italic = textEditorItalic.checked ? 'italic' : 'normal';
    const underline = textEditorUnderline.checked ? 'underline' : 'none';

    textEditorPreview.style.fontFamily = fontFamily;
    textEditorPreview.style.fontSize = fontSize;
    textEditorPreview.style.color = color;
    textEditorPreview.style.letterSpacing = letterSpacing;
    textEditorPreview.style.fontWeight = bold;
    textEditorPreview.style.fontStyle = italic;
    textEditorPreview.style.textDecoration = underline;

    // Update value displays
    textEditorFontSizeValue.textContent = fontSize;
    textEditorLetterSpacingValue.textContent = letterSpacing;
    textEditorFontColorHex.value = color;
}

// Event listeners for live preview
if (textEditorFontFamily) textEditorFontFamily.addEventListener('change', updateTextEditorPreview);
if (textEditorFontSize) textEditorFontSize.addEventListener('input', updateTextEditorPreview);
if (textEditorFontColor) {
    textEditorFontColor.addEventListener('input', updateTextEditorPreview);
    textEditorFontColorHex.addEventListener('input', (e) => {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            textEditorFontColor.value = e.target.value;
            updateTextEditorPreview();
        }
    });
}
if (textEditorLetterSpacing) textEditorLetterSpacing.addEventListener('input', updateTextEditorPreview);
if (textEditorBold) textEditorBold.addEventListener('change', updateTextEditorPreview);
if (textEditorItalic) textEditorItalic.addEventListener('change', updateTextEditorPreview);
if (textEditorUnderline) textEditorUnderline.addEventListener('change', updateTextEditorPreview);

// Open text editor modal
function openTextEditorModal(placeholderKey) {
    const placeholder = textPlaceholders[placeholderKey];
    if (!placeholder || !placeholder.fontSize) return; // Only text placeholders

    currentEditingPlaceholder = placeholderKey;

    // Store original values for cancel
    textEditorOriginalValues = {
        fontFamily: placeholder.fontFamily || 'Arial',
        fontSize: placeholder.fontSize || 60,
        color: placeholder.color || '#000000',
        letterSpacing: placeholder.letterSpacing || 0,
        bold: placeholder.bold || false,
        italic: placeholder.italic || false,
        underline: placeholder.underline || false
    };

    // Set modal title
    textEditorTitle.textContent = `Edit ${placeholder.label || placeholderKey}`;

    // Set preview text
    textEditorPreview.textContent = getPlaceholderText(placeholderKey) || placeholder.label || 'Sample Text';

    // Load values into form
    textEditorFontFamily.value = textEditorOriginalValues.fontFamily;
    textEditorFontSize.value = textEditorOriginalValues.fontSize;
    textEditorFontColor.value = textEditorOriginalValues.color;
    textEditorLetterSpacing.value = textEditorOriginalValues.letterSpacing;
    textEditorBold.checked = textEditorOriginalValues.bold;
    textEditorItalic.checked = textEditorOriginalValues.italic;
    textEditorUnderline.checked = textEditorOriginalValues.underline;

    // Update preview
    updateTextEditorPreview();

    // Reset modal position
    textEditorModalContent.style.position = '';
    textEditorModalContent.style.left = '';
    textEditorModalContent.style.top = '';
    textEditorModalContent.style.margin = '';

    // Show modal
    textEditorModal.style.display = 'flex';
}

// Close modal
function closeTextEditorModal() {
    textEditorModal.style.display = 'none';
    currentEditingPlaceholder = null;
    textEditorOriginalValues = {};
}

// Save changes
if (textEditorSave) {
    textEditorSave.addEventListener('click', () => {
        if (!currentEditingPlaceholder) return;

        const placeholder = textPlaceholders[currentEditingPlaceholder];

        // Apply changes to placeholder
        placeholder.fontFamily = textEditorFontFamily.value;
        placeholder.fontSize = parseInt(textEditorFontSize.value);
        placeholder.color = textEditorFontColor.value;
        placeholder.letterSpacing = parseFloat(textEditorLetterSpacing.value);
        placeholder.bold = textEditorBold.checked;
        placeholder.italic = textEditorItalic.checked;
        placeholder.underline = textEditorUnderline.checked;

        // Update inline font size input if it exists
        const fontSizeInput = document.getElementById(currentEditingPlaceholder + 'FontSize');
        if (fontSizeInput) {
            fontSizeInput.value = placeholder.fontSize;
        }

        // Save to config
        saveConfig();

        // Re-render canvas
        renderCertificate();

        // Close modal
        closeTextEditorModal();

        console.log(`✅ Saved text properties for ${currentEditingPlaceholder}`);
    });
}

// Cancel changes
if (textEditorCancel) {
    textEditorCancel.addEventListener('click', () => {
        closeTextEditorModal();
    });
}

// Close button
if (textEditorClose) {
    textEditorClose.addEventListener('click', () => {
        closeTextEditorModal();
    });
}

// Close on background click
if (textEditorModal) {
    textEditorModal.addEventListener('click', (e) => {
        if (e.target === textEditorModal) {
            closeTextEditorModal();
        }
    });
}
