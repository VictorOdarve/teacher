# Student Attendance System

A modular PHP-based attendance management system for students.

## Setup Instructions

1. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Your database `teacherdb` should already exist
   - Import or run the SQL file: `config/init.sql`

2. **Configuration**
   - Database settings are in `config/database.php`
   - Default credentials: host=localhost, user=root, password=blank

3. **Access the System**
   - Open browser: http://localhost/maonani
   - Dashboard will appear

## Features

- **Student Management**: Add, edit, delete students
- **Mark Attendance**: Daily attendance marking (Present/Absent)
- **Reports**: View attendance statistics by date range
- **Dashboard**: Quick overview and statistics

## File Structure

```
maonani/
├── config/          # Database configuration
├── controllers/     # Business logic handlers
├── models/          # Database operations
├── views/           # UI pages
├── includes/        # Reusable components (header, footer, nav)
├── assets/          # CSS and JS files
└── index.php        # Dashboard
```

## Usage

1. Add students via "Students" menu
2. Mark daily attendance via "Mark Attendance"
3. View reports via "Reports" menu
