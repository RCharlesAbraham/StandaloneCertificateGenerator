# Certificate Generator

A web-based certificate generator that creates personalized certificates from template images with real-time preview and Excel data import.

## Features

- **Real-time Preview**: See changes instantly on the canvas as you type
- **Excel Import**: Upload Excel files to auto-populate certificate fields
- **PDF Export**: Generate professional PDF certificates
- **Image Signatures**: Upload signature images for personalization
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

1. Open `index.html` in a web browser
2. Fill in the form fields manually OR upload an Excel file
3. Upload signature images (optional)
4. Click "Generate PDF" to download the certificate

## Files

- `index.html` - Main application structure
- `styles.css` - Styling and layout
- `script.js` - Certificate generation logic
- `DemoPlaceholderCheck.jpg` - Certificate template image

## Dependencies

- jsPDF - PDF generation
- SheetJS (xlsx) - Excel file parsing

CDN links are included in the HTML file.

## Browser Compatibility

Works in all modern browsers (Chrome, Firefox, Safari, Edge).
