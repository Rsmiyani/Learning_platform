# TrainAI Database Schema Documentation

**Database Name:** `ai-train1`  
**Server:** MariaDB 10.4.32  
**Charset:** utf8mb4

---

## Database Tables Overview

### **1. Users & Authentication**

#### `users`
- **Primary Key:** `user_id`
- **Fields:** first_name, last_name, email, password, role, created_at, last_login, status
- **Roles:** trainee, trainer, admin
- **Status:** active, inactive

---

### **2. Course Management**

#### `courses`
- **Primary Key:** `course_id`
- **Unique:** `course_code`
- **Fields:** course_name, course_code, instructor_id, description, difficulty, duration_hours, thumbnail_url, rating, total_ratings, category, is_recommended
- **Foreign Key:** instructor_id → users(user_id)

#### `course_modules`
- **Primary Key:** `module_id`
- **Fields:** course_id, module_title, module_description, module_order
- **Foreign Key:** course_id → courses(course_id) ON DELETE CASCADE

#### `module_lessons`
- **Primary Key:** `lesson_id`
- **Fields:** module_id, lesson_title, lesson_description, video_url, video_duration, lesson_order
- **Foreign Key:** module_id → course_modules(module_id) ON DELETE CASCADE

---

### **3. Enrollment & Progress**

#### `course_enrollments`
- **Primary Key:** `enrollment_id`
- **Unique:** (user_id, course_id)
- **Fields:** user_id, course_id, progress_percentage, last_accessed, enrolled_at, status
- **Status:** active, completed, paused
- **Foreign Keys:** 
  - user_id → users(user_id) ON DELETE CASCADE
  - course_id → courses(course_id) ON DELETE CASCADE

#### `course_ratings`
- **Primary Key:** `rating_id`
- **Unique:** (user_id, course_id)
- **Fields:** user_id, course_id, rating_value (0.0-5.0), review_text
- **Foreign Keys:** Both CASCADE on delete

---

### **4. Examination System**

#### `exam_questions`
- **Primary Key:** `question_id`
- **Fields:** course_id, question_text, question_type, question_order
- **Types:** multiple_choice, true_false, short_answer
- **Foreign Key:** course_id → courses(course_id) ON DELETE CASCADE

#### `question_options`
- **Primary Key:** `option_id`
- **Fields:** question_id, option_text, is_correct, option_order
- **Foreign Key:** question_id → exam_questions(question_id) ON DELETE CASCADE

#### `exam_results`
- **Primary Key:** `result_id`
- **Fields:** user_id, course_id, total_questions, correct_answers, score_percentage, passed, attempted_at

#### `student_exam_responses`
- **Primary Key:** `response_id`
- **Fields:** user_id, course_id, question_id, selected_option_id, answer_text, is_correct, submitted_at

---

### **5. Gamification System**

#### `user_points`
- **Primary Key:** `point_id`
- **Unique:** user_id
- **Fields:** user_id, total_points, level, last_updated
- **Level Calculation:** floor(total_points / 100) + 1

#### `achievements`
- **Primary Key:** `achievement_id`
- **Fields:** achievement_name, achievement_icon, description, condition_type, condition_value, points_reward
- **Condition Types:** courses_completed, streak_days, perfect_quiz, fast_completion, total_hours, challenges_completed

#### `user_achievements`
- **Primary Key:** `user_achievement_id`
- **Unique:** (user_id, achievement_id)
- **Fields:** user_id, achievement_id, earned_at
- **Foreign Keys:** Both CASCADE on delete

#### `daily_challenges`
- **Primary Key:** `challenge_id`
- **Fields:** challenge_name, description, challenge_type, points_reward, difficulty, created_date, status
- **Status:** active, inactive

#### `user_challenge_progress`
- **Primary Key:** `progress_id`
- **Unique:** (user_id, challenge_id)
- **Fields:** user_id, challenge_id, status, progress_percentage, completed_at
- **Status:** pending, in_progress, completed

---

### **6. Certificates**

#### `user_certificates`
- **Primary Key:** `cert_id`
- **Unique:** certificate_number, certificate_code
- **Fields:** user_id, course_id, certificate_code, certificate_number, issued_date, certificate_url
- **Format:** CERT-{hash}-{timestamp}, CERT-{user_id}-{course_id}
- **Foreign Keys:** Both CASCADE on delete

