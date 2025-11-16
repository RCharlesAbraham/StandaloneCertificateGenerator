// Get DOM elements
const canvas = document.getElementById('certificateCanvas');
const ctx = canvas.getContext('2d');
const generateBtn = document.getElementById('generateBtn');

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

// Store uploaded signature images
let signatures = {
    sig1: null,
    sig2: null,
    sig3: null
};

// Template image
let templateImage = null;

// Store Excel data for batch processing
let excelData = [];
let currentRowIndex = 0;

// Text placeholders with draggable positions and sizes (will be loaded from localStorage or config.json)
let textPlaceholders = {};

// Load configuration from localStorage first, then fallback to config.json
const loadConfig = async () => {
    // Try to load from localStorage first
    const savedConfig = localStorage.getItem('certificateConfig');
    if (savedConfig) {
        try {
            const config = JSON.parse(savedConfig);
            textPlaceholders = config.textPlaceholders;
            console.log('✅ Configuration loaded from localStorage');
            renderCertificate();
            return;
        } catch (error) {
            console.error('❌ Error parsing localStorage config:', error);
        }
    }

    // Fallback to config.json if no localStorage data
    try {
        const response = await fetch('config.json');
        const config = await response.json();
        textPlaceholders = config.textPlaceholders;
        console.log('✅ Configuration loaded from config.json');
        // Save to localStorage for future use
        saveConfig();
        renderCertificate();
    } catch (error) {
        console.error('❌ Error loading config.json, using default values:', error);
        // Fallback to default values if config.json is not available
        textPlaceholders = {
            certNo: { x: 0.849, y: 0.121, fontSize: 25, label: 'Certificate No', varName: '{{VAR2}}', dragging: false },
            name: { x: 0.663, y: 0.538, fontSize: 60, label: 'Name', varName: '{{VAR1}}', dragging: false },
            certifiedFor: { x: 0.417, y: 0.622, fontSize: 30, label: 'Certified For', varName: '{{VAR3}}', dragging: false },
            fromDate: { x: 0.734, y: 0.658, fontSize: 28, label: 'From Date', varName: '{{VAR7}}', dragging: false },
            toDate: { x: 0.891, y: 0.658, fontSize: 28, label: 'To Date', varName: '{{VAR8}}', dragging: false },
            sig1: { x: 0.430, y: 0.800, width: 170, height: 70, label: 'Signature 1', varName: '{{VAR4}}', dragging: false },
            sig2: { x: 0.639, y: 0.804, width: 170, height: 70, label: 'Signature 2', varName: '{{VAR5}}', dragging: false },
            sig3: { x: 0.848, y: 0.803, width: 170, height: 70, label: 'Signature 3', varName: '{{VAR6}}', dragging: false }
        };
        saveConfig();
        renderCertificate();
    }
};

// Save configuration to localStorage automatically
const saveConfig = () => {
    const config = {
        textPlaceholders: textPlaceholders
    };

    localStorage.setItem('certificateConfig', JSON.stringify(config, null, 2));
    console.log('💾 Configuration auto-saved');

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
        indicator.innerHTML = '💾 Saved!';
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
let isDragging = false;
let isResizing = false;
let dragOffsetX = 0;
let dragOffsetY = 0;

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
    templateImage = new Image();
    templateImage.onload = () => {
        // Set canvas size to match template
        canvas.width = templateImage.width;
        canvas.height = templateImage.height;
        renderCertificate();
    };
    templateImage.onerror = () => {
        // If template not found, create a blank certificate
        canvas.width = 1122;
        canvas.height = 794;
        renderCertificate();
    };
    templateImage.src = 'MainPlaceholderCertificate.jpg';
};

