# TODO: Student Access Role Implementation

## Phase 1: Database Updates
- [ ] 1.1 Update config/init.sql - Add role column to users table (teacher/student)
- [ ] 1.2 Add status column (pending/approved/rejected)
- [ ] 1.3 Add student_id column to link users to class_students

## Phase 2: Authentication Updates
- [ ] 2.1 Update login.php - Add role checking and approval status validation
- [ ] 2.2 Create student_login.php - Separate login page for students

## Phase 3: Navigation Updates
- [ ] 3.1 Update includes/nav.php - Role-based menu items

## Phase 4: Account Management (Teacher)
- [ ] 4.1 Update views/my_account.php - Show pending student approvals
- [ ] 4.2 Add approve/reject functionality for student accounts

## Phase 5: Student Dashboard
- [ ] 5.1 Create views/student_dashboard.php - Student view for their attendance and grades
- [ ] 5.2 Add access control - Only show own data

## Phase 6: Access Control
- [ ] 6.1 Add role-based access to existing pages
- [ ] 6.2 Protect student data from unauthorized access
