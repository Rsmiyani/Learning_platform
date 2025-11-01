# TrainAI Learning Platform - Complete Project Documentation

## 📚 **Project Overview**

**TrainAI** is a comprehensive AI-powered Learning Management System (LMS) designed for organizations to manage training programs, courses, and certifications. The platform supports three user roles: Admin, Trainer, and Trainee (Student).

**Version:** 1.0  
**Technology Stack:** PHP, MySQL, JavaScript, HTML5, CSS3  
**Architecture:** MVC Pattern with Session-based Authentication  

---

## 🎯 **Core Features**

### **1. User Management System**

#### **Three User Roles:**
- **Admin** - Full platform control
- **Trainer** - Course creation and management
- **Trainee** - Course enrollment and learning

#### **Authentication Features:**
- ✅ Secure login with password hashing (bcrypt)
- ✅ Registration with email validation
- ✅ "Remember Me" functionality with secure tokens
- ✅ Forgot Password with token-based reset
- ✅ Session management with timeout
- ✅ Role-based access control (RBAC)

#### **Algorithm Used:**
```
Password Hashing: bcrypt (password_hash with PASSWORD_BCRYPT)
- Cost factor: 10
- Salt: Automatically generated
- Hash length: 60 characters

Remember Me Token:
- Token generation: random_bytes(32) → bin2hex
- Token hashing: SHA-256
- Storage: Hashed in database
- Expiry: 30 days
- Security: HttpOnly cookies
```

---

### **2. Course Management System**

#### **For Trainers:**
- ✅ Create courses with details (name, code, category, duration)
- ✅ Add course modules with ordering
- ✅ Add lessons with video links and PDF documents
- ✅ Create exams with multiple-choice questions
- ✅ Set passing criteria (75% minimum)
- ✅ Track course enrollments
- ✅ View student progress

#### **Course Structure:**
```
Course
├── Modules (ordered)
│   └── Lessons (ordered)
│       ├── Video URL (YouTube, Vimeo, etc.)
│       └── PDF URL (Google Drive, Dropbox, etc.)
└── Exam
    └── Questions (multiple choice)
```

#### **Algorithm Used:**
```
Module Ordering:
- Auto-increment: MAX(module_order) + 1
- Ensures sequential ordering
- Prevents gaps in sequence

Lesson Ordering:
- Same as module ordering
- Per-module basis
- Independent sequences
```

---

### **3. Enrollment System**

#### **Features:**
- ✅ Browse all available courses
- ✅ Filter by category and search
- ✅ One-click enrollment
- ✅ Automatic enrollment tracking
- ✅ Progress percentage calculation
- ✅ Course completion status

#### **Algorithm Used:**
```
Progress Calculation:
progress_percentage = (completed_modules / total_modules) * 100

Enrollment Status:
- active: Currently enrolled, not completed
- completed: All modules done, exam passed

Automatic Updates:
- Progress updates on module completion
- Status changes on exam pass
```

---

### **4. Learning System**

#### **For Trainees:**
- ✅ View enrolled courses
- ✅ Access course modules and lessons
- ✅ Watch video content
- ✅ View PDF documents
- ✅ Track learning progress
- ✅ Take exams
- ✅ Receive certificates

#### **Video Tracking:**
- ✅ Tracks time spent watching videos
- ✅ Logs to study_logs table
- ✅ Updates weekly activity chart
- ✅ Minimum threshold: 30 seconds

#### **Algorithm Used:**
```
Video Watch Time Tracking:
1. Record start time on "Watch" click
2. Calculate duration after 5 minutes
3. Log on page close (sendBeacon)
4. Convert seconds to hours: duration / 3600
5. Store in study_logs table

Study Hours Calculation:
- Course completion: Add course duration_hours
- Video watching: Add actual watch time
- Aggregation: SUM by date for weekly chart
```

---

### **5. Examination System**

#### **Features:**
- ✅ Multiple-choice questions
- ✅ Randomized question order
- ✅ Timed exams (optional)
- ✅ Automatic grading
- ✅ Instant results
- ✅ Pass/Fail determination (75% threshold)
- ✅ Certificate issuance on pass