// Render certificate on canvas
const renderCertificate = () => {
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
    ctx.font = `bold ${textPlaceholders.certNo.fontSize}px Arial`;
    const certNo = certNoInput.value || textPlaceholders.certNo.varName;
    const certNoX = canvas.width * textPlaceholders.certNo.x;
    const certNoY = canvas.height * textPlaceholders.certNo.y;
    ctx.fillText(certNo, certNoX, certNoY);

    // Draw Name
    ctx.font = `bold ${textPlaceholders.name.fontSize}px Georgia, serif`;
    const name = nameInput.value || textPlaceholders.name.varName;
    const nameX = canvas.width * textPlaceholders.name.x;
    const nameY = canvas.height * textPlaceholders.name.y;
    ctx.fillText(name, nameX, nameY);

    // Draw Certified For
    ctx.font = `bold ${textPlaceholders.certifiedFor.fontSize}px Arial`;
    const certifiedFor = certifiedForInput.value || textPlaceholders.certifiedFor.varName;
    const certForX = canvas.width * textPlaceholders.certifiedFor.x;
    const certForY = canvas.height * textPlaceholders.certifiedFor.y;
    ctx.fillText(certifiedFor, certForX, certForY);

    // Draw From Date
    ctx.font = `bold ${textPlaceholders.fromDate.fontSize}px Arial`;
    const fromDate = fromDateInput.value ? formatDate(fromDateInput.value) : textPlaceholders.fromDate.varName;
    const fromDateX = canvas.width * textPlaceholders.fromDate.x;
    const fromDateY = canvas.height * textPlaceholders.fromDate.y;
    ctx.fillText(fromDate, fromDateX, fromDateY);

    // Draw To Date
    ctx.font = `bold ${textPlaceholders.toDate.fontSize}px Arial`;
    const toDate = toDateInput.value ? formatDate(toDateInput.value) : textPlaceholders.toDate.varName;
    const toDateX = canvas.width * textPlaceholders.toDate.x;
    const toDateY = canvas.height * textPlaceholders.toDate.y;
    ctx.fillText(toDate, toDateX, toDateY);

    // Draw signatures
    const drawSignature = (key, sigImage) => {
        const placeholder = textPlaceholders[key];
        const sigX = canvas.width * placeholder.x;
        const sigY = canvas.height * placeholder.y;

        if (sigImage) {
            ctx.drawImage(sigImage, sigX - placeholder.width / 2, sigY - placeholder.height, placeholder.width, placeholder.height);
        } else {
            ctx.font = '10px Arial';
            ctx.fillText(placeholder.varName, sigX, sigY - 10);
        }
    };

    drawSignature('sig1', signatures.sig1);
    drawSignature('sig2', signatures.sig2);
    drawSignature('sig3', signatures.sig3);

    // Draw bounding boxes for selected placeholder
    if (selectedPlaceholder) {
        ctx.strokeStyle = '#67150a';
        ctx.lineWidth = 2;
        ctx.setLineDash([5, 5]);

        const placeholder = textPlaceholders[selectedPlaceholder];
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

        ctx.setLineDash([]);
    }
};

// Draw resize handle
const drawResizeHandle = (x, y) => {
    ctx.fillStyle = '#ffffff';
    ctx.strokeStyle = '#67150a';
    ctx.lineWidth = 2;
    ctx.fillRect(x - 5, y - 5, 10, 10);
    ctx.strokeRect(x - 5, y - 5, 10, 10);
};

// Get placeholder text
const getPlaceholderText = (key) => {
    switch (key) {
        case 'certNo': return certNoInput.value || textPlaceholders.certNo.varName;
        case 'name': return nameInput.value || textPlaceholders.name.varName;
        case 'certifiedFor': return certifiedForInput.value || textPlaceholders.certifiedFor.varName;
        case 'fromDate': return fromDateInput.value ? formatDate(fromDateInput.value) : textPlaceholders.fromDate.varName;
        case 'toDate': return toDateInput.value ? formatDate(toDateInput.value) : textPlaceholders.toDate.varName;
        default: return '';
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
excelFileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('excel-name').textContent = file.name;
        const reader = new FileReader();
        reader.onload = (event) => {
            const data = new Uint8Array(event.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

            // Store all data rows (skip header)
            excelData = jsonData.slice(1).filter(row => row.length > 0 && row[0]);

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

                alert(`Excel data loaded successfully!\nFound ${excelData.length} certificate(s) to generate.`);
            } else {
                alert('No data found in Excel file.');
            }
        };
        reader.readAsArrayBuffer(file);
    }
});

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
generateBtn.addEventListener('click', () => {
    const { pdf, filename } = generateSinglePDF(
        certNoInput.value,
        nameInput.value,
        certifiedForInput.value,
        fromDateInput.value,
        toDateInput.value
    );

    pdf.save(filename);
    alert('PDF generated successfully!');
});

