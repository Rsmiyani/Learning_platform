-- Insert Test Certificate Data
-- This script will create a test certificate for testing the admin certificates page

-- Step 1: Check if you have users and courses
SELECT '=== Checking for trainee users ===' as info;
SELECT user_id, first_name, last_name, email FROM users WHERE role = 'trainee' LIMIT 5;

SELECT '=== Checking for courses ===' as info;
SELECT course_id, course_name, course_code FROM courses LIMIT 5;

-- Step 2: Insert test certificate
-- Replace @user_id and @course_id with actual values from above queries

-- Example: If you have user_id = 2 and course_id = 1, use:
INSERT INTO user_certificates 
(user_id, course_id, certificate_code, certificate_number, issued_date)
VALUES 
(2, 1, 'CERT-6738A2F1B4E5C-20251102000900', 'CERT-0002-0001', NOW());

-- You can insert multiple test certificates:
-- INSERT INTO user_certificates 
-- (user_id, course_id, certificate_code, certificate_number, issued_date)
-- VALUES 
-- (2, 1, 'CERT-TEST001', 'CERT-0002-0001', '2025-11-01'),
-- (3, 2, 'CERT-TEST002', 'CERT-0003-0002', '2025-11-02'),
-- (4, 3, 'CERT-TEST003', 'CERT-0004-0003', '2025-10-15');

-- Step 3: Verify insertion
SELECT '=== Verifying certificates ===' as info;
SELECT 
    uc.certificate_id,
    CONCAT(u.first_name, ' ', u.last_name) as student_name,
    c.course_name,
    uc.certificate_code,
    uc.certificate_number,
    uc.issued_date
FROM user_certificates uc
JOIN users u ON uc.user_id = u.user_id
JOIN courses c ON uc.course_id = c.course_id
ORDER BY uc.issued_date DESC;