#### **Algorithm Used:**
```
Exam Grading Algorithm:
1. Count total questions
2. Count correct answers
3. Calculate: score = (correct / total) * 100
4. Determine: passed = (score >= 75)

Score Calculation:
score_percentage = (correct_answers / total_questions) × 100

Pass Criteria:
IF score_percentage >= 75 THEN
    status = "passed"
    issue_certificate()
    award_points()
ELSE
    status = "failed"
    allow_retake()
END IF
```

---

### **6. Certificate System**

#### **Features:**
- ✅ Automatic certificate generation on exam pass
- ✅ Unique certificate code and number
- ✅ PDF-ready certificate design
- ✅ Download and print functionality
- ✅ Certificate verification
- ✅ Admin can view/delete certificates

#### **Algorithm Used:**
```
Certificate Code Generation:
format: CERT-[UNIQUE_ID]-[TIMESTAMP]
unique_id = substr(md5(uniqid()), 0, 13)
timestamp = date('YmdHis')
Example: CERT-6738A2F1B4E5C-20251102001530

Certificate Number Generation:
format: CERT-[USER_ID_PADDED]-[COURSE_ID_PADDED]
user_id_padded = str_pad(user_id, 4, '0', STR_PAD_LEFT)
course_id_padded = str_pad(course_id, 4, '0', STR_PAD_LEFT)
Example: CERT-0002-0001 (user 2, course 1)

Uniqueness Guarantee:
- Combination of timestamp + random hash
- Database unique constraint on certificate_code
- Collision probability: < 0.0001%
```

---

### **7. Gamification System**

#### **Points & Levels:**
- ✅ Earn points for activities
- ✅ Level progression based on points
- ✅ Leaderboard ranking
- ✅ Achievement badges
- ✅ Progress tracking

#### **Point System:**
```
Activity Points:
- Course enrollment: 10 points
- Module completion: 20 points
- Exam pass: 100 points
- Certificate earned: 50 points
- Daily login: 5 points
```

#### **Algorithm Used:**
```
Level Calculation Algorithm:
level = FLOOR(SQRT(total_points / 100)) + 1

Examples:
- 0-99 points: Level 1
- 100-399 points: Level 2
- 400-899 points: Level 3
- 900-1599 points: Level 4
- 1600+ points: Level 5+

Mathematical Formula:
points_needed_for_level_n = (n - 1)² × 100

Level Update Trigger:
- After earning points
- Automatic recalculation
- Real-time update
```

---

### **8. Leaderboard System**

#### **Features:**
- ✅ Rank users by total points
- ✅ Handle ties in ranking
- ✅ Display top performers
- ✅ Show user's current rank
- ✅ Real-time updates

#### **Algorithm Used:**
```
Ranking Algorithm with Tie Handling:

SQL Query:
SELECT user_id, total_points,
       RANK() OVER (ORDER BY total_points DESC) as rank
FROM user_points
ORDER BY total_points DESC

Tie Handling:
- Users with same points get same rank
- Next rank skips (e.g., 1, 2, 2, 4)
- Dense ranking option available

Example:
User A: 1000 points → Rank 1
User B: 1000 points → Rank 1
User C: 950 points → Rank 3 (not 2)
```

---

### **9. Analytics & Reporting**

#### **Weekly Activity Chart:**
- ✅ Displays last 7 days of activity
- ✅ Shows hours studied per day
- ✅ Real-time data from study_logs
- ✅ Interactive Chart.js visualization

#### **Algorithm Used:**
```
Weekly Activity Data Aggregation:

1. Get last 7 days:
   FOR i = 6 TO 0 DO
       date = CURRENT_DATE - i days
       day_name = DAYNAME(date)
   END FOR

2. Fetch study hours:
   SELECT DATE(study_date), SUM(hours_studied)
   FROM study_logs
   WHERE study_date >= CURRENT_DATE - 7
   GROUP BY DATE(study_date)

3. Match dates:
   FOR each day in last_7_days DO
       hours = find_matching_data(day) OR 0
       chart_data.push(hours)
   END FOR

4. Render chart:
   Chart.js bar chart with dynamic data
```

---

### **10. Admin Dashboard**