// Template upload handler
document.getElementById('templateUpload').addEventListener('change', (e) => {
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
                alert(`✅ Template changed successfully!\nSize: ${img.width}x${img.height}px`);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Generate all PDFs from Excel data (individual PDFs in a ZIP)
document.getElementById('generateAllBtn').addEventListener('click', async () => {
    if (excelData.length === 0) {
        alert('No data loaded. Please upload an Excel file first.');
        return;
    }

    // Confirm before proceeding
    const confirm = window.confirm(`Generate ${excelData.length} individual certificates?\n\nThis will create separate PDF files compressed into a ZIP archive.`);
    if (!confirm) return;

    // Show progress
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    progressContainer.style.display = 'block';
    document.getElementById('generateAllBtn').disabled = true;

    // Create ZIP file
    const zip = new JSZip();
    const { jsPDF } = window.jspdf;

    for (let i = 0; i < excelData.length; i++) {
        const row = excelData[i];

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

        // Update progress
        const progress = ((i + 1) / excelData.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressText.textContent = `Generating... ${i + 1}/${excelData.length}`;

        // Small delay to ensure smooth rendering
        await new Promise(resolve => setTimeout(resolve, 50));
    }

    // Generate ZIP file
    progressText.textContent = `Creating ZIP archive...`;
    const zipBlob = await zip.generateAsync({
        type: 'blob',
        compression: 'DEFLATE',
        compressionOptions: { level: 6 }
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

    // Hide progress and re-enable button
    progressBar.style.width = '100%';
    progressText.textContent = `Complete! Generated ${excelData.length} certificates in ZIP.`;

    setTimeout(() => {
        progressContainer.style.display = 'none';
        progressBar.style.width = '0%';
        document.getElementById('generateAllBtn').disabled = false;
        alert(`Successfully generated ${excelData.length} certificates in a single PDF file!\n\nFile: ${filename}`);
    }, 2000);
});

// Add event listeners for real-time updates
certNoInput.addEventListener('input', renderCertificate);
nameInput.addEventListener('input', renderCertificate);
certifiedForInput.addEventListener('input', renderCertificate);
fromDateInput.addEventListener('change', renderCertificate);
toDateInput.addEventListener('change', renderCertificate);

// Setup signature uploads
handleSignatureUpload(sig1Input, 'sig1', document.getElementById('sig1-name'));
handleSignatureUpload(sig2Input, 'sig2', document.getElementById('sig2-name'));
handleSignatureUpload(sig3Input, 'sig3', document.getElementById('sig3-name'));

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

canvas.addEventListener('mousedown', (e) => {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;

    // First check if clicking on resize handle of selected placeholder
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

    // Check if clicking on any placeholder
    for (let key in textPlaceholders) {
        const placeholder = textPlaceholders[key];
        const px = canvas.width * placeholder.x;
        const py = canvas.height * placeholder.y;
        const dx = Math.abs(mouseX - px);
        const dy = Math.abs(mouseY - py);

        // Hit detection area
        const hitArea = 100; // pixels
        if (dx < hitArea && dy < hitArea) {
            selectedPlaceholder = key;
            isDragging = true;
            dragOffsetX = mouseX - px;
            dragOffsetY = mouseY - py;
            canvas.style.cursor = 'move';
            renderCertificate();
            console.log(`Selected: ${key}`);
            break;
        }
    }
});

canvas.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;

    if (isResizing && selectedPlaceholder && resizeHandle) {
        const placeholder = textPlaceholders[selectedPlaceholder];

        if (placeholder.fontSize) {
            // Resize text by changing font size based on vertical distance
            const centerY = canvas.height * placeholder.y;
            const distance = Math.abs(mouseY - centerY);
            const newFontSize = Math.max(8, Math.min(100, Math.round(distance / 3)));
            placeholder.fontSize = newFontSize;
            console.log(`${selectedPlaceholder} fontSize: ${newFontSize}`);
        } else {
            // Resize image based on handle position
            const centerX = canvas.width * placeholder.x;
            const centerY = canvas.height * placeholder.y;
            const newWidth = Math.abs(mouseX - centerX) * 2;
            const newHeight = Math.abs(mouseY - centerY) * 2;
            placeholder.width = Math.max(20, Math.min(500, Math.round(newWidth)));
            placeholder.height = Math.max(20, Math.min(300, Math.round(newHeight)));
            console.log(`${selectedPlaceholder} size: ${placeholder.width}x${placeholder.height}`);
        }
        renderCertificate();
    } else if (isDragging && selectedPlaceholder) {
        const newX = (mouseX - dragOffsetX) / canvas.width;
        const newY = (mouseY - dragOffsetY) / canvas.height;

        textPlaceholders[selectedPlaceholder].x = Math.max(0, Math.min(1, newX));
        textPlaceholders[selectedPlaceholder].y = Math.max(0, Math.min(1, newY));
        renderCertificate();

        // Log position for debugging
        console.log(`${selectedPlaceholder}: x=${textPlaceholders[selectedPlaceholder].x.toFixed(3)}, y=${textPlaceholders[selectedPlaceholder].y.toFixed(3)}`);
    } else {
        // Check if hovering over resize handles
        let overHandle = false;
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

        if (!overHandle) {
            // Check if hovering over any placeholder
            let hovering = false;
            for (let key in textPlaceholders) {
                const placeholder = textPlaceholders[key];
                const px = canvas.width * placeholder.x;
                const py = canvas.height * placeholder.y;
                const dx = Math.abs(mouseX - px);
                const dy = Math.abs(mouseY - py);

                const hitArea = 100;
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
    if (isDragging || isResizing) {
        isDragging = false;
        isResizing = false;
        resizeHandle = null;
        canvas.style.cursor = 'default';

        // Log final position/size
        if (selectedPlaceholder) {
            const p = textPlaceholders[selectedPlaceholder];
            if (p.fontSize) {
                console.log(`✅ Final ${selectedPlaceholder}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, fontSize=${p.fontSize}`);
            } else {
                console.log(`✅ Final ${selectedPlaceholder}: x=${p.x.toFixed(3)}, y=${p.y.toFixed(3)}, width=${p.width}, height=${p.height}`);
            }
        }

        // Auto-save configuration after any change
        saveConfig();
    }
}); canvas.addEventListener('dblclick', (e) => {
    const rect = canvas.getBoundingClientRect();
    const mouseX = (e.clientX - rect.left) / canvas.width;
    const mouseY = (e.clientY - rect.top) / canvas.height;

    // Double click to resize text
    if (selectedPlaceholder && textPlaceholders[selectedPlaceholder].fontSize) {
        const newSize = prompt(`Enter font size for ${textPlaceholders[selectedPlaceholder].label}:`, textPlaceholders[selectedPlaceholder].fontSize);
        if (newSize && !isNaN(newSize)) {
            textPlaceholders[selectedPlaceholder].fontSize = parseInt(newSize);
            renderCertificate();
            console.log(`${selectedPlaceholder} fontSize: ${newSize}`);
            saveConfig(); // Auto-save
        }
    } else if (selectedPlaceholder) {
        const newWidth = prompt(`Enter width for ${textPlaceholders[selectedPlaceholder].label}:`, textPlaceholders[selectedPlaceholder].width);
        const newHeight = prompt(`Enter height for ${textPlaceholders[selectedPlaceholder].label}:`, textPlaceholders[selectedPlaceholder].height);
        if (newWidth && !isNaN(newWidth) && newHeight && !isNaN(newHeight)) {
            textPlaceholders[selectedPlaceholder].width = parseInt(newWidth);
            textPlaceholders[selectedPlaceholder].height = parseInt(newHeight);
            renderCertificate();
            console.log(`${selectedPlaceholder} size: ${newWidth}x${newHeight}`);
            saveConfig(); // Auto-save
        }
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (!selectedPlaceholder) return;

    const step = e.shiftKey ? 0.001 : 0.005;
    const placeholder = textPlaceholders[selectedPlaceholder];
    let configChanged = false;

    switch (e.key) {
        case 'ArrowLeft':
            placeholder.x -= step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'ArrowRight':
            placeholder.x += step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'ArrowUp':
            placeholder.y -= step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'ArrowDown':
            placeholder.y += step;
            renderCertificate();
            configChanged = true;
            e.preventDefault();
            break;
        case 'Escape':
            selectedPlaceholder = null;
            renderCertificate();
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
document.getElementById('toggleGrid').addEventListener('click', () => {
    showGrid = !showGrid;
    renderCertificate();
    console.log(`Grid ${showGrid ? 'enabled' : 'disabled'}`);
});

document.getElementById('copyPositions').addEventListener('click', () => {
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

    alert('✅ Configuration saved!\n\nYour placeholder positions are automatically saved.\nCheck the console for current position values.');
});

// Zoom functionality
const updateZoom = () => {
    canvas.style.transform = `scale(${zoomLevel})`;
    // Display zoom: 50% actual = 100% displayed
    const displayZoom = Math.round((zoomLevel / 0.5) * 100);
    document.getElementById('zoomLevel').textContent = `${displayZoom}%`;
};

document.getElementById('zoomIn').addEventListener('click', () => {
    zoomLevel = Math.min(zoomLevel + 0.05, 1.5); // Max 300% displayed (1.5 actual)
    updateZoom();
});

document.getElementById('zoomOut').addEventListener('click', () => {
    zoomLevel = Math.max(zoomLevel - 0.05, 0.15); // Min 30% displayed (0.15 actual)
    updateZoom();
});

document.getElementById('zoomReset').addEventListener('click', () => {
    zoomLevel = 0.5; // Reset to 100% displayed (0.5 actual)
    updateZoom();
});

// Mouse wheel zoom
const canvasContainer = document.getElementById('canvasContainer');
canvasContainer.addEventListener('wheel', (e) => {
    if (e.ctrlKey || e.metaKey) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.05 : 0.05;
        zoomLevel = Math.max(0.15, Math.min(1.5, zoomLevel + delta));
        updateZoom();
    }
}, { passive: false });

// Keyboard zoom shortcuts
document.addEventListener('keydown', (e) => {
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

// Top Navigation Bar - User Menu
const userInfo = document.getElementById('userInfo');
const dropdownMenu = document.getElementById('dropdownMenu');
const userName = document.getElementById('userName');
const logoutBtn = document.getElementById('logoutBtn');
const accountDetailsBtn = document.getElementById('accountDetails');

// Display logged-in user's name
const currentUser = sessionStorage.getItem('currentUser');
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
    const user = sessionStorage.getItem('currentUser') || 'User';
    const loginTime = sessionStorage.getItem('loginTime');
    let message = `Account Details:\n\nUsername: ${user}`;
    
    if (loginTime) {
        const time = new Date(loginTime).toLocaleString();
        message += `\nLogged in: ${time}`;
    }
    
    alert(message);
    dropdownMenu.classList.remove('active');
    userInfo.classList.remove('active');
});

// Logout
logoutBtn.addEventListener('click', () => {
    if (confirm('Are you sure you want to logout?')) {
        sessionStorage.removeItem('isAuthenticated');
        sessionStorage.removeItem('currentUser');
        sessionStorage.removeItem('loginTime');
        alert('Logged out successfully!');
        window.location.href = 'login.html';
    }
});

// Initialize
loadConfig(); // Load configuration from config.json first
loadTemplate();
updateZoom(); // Initialize zoom display