---

### **7. User Activity & Analytics**

#### `study_logs`
- **Primary Key:** `log_id`
- **Fields:** user_id, study_date, hours_studied, courses_studied, activities
- **Index:** (user_id, study_date)

#### `study_streaks`
- **Primary Key:** `streak_id`
- **Unique:** user_id
- **Fields:** user_id, streak_count, last_study_date, longest_streak

#### `user_bookmarks`
- **Primary Key:** `bookmark_id`
- **Unique:** (user_id, course_id)
- **Fields:** user_id, course_id, bookmarked_at

#### `user_interests`
- **Primary Key:** `interest_id`
- **Fields:** user_id, interest_name
- **Used for:** AI-powered course recommendations

---

### **8. Notifications**

#### `notifications`
- **Primary Key:** `notification_id`
- **Fields:** user_id, notification_type, title, message, is_read, created_at
- **Types:** achievement_earned, module_added, course_update, etc.
- **Index:** (user_id, is_read)

---

## Key Features

### **Gamification Logic**
- **Points System:** Users earn points for completing courses, exams, and challenges
- **Level Progression:** 100 points = 1 level
- **Achievement Milestones:**
  - 100 pts: First Step (🎯)
  - 200 pts: 100% Champion (💯)
  - 300 pts: Week Warrior (🔥)
  - 400 pts: Speed Reader (⚡)
  - 500 pts: Learning Enthusiast (📚)
  - 600 pts: Month Master (⭐)
  - 700 pts: Time Master (⏰)
  - 800 pts: Early Bird (🌅)

### **Course Structure**
```
Course
  ├── Modules (ordered)
  │     └── Lessons (ordered, with video URLs)
  └── Exam
        ├── Questions (multiple choice / true-false / short answer)
        └── Options (for multiple choice)
```

### **Enrollment Flow**
1. User enrolls in course
2. Views modules and lessons
3. Takes exam
4. Receives certificate (if passed)
5. Earns points and achievements

### **AI Recommendations**
- Based on `user_interests` table
- Matches course categories with user preferences
- Fallback to popular courses (by rating and enrollments)

---

## Sample Data

### **Users**
- Admin: admin@trainai.com
- Trainee: admin2002@gmail.com (Rudra Miyani)
- Trainer: admin2003@gmail.com (Rudra Miyani1)

### **Courses**
- 25+ courses across categories:
  - Programming (Python, JavaScript, C++, Java)
  - Web Development (React, Vue, Full Stack)
  - Data Science (ML, Deep Learning)
  - Cloud (AWS, Docker, Kubernetes)
  - Mobile (Android, iOS, React Native)
  - Database (SQL, MongoDB)
  - Design (UI/UX, Figma)

---

## Database Relationships

```
users (1) ─────< (M) courses [instructor_id]
users (1) ─────< (M) course_enrollments
courses (1) ────< (M) course_enrollments
courses (1) ────< (M) course_modules
course_modules (1) ─< (M) module_lessons
courses (1) ────< (M) exam_questions
exam_questions (1) ─< (M) question_options
users (1) ─────< (M) exam_results
users (1) ─────< (M) user_achievements
achievements (1) ──< (M) user_achievements
users (1) ─────< (M) user_points
users (1) ─────< (M) notifications
users (1) ─────< (M) user_bookmarks
users (1) ─────< (M) user_certificates
```

---

## Indexes

- **users:** email (UNIQUE)
- **courses:** course_code (UNIQUE), category, instructor_id
- **course_enrollments:** (user_id, course_id) UNIQUE, user_id, course_id
- **notifications:** (user_id, is_read)
- **study_logs:** (user_id, study_date)

---

## Notes

- All timestamps use MySQL `current_timestamp()`
- Password hashing: PHP `password_hash()` with bcrypt
- Foreign keys enforce referential integrity
- CASCADE deletes maintain data consistency
- UTF8MB4 charset supports emojis in achievement icons

---

**Last Updated:** November 1, 2025  
**Database Version:** MariaDB 10.4.32  
**Project:** TrainAI by Team DebugThugs