#### **Full Platform Control:**
- ✅ View all users, courses, enrollments
- ✅ Manage user points (add/remove)
- ✅ Unenroll users from courses
- ✅ Delete certificates
- ✅ Activate/deactivate user accounts
- ✅ View platform statistics
- ✅ Generate reports (CSV, Excel, Print)

#### **Admin Capabilities:**
```
User Management:
- View all users with statistics
- Filter by role and status
- Modify user points
- Toggle user status
- Cannot modify other admins

Course Management:
- View all courses
- See enrollment statistics
- Access course content
- Monitor course performance

Enrollment Management:
- View all enrollments
- Track progress
- Unenroll users
- Filter by status

Certificate Management:
- View all certificates
- Delete certificates
- Verify authenticity
- Track issuance

Reports & Analytics:
- Platform overview
- Top courses
- Top students
- Trends and insights
- Export to CSV/Excel
```

---

### **11. Notification System**

#### **Features:**
- ✅ Real-time notifications
- ✅ Module added alerts
- ✅ Exam results notifications
- ✅ Certificate earned alerts
- ✅ Mark as read functionality

#### **Algorithm Used:**
```
Notification Delivery:

1. Event Trigger:
   - Module added
   - Exam completed
   - Certificate issued

2. Recipient Selection:
   SELECT user_id FROM course_enrollments
   WHERE course_id = ?

3. Notification Creation:
   INSERT INTO notifications
   (user_id, type, title, message, created_at)
   VALUES (?, ?, ?, ?, NOW())

4. Display:
   - Unread count badge
   - Dropdown list
   - Mark as read on click
```

---

### **12. Search & Filter System**

#### **Features:**
- ✅ Search courses by name, category
- ✅ Filter by category, difficulty
- ✅ Sort by rating, newest, popular
- ✅ Real-time search results

#### **Algorithm Used:**
```
Search Algorithm:

1. Input Processing:
   query = TRIM(UPPER(user_input))

2. Database Query:
   SELECT * FROM courses
   WHERE UPPER(course_name) LIKE '%query%'
      OR UPPER(description) LIKE '%query%'
      OR UPPER(category) LIKE '%query%'

3. Filtering:
   IF category_filter THEN
       AND category = category_filter
   END IF

4. Sorting:
   ORDER BY
       CASE sort_by
           WHEN 'rating' THEN rating DESC
           WHEN 'new' THEN created_at DESC
           WHEN 'popular' THEN total_ratings DESC
       END

5. Result Display:
   - Paginated results
   - Highlight matching terms
   - Show relevant metadata
```

---

## 🗄️ **Database Schema**

### **Core Tables:**

#### **1. users**
```sql
- user_id (PK)
- first_name, last_name
- email (UNIQUE)
- password (hashed)
- role (admin/trainer/trainee)
- status (active/inactive)
- created_at
```

#### **2. courses**
```sql
- course_id (PK)
- instructor_id (FK → users)
- course_name, course_code
- category, difficulty
- duration_hours
- rating, total_ratings
- created_at
```

#### **3. course_modules**
```sql
- module_id (PK)
- course_id (FK → courses)
- module_title, module_description
- module_order
```

#### **4. module_lessons**
```sql
- lesson_id (PK)
- module_id (FK → course_modules)
- lesson_title, lesson_description
- video_url, pdf_url
- content_type (video/pdf/both)
- lesson_order
```

#### **5. course_enrollments**
```sql
- enrollment_id (PK)
- user_id (FK → users)
- course_id (FK → courses)
- status (active/completed)
- progress_percentage
- enrolled_at
```

#### **6. exams**
```sql
- exam_id (PK)
- course_id (FK → courses)
- exam_title
- passing_score (default: 75)
- time_limit
```

#### **7. exam_questions**
```sql
- question_id (PK)
- exam_id (FK → exams)
- question_text
- option_a, option_b, option_c, option_d
- correct_answer
- question_order
```

#### **8. exam_results**
```sql
- result_id (PK)
- user_id (FK → users)
- exam_id (FK → exams)
- score_percentage
- passed (boolean)
- completed_at
```

#### **9. user_certificates**
```sql
- cert_id (PK)
- user_id (FK → users)
- course_id (FK → courses)
- certificate_code (UNIQUE)
- certificate_number
- issued_date
```

