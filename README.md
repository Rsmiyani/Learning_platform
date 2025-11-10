# 🎓 TrainAI Learning Platform

> A comprehensive AI-powered Learning Management System (LMS) for organizations

[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

---

## 📋 **Overview**

TrainAI is a full-featured Learning Management System designed for organizations to manage training programs, courses, and certifications. It supports three user roles (Admin, Trainer, Trainee) with comprehensive features for course creation, learning tracking, gamification, and analytics.

---

## ✨ **Key Features**

### **🔐 Authentication & Security**
- Secure login with bcrypt password hashing
- "Remember Me" functionality
- Forgot password with token-based reset
- Role-based access control (RBAC)
- Session management

### **📚 Course Management**
- Create courses with modules and lessons
- Support for video (YouTube, Vimeo) and PDF content
- Structured learning paths
- Progress tracking
- Course categories and search

### **📝 Examination System**
- Multiple-choice questions
- Automatic grading
- 75% passing threshold
- Instant results
- Retake functionality

### **🎓 Certificates**
- Auto-generated on exam pass
- Unique certificate codes
- PDF download
- Verification system
- Admin management

### **🎮 Gamification**
- Points system
- Level progression
- Leaderboard rankings
- Achievement tracking
- Weekly activity charts

### **👑 Admin Dashboard**
- Full platform control
- User management
- Points manipulation
- Enrollment management
- Certificate control
- Analytics & reports (CSV/Excel export)

### **📊 Analytics**
- Weekly activity tracking
- Study hours logging
- Video watch time tracking
- Progress monitoring
- Platform statistics

### **📧 Email Notifications** ⭐ **NEW!**
- Password reset emails
- Welcome emails on registration
- Certificate earned notifications
- Exam result notifications
- Professional HTML templates
- SMTP support for production

---

## 🛠️ **Technology Stack**

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Charts:** Chart.js 3.9.1
- **Server:** Apache (XAMPP)
- **Architecture:** MVC Pattern

---

## 📦 **Installation**

### **Prerequisites:**
- XAMPP (or Apache + MySQL + PHP)
- Web browser
- Text editor (optional)

### **Steps:**

1. **Install XAMPP**
   ```bash
   Download from: https://www.apachefriends.org/
   ```

2. **Copy Project**
   ```bash
   Copy to: C:\xampp\htdocs\Learning Platform
   ```

3. **Create Database**
   ```sql
   1. Open phpMyAdmin: http://localhost/phpmyadmin
   2. Create database: learning_platform
   3. Import: database/schema.sql
   ```

4. **Configure**
   ```php
   Edit: config/database.php
   Set your database credentials
   ```

5. **Start Services**
   ```bash
   Start Apache and MySQL in XAMPP Control Panel
   ```

6. **Access Platform**
   ```
   URL: http://localhost/Learning Platform
   ```

---

## 🔑 **Default Credentials**

### **Admin Account:**
```
Email: admin@trainai.com
Password: password
```

### **Trainer Account:**
```
Email: trainer@trainai.com
Password: password
```

### **Student Account:**
```
Email: student@trainai.com
Password: password
```

**⚠️ Important:** Change these passwords after first login!

---

## 📖 **Documentation**

- **📚 Full Documentation:** [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md)
- **🚀 Quick Start Guide:** [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)
- **📧 Email Setup Guide:** [EMAIL_SETUP_QUICK_START.md](EMAIL_SETUP_QUICK_START.md) ⭐ **NEW!**
- **📧 Email Documentation:** [EMAIL_NOTIFICATION_GUIDE.md](EMAIL_NOTIFICATION_GUIDE.md) ⭐ **NEW!**
- **🗄️ Database Schema:** [database/schema.sql](database/schema.sql)

---

## 🎯 **User Roles**

### **👑 Admin**
- Full platform control
- User management (add/remove points, activate/deactivate)
- Course oversight
- Enrollment management (unenroll users)
- Certificate control (delete certificates)
- Analytics and reporting

### **👨‍🏫 Trainer**
- Create and manage courses
- Add modules and lessons
- Upload video/PDF content
- Create exams
- Monitor student progress
- View enrollment statistics

### **👨‍🎓 Trainee (Student)**
- Browse and enroll in courses
- Access learning materials
- Watch videos and view PDFs
- Take exams
- Earn certificates
- Track progress and points
- View leaderboard

---

## 🗄️ **Database Structure**

### **Core Tables:**
- `users` - User accounts and authentication
- `courses` - Course information
- `course_modules` - Course modules
- `module_lessons` - Lessons with video/PDF
- `course_enrollments` - Student enrollments
- `exams` - Exam information
- `exam_questions` - Exam questions
- `exam_results` - Exam scores
- `user_certificates` - Issued certificates
- `user_points` - Points and levels
- `study_logs` - Learning activity
- `notifications` - System notifications
- `remember_tokens` - Remember me tokens
- `video_watch_logs` - Video tracking

---

## 🔐 **Security Features**

- ✅ **Password Hashing:** bcrypt with cost factor 10
- ✅ **SQL Injection Prevention:** Prepared statements (PDO)
- ✅ **XSS Protection:** htmlspecialchars() on all outputs
- ✅ **CSRF Protection:** Session-based tokens
- ✅ **Session Security:** Secure session handling
- ✅ **Token Security:** SHA-256 hashing for remember tokens
- ✅ **Access Control:** Role-based permissions

---

## 🎨 **Design**

- **Theme:** Clean, minimal design
- **Colors:** Beige/cream background with black accents
- **Layout:** Responsive (Desktop, Tablet, Mobile)
- **Components:** Card-based, modern UI
- **Animations:** Smooth transitions and hover effects

---

## 📊 **Algorithms Used**

### **Password Security:**
- **bcrypt** hashing with automatic salt generation

### **Level Calculation:**
- Formula: `level = ⌊√(points/100)⌋ + 1`
- Square root progression for balanced difficulty

### **Exam Grading:**
- Formula: `score = (correct/total) × 100`
- Pass threshold: 75%

### **Certificate Generation:**
- Unique code: `CERT-[HASH]-[TIMESTAMP]`
- Certificate number: `CERT-[USER]-[COURSE]`

### **Video Tracking:**
- Time tracking with JavaScript
- Conversion: seconds → hours
- Aggregation by date

### **Ranking System:**
- SQL RANK() function
- Tie handling with same rank
- Descending order by points

---

## 📈 **Features Breakdown**

### **Course Creation:**
1. Create course with details
2. Add modules (auto-ordered)
3. Add lessons (video/PDF)
4. Create exam with questions
5. Publish to students

### **Learning Flow:**
1. Student browses courses
2. Enrolls in course
3. Completes modules
4. Watches videos/views PDFs
5. Takes final exam
6. Receives certificate (if passed)

### **Gamification:**
1. Earn points for activities
2. Level up automatically
3. Compete on leaderboard
4. Track weekly progress
5. View achievements

### **Admin Control:**
1. View all platform data
2. Manage user points
3. Unenroll students
4. Delete certificates
5. Generate reports

---

## 📱 **Responsive Design**

- **Desktop (>1024px):** Full sidebar, multi-column grids
- **Tablet (768-1024px):** Collapsible sidebar, 2-column grids
- **Mobile (<768px):** Hidden sidebar (toggle), single column

---

## 🧪 **Testing**

### **Test Accounts:**
Use the default credentials above to test each role.

### **Test Scenarios:**
1. **Authentication:** Login, register, forgot password
2. **Course Creation:** Create course, add content, create exam
3. **Learning:** Enroll, watch videos, take exam
4. **Gamification:** Earn points, level up, check leaderboard
5. **Admin:** Manage users, generate reports

---

## 📊 **Project Statistics**

- **Total Files:** 100+
- **Lines of Code:** 15,000+
- **Database Tables:** 14
- **User Roles:** 3
- **Features:** 50+
- **Pages:** 30+
- **Forms:** 20+

---

## 🚀 **Performance**

### **Optimizations:**
- Indexed database columns
- Optimized SQL queries
- Lazy loading
- Async JavaScript
- Gzip compression
- Browser caching

---

## 🔮 **Future Enhancements**

- [x] Email notifications ✅ **NEW!**
- [ ] Live video classes (WebRTC)
- [ ] Discussion forums
- [ ] Assignment submissions
- [ ] Peer reviews
- [ ] Mobile app
- [ ] AI recommendations
- [ ] Advanced analytics
- [ ] Multi-language support
- [ ] Payment integration

---

## 📞 **Support**

For questions or issues:
1. Check [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md)
2. Review [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)
3. Check code comments
4. Contact development team

---

## 📝 **License**

Proprietary - All rights reserved

---

## 👥 **Credits**

**Developed by:** DebugThugs Team  
**Platform:** TrainAI  
**Year:** 2025  

---

## 🎉 **Acknowledgments**

- Chart.js for beautiful charts
- PHP community for excellent documentation
- MySQL for robust database
- Apache for reliable server

---

## 📸 **Screenshots**

### **Login Page**
Clean, minimal design with beige background

### **Student Dashboard**
Weekly activity chart, enrolled courses, leaderboard

### **Course Modules**
Structured learning with videos and PDFs

### **Admin Dashboard**
Full platform control with statistics

### **Certificates**
Professional, downloadable certificates

---

## 🏆 **Key Achievements**

✅ Complete LMS with 3 user roles  
✅ Secure authentication system  
✅ Course management with modules  
✅ Gamification with points & levels  
✅ Auto-generated certificates  
✅ **Email notifications** ⭐ **NEW!**  
✅ Admin control panel  
✅ Analytics & reporting  
✅ Responsive design  
✅ Professional UI  
✅ Production-ready  

---

**🎓 TrainAI - Empowering Learning Through Technology**

---

*For detailed information, see [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md)*
