# Registration System Update - Stream & Level Selection

## Overview
The registration system has been updated to replace the simple Student/Staff toggle with a comprehensive stream and level selection system for students, specifically tailored for Madras Christian College's program structure.

## What Changed

### 1. **User Interface**
- ✅ **User Type Selection**: Radio buttons for Student/Staff (previously toggle buttons)
- ✅ **Stream Selection** (Students only): 
  - Aided
  - Self-Financed Stream (SFS)
- ✅ **Level Selection** (Students only):
  - UG (Undergraduate)
  - PG (Postgraduate)
- ✅ **Dynamic Department Dropdowns**: Department list automatically updates based on selected stream and level
- ✅ **Staff Flexibility**: Staff members still enter department as free text

### 2. **Database Changes**
New columns added to `users` table:
- `stream` ENUM('aided', 'sfs') - Student's stream type
- `level` ENUM('ug', 'pg') - Student's level (UG/PG)
- `program_id` VARCHAR(50) - Auto-generated unique program identifier

**Program ID Format**: `STREAM-LEVEL-YEAR-RANDOM`
- Example: `AIDED-UG-2024-A5B3`, `SFS-PG-2024-X7Y9`

### 3. **Department Lists**

#### Aided - UG (13 departments)
- English Language & Literature
- Tamil Literature
- History
- Political Science
- Economics
- Philosophy
- Commerce (General)
- Mathematics
- Statistics
- Physics
- Chemistry
- Plant Biology & Plant Biotechnology
- Zoology

#### Aided - PG (15 departments)
- English Language & Literature
- Tamil Literature
- History
- Political Science
- Public Administration
- Economics
- Philosophy
- Commerce (M.Com)
- MSW (Community Development / Medical & Psychiatry)
- Mathematics
- Statistics
- Physics
- Chemistry
- Plant Biology & Plant Biotechnology
- Zoology

#### Self-Financed Stream (SFS) - UG (18 departments)
- English Language & Literature
- Journalism
- History (Vocational – Archaeology & Museology)
- Social Work (BSW)
- Commerce (General)
- Commerce (Accounting & Finance)
- Commerce (Professional Accounting)
- Business Administration (BBA)
- Computer Applications (BCA)
- Geography, Tourism & Travel Management
- Hospitality & Tourism
- Mathematics
- Physics
- Microbiology
- Computer Science
- Visual Communication
- Physical Education, Health Education & Sports
- Psychology

#### Self-Financed Stream (SFS) - PG (7 departments)
- M.A. Communication
- MSW – Human Resource Management
- M.Com – Computer Oriented Business Applications
- M.Sc. Chemistry
- M.Sc. Applied Microbiology
- MCA – Computer Applications
- M.Sc. Data Science

## Setup Instructions

### For New Installations
1. Run the full database schema:
   ```bash
   mysql -u root -p < db/database_schema.sql
   ```

### For Existing Databases
1. Run the migration script to add new columns:
   ```bash
   mysql -u root -p certificate_generator < db/migration_add_stream_level.sql
   ```

2. Verify migration with test suite:
   ```
   http://localhost/test_registration.php
   ```

## Testing

### Manual Testing Checklist
1. ✅ Open `public/register.php`
2. ✅ Select "Student" user type
3. ✅ Select "Aided" stream
4. ✅ Select "UG" level
5. ✅ Verify department dropdown shows 13 Aided-UG departments
6. ✅ Change to "PG" level
7. ✅ Verify department dropdown updates to 15 Aided-PG departments
8. ✅ Change stream to "SFS"
9. ✅ Verify department dropdown updates to SFS-UG or SFS-PG departments
10. ✅ Select "Staff" user type
11. ✅ Verify department becomes text input (not dropdown)
12. ✅ Complete registration and check database

### Automated Test Suite
Open `test_registration.php` in your browser to run comprehensive tests:
- Database connection verification
- Table structure validation
- Department list accuracy
- Sample data visualization
- Frontend functionality checklist

## File Changes

### Modified Files
1. **db/database_schema.sql** - Added `stream`, `level`, `program_id` columns
2. **public/register.php** - Complete UI redesign with radio buttons and dynamic dropdowns
3. **public/actions/register_process.php** - Backend validation and program ID generation
4. **public/styles.css** - Added CSS for radio buttons and form styling

### New Files
1. **db/migration_add_stream_level.sql** - Migration script for existing databases
2. **test_registration.php** - Comprehensive test suite

## Features

### Program ID Generation
Every student registration automatically generates a unique program ID:
```php
// Format: STREAM-LEVEL-YEAR-RANDOM
// Example: AIDED-UG-2024-A5B3
$year = date('Y');
$random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
$program_id = strtoupper($stream) . '-' . strtoupper($level) . '-' . $year . '-' . $random;
```

### Dynamic Department Loading
JavaScript automatically updates department options based on stream+level selection:
```javascript
const departments = {
    aided: {
        ug: [...], // 13 departments
        pg: [...]  // 15 departments
    },
    sfs: {
        ug: [...], // 18 departments
        pg: [...]  // 7 departments
    }
};
```

### Validation
Backend validates:
- Stream must be 'aided' or 'sfs' (for students)
- Level must be 'ug' or 'pg' (for students)
- Department must be selected from valid list
- Registration number uniqueness
- Email uniqueness
- Password strength (min 6 characters)

### Activity Logging
Enhanced activity logs now include:
- Stream and level information
- Auto-generated program ID
- Department selection
```
"New student account created: John Doe | Stream: AIDED | Level: UG | Program ID: AIDED-UG-2024-A5B3 | Department: Computer Science"
```

## Database Query Examples

### Get all Aided UG students
```sql
SELECT * FROM users 
WHERE user_type = 'student' 
AND stream = 'aided' 
AND level = 'ug';
```

### Count students by stream and level
```sql
SELECT stream, level, COUNT(*) as total
FROM users 
WHERE user_type = 'student'
GROUP BY stream, level;
```

### Find student by program ID
```sql
SELECT * FROM users 
WHERE program_id = 'AIDED-UG-2024-A5B3';
```

## Browser Compatibility
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

## Notes
- Staff members do NOT have stream/level fields (NULL in database)
- Program ID is only generated for students
- Department dropdown is dynamically populated client-side
- All department lists are hardcoded in JavaScript for performance
- Radio button `accent-color` uses MCC brand color (#67150a)

## Troubleshooting

### Department dropdown not updating
- Check browser console for JavaScript errors
- Verify stream and level radio buttons are checked
- Ensure `public/script.js` is not interfering

### Database errors on registration
- Run `test_registration.php` to verify table structure
- Check if migration script was executed
- Verify `stream`, `level`, `program_id` columns exist

### Program ID not generated
- Check PHP version (requires 5.4+)
- Verify `$user_type === 'student'` condition
- Check backend logs in `activity_logs` table

## Support
For issues or questions, refer to:
- Main README: `readme/README.md`
- Setup Guide: `readme/SETUP_GUIDE.md`
- Backend Docs: `readme/BACKEND_README.md`