#### **10. user_points**
```sql
- point_id (PK)
- user_id (FK → users, UNIQUE)
- total_points
- level
- last_updated
```

#### **11. study_logs**
```sql
- log_id (PK)
- user_id (FK → users)
- study_date (DATE)
- hours_studied
- courses_studied
- activities
```

#### **12. notifications**
```sql
- notification_id (PK)
- user_id (FK → users)
- notification_type
- title, message
- is_read (boolean)
- created_at
```

#### **13. remember_tokens**
```sql
- token_id (PK)
- user_id (FK → users)
- token_hash (SHA-256)
- expires_at
- created_at
```

#### **14. video_watch_logs**
```sql
- log_id (PK)
- user_id (FK → users)
- lesson_id (FK → module_lessons)
- course_id (FK → courses)
- watch_duration (hours)
- watch_date
```

---

## 🔐 **Security Features**

### **1. Authentication Security:**
```
✅ Password hashing with bcrypt
✅ Salt automatically generated
✅ Cost factor: 10 (2^10 iterations)
✅ Session-based authentication
✅ Session timeout after inactivity
✅ CSRF protection (session tokens)
```

### **2. Authorization:**
```
✅ Role-based access control (RBAC)
✅ Page-level permission checks
✅ Function-level permission checks
✅ Admin-only routes protected
✅ Trainer-only routes protected
```

### **3. Input Validation:**
```
✅ SQL injection prevention (prepared statements)
✅ XSS protection (htmlspecialchars)
✅ Email validation
✅ URL validation
✅ File type validation
```

### **4. Data Protection:**
```
✅ Sensitive data encrypted
✅ Passwords never stored in plain text
✅ Remember tokens hashed (SHA-256)
✅ HttpOnly cookies
✅ Secure session handling
```

---

## 📊 **Algorithms Summary**

### **1. Password Security:**
- **Algorithm:** bcrypt
- **Cost:** 10
- **Salt:** Auto-generated
- **Hash Length:** 60 characters

### **2. Token Generation:**
- **Method:** random_bytes(32) + bin2hex
- **Hashing:** SHA-256
- **Length:** 64 characters
- **Expiry:** 30 days

### **3. Level Calculation:**
- **Formula:** level = ⌊√(points/100)⌋ + 1
- **Type:** Square root progression
- **Scaling:** Exponential difficulty

### **4. Ranking:**
- **Method:** SQL RANK() function
- **Tie Handling:** Same rank for same points
- **Order:** Descending by points

### **5. Progress Tracking:**
- **Formula:** (completed/total) × 100
- **Update:** Real-time on completion
- **Display:** Percentage with progress bar

### **6. Exam Grading:**
- **Formula:** (correct/total) × 100
- **Threshold:** 75% to pass
- **Instant:** Immediate results

### **7. Certificate Generation:**
- **Code:** CERT-[HASH]-[TIMESTAMP]
- **Number:** CERT-[USER]-[COURSE]
- **Uniqueness:** Hash + timestamp

### **8. Video Tracking:**
- **Method:** JavaScript timer
- **Conversion:** seconds / 3600 = hours
- **Storage:** Aggregated by date

### **9. Search:**
- **Type:** Full-text search
- **Method:** LIKE with wildcards
- **Optimization:** Indexed columns

### **10. Sorting:**
- **Methods:** Rating, Date, Popularity
- **Order:** DESC (highest first)
- **Default:** By rating

---

## 🚀 **How to Use the Platform**

### **For Admins:**

#### **1. Login:**
```
1. Go to login page
2. Email: admin@trainai.com
3. Password: (your admin password)
4. Access admin dashboard
```

#### **2. Manage Users:**
```
1. Go to Admin Dashboard → Manage Users
2. Search/filter users
3. Add/remove points
4. Activate/deactivate accounts
5. View user statistics
```

#### **3. Manage Courses:**
```
1. Go to Manage Courses
2. View all courses
3. See enrollment statistics
4. Monitor course performance
```

#### **4. Manage Enrollments:**
```
1. Go to Enrollments
2. View all enrollments
3. Unenroll users if needed
4. Track progress
```

