# Certificate Generator - PHP/MySQL System

A complete web-based certificate generator with user management, database integration, and admin panel.

## 🚀 Quick Start

1. **Start XAMPP** - Start Apache and MySQL
2. **Import Database** - Import `db/database_schema.sql` into MySQL
3. **Test Setup** - Visit `http://localhost:3000/test_database.php`
4. **Start Using** - Go to `http://localhost:3000/public/login.php` (or set DocumentRoot to `public/` then visit `/login.php`)

## 🔐 Default Credentials

**Admin Login**: `public/admin/login.php`
- Username: `Admin@MCC`
- Password: `Admin123`

**User Login**: `public/login.php`
- Create account at: `public/register.php`

## Features

- **Real-time Preview**: See changes instantly on the canvas as you type
- **Excel Import**: Upload Excel files to auto-populate certificate fields
- **Batch PDF Generation**: Generate all certificates in a single multi-page PDF
- **Persistent Configuration**: Placeholder positions saved in `config.json`
- **Drag & Drop Positioning**: Move and resize text/image placeholders on canvas
- **Image Signatures**: Upload signature images for personalization
- **Zoom Controls**: Zoom in/out on canvas for precise positioning
- **Calibration Grid**: Toggle grid overlay for alignment
- **Responsive Layout**: 25% sidebar for controls, 75% canvas for preview
- **Minimal UI**: Clean, modern interface with SVG icons

## Variables Mapping

The template supports the following variables:

- `{{VAR1}}` - Name
- `{{VAR2}}` - Certificate Number
- `{{VAR3}}` - Certified For
- `{{VAR4}}` - Signature 1 Image
- `{{VAR5}}` - Signature 2 Image
- `{{VAR6}}` - Signature 3 Image
- `{{VAR7}}` - From Date
- `{{VAR8}}` - To Date

## Excel File Format

Create an Excel file (.xlsx or .xls) with the following columns:

| Certificate No | Name | Certified For | From Date | To Date |
|----------------|------|---------------|-----------|---------|
| MCC-001 | John Doe | Python Developer | 01/06/2024 | 01/07/2024 |

## Usage

### Single Certificate Generation:
1. Open `index.html` in a web browser
2. Fill in the form fields manually
3. Upload signature images (optional)
4. Click "Generate PDF" to download the certificate

### Batch Certificate Generation:
1. Upload an Excel file with multiple certificate records
2. Click "Generate All PDFs" to create a single multi-page PDF with all certificates
3. The PDF will contain one page per certificate

### Adjusting Placeholder Positions:
1. Click on any text or signature placeholder on the canvas to select it
2. **Drag** to move the placeholder
3. **Drag corners** to resize (images) or use **+/-** keys (text)
4. Use **Arrow keys** for fine adjustments
5. Click **"Save to Config"** button to save your changes
6. Download the generated `config.json` file
7. Replace the existing `config.json` in your project folder
8. Refresh the page - your settings are now permanent!

## Files

- `index.html` - Main application structure
- `styles.css` - Styling and layout
- `script.js` - Certificate generation logic
- `config.json` - **Placeholder positions and sizes configuration**
- `MainPlaceholderCertificate.jpg` - Certificate template image

## Dependencies

- jsPDF - PDF generation
- SheetJS (xlsx) - Excel file parsing

CDN links are included in the HTML file.

## Configuration File (config.json)

The `config.json` file stores all placeholder positions, sizes, and properties. This ensures your layout settings persist across page refreshes.

### Structure:
```json
{
  "textPlaceholders": {
    "certNo": {
      "x": 0.849,        // Horizontal position (0-1, percentage of canvas width)
      "y": 0.121,        // Vertical position (0-1, percentage of canvas height)
      "fontSize": 25,    // Font size in pixels
      "label": "Certificate No",
      "varName": "{{VAR2}}",
      "dragging": false
    },
    "sig1": {
      "x": 0.430,
      "y": 0.800,
      "width": 170,      // Image width in pixels
      "height": 70,      // Image height in pixels
      "label": "Signature 1",
      "varName": "{{VAR4}}",
      "dragging": false
    }
  }
}
```

### How It Works:
1. **On page load**: `script.js` automatically loads settings from `config.json`
2. **When you adjust placeholders**: Changes are made in memory
3. **Click "Save to Config"**: Downloads an updated `config.json` with your changes
4. **Replace the file**: Copy the downloaded file to your project folder
5. **Refresh**: Your new settings are loaded automatically

This system ensures your carefully positioned placeholders are never lost!

## Browser Compatibility

Works in all modern browsers (Chrome, Firefox, Safari, Edge).