#### **5. Manage Certificates:**
```
1. Go to Certificates
2. View all issued certificates
3. Delete certificates if needed
4. Verify authenticity
```

#### **6. Generate Reports:**
```
1. Go to Reports
2. View platform analytics
3. Click "Export as CSV" or "Export as Excel"
4. Download report file
5. Analyze data
```

---

### **For Trainers:**

#### **1. Create Course:**
```
1. Login as trainer
2. Go to My Courses
3. Click "Create New Course"
4. Fill in course details:
   - Course name
   - Course code
   - Category
   - Duration
   - Description
5. Click "Create Course"
```

#### **2. Add Modules:**
```
1. Go to course → Manage Modules
2. Click "Add New Module"
3. Enter module title and description
4. Click "Add Module"
5. Modules auto-ordered
```

#### **3. Add Lessons:**
```
1. Select module
2. Click "Add Lesson"
3. Enter lesson details:
   - Lesson title
   - Description
   - Video URL (YouTube, Vimeo)
   - PDF URL (Google Drive, Dropbox)
4. At least one (video or PDF) required
5. Click "Add Lesson"
```

#### **4. Create Exam:**
```
1. Go to course → Create Exam
2. Enter exam title
3. Add questions:
   - Question text
   - 4 options (A, B, C, D)
   - Correct answer
4. Set passing score (default: 75%)
5. Click "Create Exam"
```

#### **5. Monitor Students:**
```
1. Go to My Courses
2. View enrollment count
3. See student progress
4. Track completion rates
```

---

### **For Trainees (Students):**

#### **1. Register:**
```
1. Go to registration page
2. Fill in details:
   - First name, Last name
   - Email
   - Password
   - Role: Trainee
3. Agree to terms
4. Click "Create Account"
```

#### **2. Browse Courses:**
```
1. Login to dashboard
2. Go to "Explore Courses"
3. Search or filter courses
4. View course details
5. Click "Enroll Now"
```

#### **3. Learn:**
```
1. Go to "My Courses"
2. Click on enrolled course
3. View modules and lessons
4. Click "Watch" for videos
5. Click "View PDF" for documents
6. Complete all modules
```

#### **4. Take Exam:**
```
1. Complete all course modules
2. Click "Take the Final Exam"
3. Answer all questions
4. Click "Submit Exam"
5. View results instantly
```

#### **5. Get Certificate:**
```
1. Pass exam with 75%+
2. Certificate auto-generated
3. Go to "My Certificates"
4. Click "Download"
5. Print or save PDF
```

#### **6. Track Progress:**
```
1. View dashboard
2. See weekly activity chart
3. Check learning hours
4. View points and level
5. See leaderboard rank
```

---

## 🎨 **User Interface Features**

### **Design Principles:**
- ✅ Clean, minimal design
- ✅ Beige/cream background
- ✅ Black accents
- ✅ Responsive layout
- ✅ Mobile-friendly
- ✅ Intuitive navigation

### **Components:**
- ✅ Sidebar navigation
- ✅ Top bar with user info
- ✅ Card-based layouts
- ✅ Interactive charts (Chart.js)
- ✅ Modal dialogs
- ✅ Toast notifications
- ✅ Progress bars
- ✅ Badges and labels

### **Animations:**
- ✅ Smooth transitions (0.3s)
- ✅ Hover effects
- ✅ Card lift on hover
- ✅ Button animations
- ✅ Loading indicators

---

## 📱 **Responsive Design**

### **Breakpoints:**
```css
Desktop: > 1024px
Tablet: 768px - 1024px
Mobile: < 768px
```

### **Adaptations:**
- **Desktop:** Full sidebar, multi-column grids
- **Tablet:** Collapsible sidebar, 2-column grids
- **Mobile:** Hidden sidebar (toggle), single column

---

## 🔧 **Technical Stack**

### **Backend:**
- **Language:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP)
- **Architecture:** MVC Pattern

### **Frontend:**
- **HTML5:** Semantic markup
- **CSS3:** Flexbox, Grid, Animations
- **JavaScript:** ES6+, Fetch API
- **Libraries:** Chart.js 3.9.1

### **Security:**
- **Password:** bcrypt hashing
- **Sessions:** PHP native sessions
- **SQL:** Prepared statements (PDO)
- **XSS:** htmlspecialchars()

---

## 📈 **Performance Optimizations**

### **Database:**
- ✅ Indexed columns (user_id, course_id, email)
- ✅ Foreign key constraints
- ✅ Optimized queries with JOINs
- ✅ Query result caching

### **Frontend:**
- ✅ Minified CSS/JS (production)
- ✅ Lazy loading images
- ✅ Async JavaScript
- ✅ Debounced search

### **Server:**
- ✅ Gzip compression
- ✅ Browser caching
- ✅ Session optimization
- ✅ Connection pooling

---

## 🧪 **Testing Checklist**

### **Authentication:**
- ✅ Login with valid credentials
- ✅ Login with invalid credentials
- ✅ Register new user
- ✅ Remember me functionality
- ✅ Forgot password flow
- ✅ Session timeout

### **Course Management:**
- ✅ Create course
- ✅ Add modules
- ✅ Add lessons (video/PDF)
- ✅ Create exam
- ✅ Enroll in course

### **Learning:**
- ✅ View course modules
- ✅ Watch videos
- ✅ View PDFs
- ✅ Track progress
- ✅ Take exam
- ✅ Receive certificate

### **Admin:**
- ✅ View all users
- ✅ Manage points
- ✅ Unenroll users
- ✅ Delete certificates
- ✅ Generate reports

---

## 📦 **Installation Guide**

### **Requirements:**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache server
- XAMPP (recommended)

### **Steps:**
```
1. Install XAMPP
2. Copy project to htdocs/Learning Platform
3. Import database:
   - Open phpMyAdmin
   - Create database: learning_platform
   - Import schema.sql
4. Configure database:
   - Edit config/database.php
   - Set DB credentials
5. Start Apache and MySQL
6. Access: http://localhost/Learning Platform
```

---

## 🎓 **Default Credentials**

### **Admin:**
```
Email: admin@trainai.com
Password: password
```

### **Trainer:**
```
Email: trainer@trainai.com
Password: password
```

### **Trainee:**
```
Email: student@trainai.com
Password: password
```

**⚠️ Change these passwords after first login!**

---

## 📊 **Project Statistics**

### **Code Metrics:**
- **Total Files:** 100+
- **Lines of Code:** 15,000+
- **Database Tables:** 14
- **User Roles:** 3
- **Features:** 50+

### **Functionality:**
- **Pages:** 30+
- **Forms:** 20+
- **Reports:** 5
- **Charts:** 3
- **Notifications:** Real-time

---

## 🎉 **Key Achievements**

✅ **Complete LMS** - Full-featured learning platform  
✅ **Three User Roles** - Admin, Trainer, Trainee  
✅ **Secure Authentication** - bcrypt + tokens  
✅ **Course Management** - Modules, lessons, exams  
✅ **Gamification** - Points, levels, leaderboard  
✅ **Certificates** - Auto-generated, downloadable  
✅ **Analytics** - Charts, reports, insights  
✅ **Admin Control** - Full platform management  
✅ **Responsive Design** - Works on all devices  
✅ **Professional UI** - Clean, modern interface  

---

## 📝 **Future Enhancements**

### **Potential Features:**
- 🔮 Live video classes (WebRTC)
- 🔮 Discussion forums
- 🔮 Assignment submissions
- 🔮 Peer reviews
- 🔮 Mobile app (React Native)
- 🔮 AI-powered recommendations
- 🔮 Advanced analytics (ML)
- 🔮 Multi-language support
- 🔮 Payment integration
- 🔮 Email notifications

---

## 📞 **Support & Contact**

**Project:** TrainAI Learning Platform  
**Version:** 1.0  
**Status:** Production Ready  
**License:** Proprietary  

---

## 🏆 **Credits**

**Developed by:** DebugThugs Team  
**Platform:** TrainAI  
**Year:** 2025  

---

**🎓 TrainAI - Empowering Learning Through Technology**

---

*This documentation covers all features, algorithms, and usage instructions for the TrainAI Learning Platform. For technical support or questions, please refer to the code comments or contact the development team.*
