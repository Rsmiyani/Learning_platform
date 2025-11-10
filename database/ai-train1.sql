-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 07:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ai-train1`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `achievement_id` int(11) NOT NULL,
  `achievement_name` varchar(100) NOT NULL,
  `achievement_icon` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `condition_type` varchar(50) DEFAULT NULL,
  `condition_value` int(11) DEFAULT NULL,
  `points_reward` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`achievement_id`, `achievement_name`, `achievement_icon`, `description`, `condition_type`, `condition_value`, `points_reward`) VALUES
(1, 'First Step', '🎯', 'Complete your first course', 'courses_completed', 1, 100),
(2, 'Week Warrior', '🔥', 'Maintain 7-day streak', 'streak_days', 7, 200),
(3, 'Month Master', '⭐', 'Maintain 30-day streak', 'streak_days', 30, 500),
(4, '100% Champion', '💯', 'Score 100% in a quiz', 'perfect_quiz', 1, 150),
(5, 'Learning Enthusiast', '📚', 'Complete 5 courses', 'courses_completed', 5, 300),
(6, 'Speed Reader', '⚡', 'Complete a course in 1 week', 'fast_completion', 1, 250),
(7, 'Time Master', '⏰', 'Study 100 hours', 'total_hours', 100, 400),
(8, 'Early Bird', '🌅', 'Complete 10 challenges', 'challenges_completed', 10, 200),
(9, '🎯 First Step', '🎯', 'Complete your first course', 'courses_completed', 1, 100),
(10, '🔥 Week Warrior', '🔥', 'Maintain 7-day streak', 'streak_days', 7, 200),
(11, '⭐ Month Master', '⭐', 'Maintain 30-day streak', 'streak_days', 30, 500),
(12, '💯 100% Champion', '💯', 'Score 100% in a quiz', 'perfect_quiz', 1, 150),
(13, '📚 Learning Enthusiast', '📚', 'Complete 5 courses', 'courses_completed', 5, 300),
(14, '⚡ Speed Reader', '⚡', 'Complete a course in 1 week', 'fast_completion', 1, 250),
(15, '⏰ Time Master', '⏰', 'Study 100 hours', 'total_hours', 100, 400),
(16, '🌅 Early Bird', '🌅', 'Complete 10 challenges', 'challenges_completed', 10, 200);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `course_code` varchar(50) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `instructor_name` varchar(100) DEFAULT NULL,
  `difficulty` varchar(20) DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `total_ratings` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `is_recommended` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `course_code`, `instructor_id`, `description`, `instructor_name`, `difficulty`, `duration_hours`, `thumbnail_url`, `rating`, `total_ratings`, `category`, `is_recommended`, `created_at`) VALUES
(136, 'Python Programming Basics', 'PY101', 1, 'Learn Python from scratch - Perfect for beginners', 'John Smith', 'Beginner', 24, 'https://www.google.com/imgres?q=python%20course&imgurl=https%3A%2F%2Fcdn.shopaccino.com%2Figmguru%2Fproducts%2Fpython-training--igmguru-64626207279351_m.jpg%3Fv%3D531&imgrefurl=https%3A%2F%2Fwww.igmguru.com%2Fdata-science-bi%2Fpython-training&docid=QBKQ7l', 4.8, 2500, 'Programming', 1, '2025-10-30 14:56:50'),
(137, 'Advanced Python', 'PY201', 1, 'Master advanced Python concepts and patterns', 'Sarah Wilson', 'Advanced', 40, NULL, 4.9, 1800, 'Programming', 0, '2025-10-30 14:56:50'),
(138, 'JavaScript Fundamentals', 'JS101', 1, 'Complete JavaScript guide for web development', 'Mike Davis', 'Beginner', 20, NULL, 4.7, 2100, 'Programming', 1, '2025-10-30 14:56:50'),
(139, 'JavaScript Advanced', 'JS202', 1, 'Advanced JavaScript ES6+ and patterns', 'Tech Academy', 'Intermediate', 28, NULL, 4.6, 1500, 'Programming', 0, '2025-10-30 14:56:50'),
(140, 'C++ Programming', 'CPP101', 1, 'Learn C++ for competitive programming', 'Alex Johnson', 'Intermediate', 32, NULL, 4.5, 900, 'Programming', 0, '2025-10-30 14:56:50'),
(141, 'Java Mastery', 'JAVA101', 1, 'Complete Java programming course', 'Robert Miller', 'Beginner', 36, NULL, 4.7, 1200, 'Programming', 1, '2025-10-30 14:56:50'),
(142, 'Web Development Masterclass', 'WEB201', 1, 'HTML, CSS, JavaScript & More - Build amazing websites', 'Sarah Johnson', 'Intermediate', 32, NULL, 4.7, 1800, 'Web Development', 1, '2025-10-30 14:56:50'),
(143, 'React.js Complete Guide', 'REACT201', 1, 'Master React for modern web applications', 'Emma Chen', 'Intermediate', 28, NULL, 4.8, 1600, 'Web Development', 0, '2025-10-30 14:56:50'),
(144, 'Vue.js for Beginners', 'VUE101', 1, 'Learn Vue.js framework from scratch', 'Daniel Lee', 'Beginner', 24, NULL, 4.6, 1100, 'Web Development', 0, '2025-10-30 14:56:50'),
(145, 'Full Stack Web Development', 'FULLSTACK301', 1, 'Frontend to Backend development', 'Lisa Park', 'Advanced', 48, NULL, 4.9, 2200, 'Web Development', 0, '2025-10-30 14:56:50'),
(146, 'Data Science with Python', 'DS301', 1, 'Machine Learning & Data Analysis', 'Mike Wilson', 'Advanced', 40, NULL, 4.9, 3200, 'Data Science', 1, '2025-10-30 14:56:50'),
(147, 'Machine Learning Basics', 'ML101', 1, 'Introduction to Machine Learning', 'Dr. Rajesh', 'Intermediate', 36, NULL, 4.7, 1400, 'Data Science', 1, '2025-10-30 14:56:50'),
(148, 'Deep Learning Fundamentals', 'DL201', 1, 'Neural Networks and Deep Learning', 'Prof. Kumar', 'Advanced', 44, NULL, 4.8, 900, 'Data Science', 0, '2025-10-30 14:56:50'),
(149, 'Data Analytics & Excel', 'DA101', 1, 'Master data analysis with Excel and Python', 'Maria Garcia', 'Beginner', 20, NULL, 4.5, 1600, 'Data Science', 0, '2025-10-30 14:56:50'),
(150, 'Cloud Computing 101', 'CLOUD101', 1, 'AWS, Azure & Google Cloud', 'Cloud Experts', 'Intermediate', 28, NULL, 4.5, 1200, 'Cloud', 1, '2025-10-30 14:56:50'),
(151, 'AWS Certification Course', 'AWS201', 1, 'Complete AWS Solutions Architect path', 'David Brown', 'Advanced', 32, NULL, 4.7, 800, 'Cloud', 0, '2025-10-30 14:56:50'),
(152, 'Docker & Kubernetes', 'DOCKER201', 1, 'Containerization and orchestration', 'Container Pro', 'Advanced', 30, NULL, 4.6, 700, 'Cloud', 0, '2025-10-30 14:56:50'),
(153, 'Android Development', 'ANDROID101', 1, 'Build Android apps with Java and Kotlin', 'James Wilson', 'Intermediate', 35, NULL, 4.6, 950, 'Mobile Development', 1, '2025-10-30 14:56:50'),
(154, 'iOS Development with Swift', 'IOS201', 1, 'Create beautiful iOS applications', 'Apple Expert', 'Intermediate', 32, NULL, 4.7, 850, 'Mobile Development', 0, '2025-10-30 14:56:50'),
(155, 'React Native Complete', 'Rnative201', 1, 'Cross-platform mobile development', 'Mobile Dev', 'Intermediate', 28, NULL, 4.5, 750, 'Mobile Development', 0, '2025-10-30 14:56:50'),
(156, 'SQL & Database Design', 'SQL101', 1, 'Master SQL and database fundamentals', 'DB Expert', 'Beginner', 22, NULL, 4.6, 1300, 'Database', 0, '2025-10-30 14:56:50'),
(157, 'MongoDB & NoSQL', 'MONGODB201', 1, 'Learn NoSQL databases', 'NoSQL Pro', 'Intermediate', 24, NULL, 4.5, 600, 'Database', 0, '2025-10-30 14:56:50'),
(158, 'Node.js Backend Development', 'NODE201', 1, 'Build scalable backend with Node.js', 'Backend Master', 'Intermediate', 30, NULL, 4.7, 1100, 'Backend', 1, '2025-10-30 14:56:50'),
(159, 'UI/UX Design Fundamentals', 'DESIGN101', 1, 'Learn modern design principles', 'Design Expert', 'Beginner', 20, NULL, 4.7, 1400, 'Design', 0, '2025-10-30 14:56:50'),
(160, 'Figma for Designers', 'FIGMA101', 1, 'Master Figma design tool', 'Figma Pro', 'Beginner', 16, NULL, 4.6, 900, 'Design', 0, '2025-10-30 14:56:50'),
(161, 'Data ', '0200', 3, 'ss', NULL, 'Beginner', 25, 'https://www.google.com/imgres?q=python%20course&imgurl=https%3A%2F%2Fcdn.shopaccino.com%2Figmguru%2Fproducts%2Fpython-training--igmguru-64626207279351_m.jpg%3Fv%3D531&imgrefurl=https%3A%2F%2Fwww.igmguru.com%2Fdata-science-bi%2Fpython-training&docid=QBKQ7l', 0.0, 0, 'Web Development', 0, '2025-10-31 08:14:12'),
(162, 'Data', '0201', 3, 'oyoo', NULL, 'Intermediate', 96, 'https://i.ibb.co/wJz5qKm/python-course.jpg', 0.0, 0, 'Data Science', 0, '2025-10-31 08:20:01'),
(163, 'data 3', 'PY2022', 3, 'learn with me', NULL, 'Advanced', 96, '../../uploads/courses/6904781fa732d_download.jpeg', 0.0, 0, 'Web Development', 0, '2025-10-31 08:49:35'),
(164, 'hmm', '9300', 3, 'here', NULL, 'Beginner', 99, '../../uploads/courses/6904b328cc863_wmremove-transformed.png', 5.0, 2, 'Other', 0, '2025-10-31 13:01:28'),
(165, 'test', '00066', 3, 'hil', NULL, 'Intermediate', 30, '../../uploads/courses/69061b3dc0424_download.jpeg', 4.0, 2, 'Web Development', 0, '2025-11-01 14:37:49'),
(166, 'machin masters', '0360', 3, 'you will learn lot', NULL, 'Intermediate', 20, '../../uploads/courses/69063d4f27cec_Gemini_Generated_Image_bfo8lrbfo8lrbfo8.png', 0.0, 0, 'Design', 0, '2025-11-01 17:03:11'),
(167, 'test duration', '020033', 3, 'hm', NULL, 'Intermediate', 30, '../../uploads/courses/69064275535fe_Gemini_Generated_Image_tnuwnttnuwnttnuw.png', 3.0, 1, 'Mobile Development', 0, '2025-11-01 17:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','paused') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`enrollment_id`, `user_id`, `course_id`, `progress_percentage`, `last_accessed`, `enrolled_at`, `status`) VALUES
(4, 2, 158, 0.00, '2025-10-30 15:04:08', '2025-10-30 15:04:08', 'active'),
(5, 2, 142, 0.00, '2025-10-30 15:04:16', '2025-10-30 15:04:16', 'active'),
(6, 2, 136, 0.00, '2025-10-30 15:09:52', '2025-10-30 15:09:52', 'active'),
(7, 2, 138, 0.00, '2025-10-30 15:10:32', '2025-10-30 15:10:32', 'active'),
(8, 2, 161, 0.00, '2025-10-31 08:24:55', '2025-10-31 08:24:55', 'active'),
(9, 2, 163, 0.00, '2025-10-31 09:00:19', '2025-10-31 09:00:19', 'active'),
(10, 2, 162, 0.00, '2025-10-31 12:49:03', '2025-10-31 12:49:03', 'active'),
(11, 2, 164, 0.00, '2025-10-31 13:26:04', '2025-10-31 13:26:04', 'active'),
(12, 2, 165, 100.00, '2025-11-01 14:41:03', '2025-11-01 14:39:59', 'completed'),
(13, 2, 146, 0.00, '2025-11-01 14:41:28', '2025-11-01 14:41:28', 'active'),
(14, 4, 165, 100.00, '2025-11-01 16:33:55', '2025-11-01 16:33:38', 'completed'),
(15, 5, 164, 100.00, '2025-11-01 16:57:13', '2025-11-01 16:56:40', 'completed'),
(16, 2, 150, 0.00, '2025-11-01 17:24:18', '2025-11-01 17:24:18', 'active'),
(17, 2, 167, 100.00, '2025-11-01 17:26:33', '2025-11-01 17:25:32', 'completed'),
(18, 2, 166, 0.00, '2025-11-02 11:35:13', '2025-11-02 11:35:13', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `course_modules`
--

CREATE TABLE `course_modules` (
  `module_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_title` varchar(255) NOT NULL,
  `module_description` text DEFAULT NULL,
  `module_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_modules`
--

INSERT INTO `course_modules` (`module_id`, `course_id`, `module_title`, `module_description`, `module_order`, `created_at`) VALUES
(1, 163, 'intoduction ', 'you will learn lot', 1, '2025-10-31 10:21:38'),
(2, 164, 'hii', 'hii', 1, '2025-10-31 14:33:43'),
(3, 161, 'hii', 'hi there ', 1, '2025-11-01 14:34:29'),
(4, 165, 'hii', 'hi', 1, '2025-11-01 14:40:25'),
(5, 165, 'notification test', '', 2, '2025-11-01 15:05:40'),
(6, 165, 'notification test-2', '', 3, '2025-11-01 15:09:31'),
(7, 165, 'chap 2 ', 'ddsdd', 4, '2025-11-01 17:02:07'),
(8, 167, 'hii', 'ss', 1, '2025-11-01 17:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `course_ratings`
--

CREATE TABLE `course_ratings` (
  `rating_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `rating_value` decimal(2,1) NOT NULL DEFAULT 0.0,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_ratings`
--

INSERT INTO `course_ratings` (`rating_id`, `user_id`, `course_id`, `rating_value`, `review_text`, `created_at`, `updated_at`) VALUES
(1, 2, 164, 5.0, '', '2025-11-01 14:29:51', '2025-11-01 14:29:51'),
(2, 2, 165, 3.0, 'good\n', '2025-11-01 14:41:09', '2025-11-01 14:41:09'),
(3, 4, 165, 5.0, '', '2025-11-01 16:45:45', '2025-11-01 16:45:45'),
(4, 5, 164, 5.0, '', '2025-11-01 17:00:11', '2025-11-01 17:00:11'),
(5, 2, 167, 3.0, '', '2025-11-01 17:26:37', '2025-11-01 17:26:37');

-- --------------------------------------------------------

--
-- Table structure for table `daily_challenges`
--

CREATE TABLE `daily_challenges` (
  `challenge_id` int(11) NOT NULL,
  `challenge_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `challenge_type` varchar(50) DEFAULT NULL,
  `points_reward` int(11) DEFAULT NULL,
  `difficulty` varchar(20) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_challenges`
--

INSERT INTO `daily_challenges` (`challenge_id`, `challenge_name`, `description`, `challenge_type`, `points_reward`, `difficulty`, `created_date`, `status`) VALUES
(1, 'Code 30 Minutes', 'Study any course for 30 minutes', 'study_session', 50, 'Easy', '2025-10-30', 'active'),
(2, 'Quiz Master', 'Complete a quiz with 80%+ score', 'quiz_completion', 100, 'Medium', '2025-10-30', 'active'),
(3, 'Course Chapter', 'Complete one full course chapter', 'chapter_completion', 75, 'Medium', '2025-10-30', 'active'),
(4, 'Help a Friend', 'Share a course with a friend', 'social', 60, 'Easy', '2025-10-30', 'active'),
(5, 'Streak Keeper', 'Maintain your learning streak', 'streak_maintenance', 80, 'Medium', '2025-10-30', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `exam_questions`
--

CREATE TABLE `exam_questions` (
  `question_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer') DEFAULT 'multiple_choice',
  `question_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_questions`
--

INSERT INTO `exam_questions` (`question_id`, `course_id`, `question_text`, `question_type`, `question_order`, `created_at`) VALUES
(1, 163, 'hi', 'true_false', 1, '2025-10-31 10:44:55'),
(2, 161, 'hi', 'true_false', 1, '2025-10-31 10:52:52'),
(3, 164, 'hi', 'true_false', 1, '2025-10-31 14:29:00'),
(4, 162, 'hii', 'true_false', 1, '2025-10-31 14:29:19'),
(5, 161, 'hi', 'true_false', 2, '2025-10-31 14:29:30'),
(6, 165, 'hii', 'true_false', 1, '2025-11-01 14:40:49'),
(7, 167, 'hi', 'true_false', 1, '2025-11-01 17:26:22');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `result_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `correct_answers` int(11) DEFAULT NULL,
  `score_percentage` decimal(5,2) DEFAULT NULL,
  `passed` tinyint(1) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`result_id`, `user_id`, `course_id`, `total_questions`, `correct_answers`, `score_percentage`, `passed`, `attempted_at`) VALUES
(1, 2, 163, 1, 1, 100.00, 1, '2025-10-31 10:45:12'),
(2, 2, 163, 1, 1, 100.00, 1, '2025-10-31 10:45:20'),
(3, 2, 163, 1, 1, 100.00, 1, '2025-10-31 10:48:21'),
(4, 2, 163, 1, 1, 100.00, 1, '2025-10-31 10:49:59'),
(5, 2, 163, 1, 0, 0.00, 0, '2025-10-31 10:50:23'),
(6, 2, 163, 1, 1, 100.00, 1, '2025-10-31 10:50:30'),
(7, 2, 161, 1, 1, 100.00, 1, '2025-10-31 10:53:13'),
(8, 2, 161, 1, 1, 100.00, 1, '2025-10-31 10:55:27'),
(9, 2, 163, 1, 1, 100.00, 1, '2025-10-31 10:56:50'),
(10, 2, 163, 1, 1, 100.00, 1, '2025-10-31 11:00:56'),
(11, 2, 163, 1, 1, 100.00, 1, '2025-10-31 11:01:10'),
(12, 2, 163, 1, 1, 100.00, 1, '2025-10-31 11:01:42'),
(13, 2, 163, 1, 1, 100.00, 1, '2025-10-31 11:02:12'),
(14, 2, 161, 1, 1, 100.00, 1, '2025-10-31 11:06:39'),
(15, 2, 164, 1, 1, 100.00, 1, '2025-10-31 14:37:15'),
(16, 2, 165, 1, 1, 100.00, 1, '2025-11-01 14:41:03'),
(17, 4, 165, 1, 1, 100.00, 1, '2025-11-01 16:33:55'),
(18, 5, 164, 1, 1, 100.00, 1, '2025-11-01 16:57:13'),
(19, 2, 167, 1, 1, 100.00, 1, '2025-11-01 17:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `module_lessons`
--

CREATE TABLE `module_lessons` (
  `lesson_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `lesson_title` varchar(255) NOT NULL,
  `lesson_description` text DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `pdf_url` text DEFAULT NULL,
  `content_type` enum('video','pdf','both') DEFAULT 'video',
  `video_duration` int(11) DEFAULT NULL,
  `lesson_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `module_lessons`
--

INSERT INTO `module_lessons` (`lesson_id`, `module_id`, `lesson_title`, `lesson_description`, `video_url`, `pdf_url`, `content_type`, `video_duration`, `lesson_order`, `created_at`) VALUES
(1, 2, 'data type', 'lot', 'https://youtu.be/yo01AKeQXiI?si=X9u3a6UB62JuUkd2', NULL, 'video', NULL, 1, '2025-10-31 14:36:53'),
(2, 3, 'data type', 'hii', 'https://youtu.be/yo01AKeQXiI?si=X9u3a6UB62JuUkd2', NULL, 'video', NULL, 1, '2025-11-01 14:34:49'),
(3, 4, 'data type', 'you will learn lot ', 'https://youtu.be/yo01AKeQXiI?si=X9u3a6UB62JuUkd2', NULL, 'video', NULL, 1, '2025-11-01 14:40:40'),
(4, 8, 'data type', 'ss', 'https://youtu.be/yo01AKeQXiI?si=X9u3a6UB62JuUkd2', NULL, 'video', NULL, 1, '2025-11-01 17:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `notification_type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'module_added', 'New Module Added! 📚', 'A new module \'notification test\' has been added to test', 1, '2025-11-01 15:05:40'),
(2, 2, 'module_added', 'New Module Added! 📚', 'A new module \'notification test-2\' has been added to test', 1, '2025-11-01 15:09:31'),
(3, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'First Step\' achievement! (+100 bonus points)', 1, '2025-11-01 15:19:40'),
(4, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'Week Warrior\' achievement! (+200 bonus points)', 1, '2025-11-01 15:19:40'),
(5, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'🔥 Week Warrior\' achievement! (+200 bonus points)', 1, '2025-11-01 15:19:40'),
(6, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'Month Master\' achievement! (+500 bonus points)', 1, '2025-11-01 15:19:40'),
(7, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'⭐ Month Master\' achievement! (+500 bonus points)', 1, '2025-11-01 15:19:40'),
(8, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'100% Champion\' achievement! (+150 bonus points)', 1, '2025-11-01 15:19:40'),
(9, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'💯 100% Champion\' achievement! (+150 bonus points)', 1, '2025-11-01 15:19:40'),
(10, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'Speed Reader\' achievement! (+250 bonus points)', 1, '2025-11-01 15:19:40'),
(11, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'⚡ Speed Reader\' achievement! (+250 bonus points)', 1, '2025-11-01 15:19:40'),
(12, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'Early Bird\' achievement! (+200 bonus points)', 1, '2025-11-01 15:19:40'),
(13, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'🌅 Early Bird\' achievement! (+200 bonus points)', 1, '2025-11-01 15:19:40'),
(14, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'Learning Enthusiast\' achievement!', 1, '2025-11-01 15:20:45'),
(15, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'Time Master\' achievement!', 1, '2025-11-01 15:20:45'),
(16, 2, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'⏰ Time Master\' achievement!', 1, '2025-11-01 15:20:45'),
(17, 4, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'First Step\' achievement!', 1, '2025-11-01 16:33:55'),
(18, 5, 'achievement_earned', 'Achievement Unlocked! 🏆', 'You earned \'First Step\' achievement!', 1, '2025-11-01 16:57:13'),
(19, 2, 'module_added', 'New Module Added! 📚', 'A new module \'chap 2 \' has been added to test', 1, '2025-11-01 17:02:07'),
(20, 4, 'module_added', 'New Module Added! 📚', 'A new module \'chap 2 \' has been added to test', 1, '2025-11-01 17:02:07'),
(21, 2, 'module_added', 'New Module Added! 📚', 'A new module \'hii\' has been added to test duration', 1, '2025-11-01 17:26:02'),
(22, 2, 'exam_added', 'New Exam Available! 📝', 'An exam has been created for test duration. Test your knowledge now!', 1, '2025-11-01 17:26:22');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `user_id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 4, 'rudramiyani2008@gmail.com', '6a409a1619d7e54373dc8b91072e2f23dd0ae2ddaca11e0f5f181956552dfd6d', '2025-11-06 06:57:41', 1, '2025-11-06 10:27:41'),
(2, 4, 'rudramiyani2008@gmail.com', '7c18b9834c51459f7502b6dc1e2d23f24ab8385f9ba2956c2e58c8977f055bc3', '2025-11-06 06:58:41', 1, '2025-11-06 10:28:41'),
(3, 4, 'rudramiyani2008@gmail.com', 'b59d28ab2d7fa21293591dd0af99ffa78e202834f5d40a8ceb420f9d7eadda34', '2025-11-06 07:00:44', 1, '2025-11-06 10:30:44'),
(4, 4, 'rudramiyani2008@gmail.com', '97f66532ed189918bed241cef7c8e50203cfa1ef6a00b720031d7e2d0bec7163', '2025-11-06 07:00:47', 1, '2025-11-06 10:30:47'),
(5, 4, 'rudramiyani2008@gmail.com', '5dd2a92b15ae86ec2109e3b0ffb9c932a9a66e49e8e56976f0c5f082e9dd2e63', '2025-11-06 07:00:56', 1, '2025-11-06 10:30:56'),
(6, 7, 'rudramiyani2007@gmail.com', '97e99927c44c4bbdffa55742ebce12b5fa3ef4647bc8b5661b5c5a12e12a97f0', '2025-11-06 07:01:15', 0, '2025-11-06 10:31:15'),
(7, 4, 'rudramiyani2008@gmail.com', '305c032084166b6ccfd3b0bb63254344802357aee6b8fb1bca9e70f667223448', '2025-11-06 07:01:37', 1, '2025-11-06 10:31:37'),
(8, 4, 'rudramiyani2008@gmail.com', '93655933f2e49378af9211454407e2a2f95e8a050ff8303cbc21c7598b85a7f8', '2025-11-06 07:04:10', 1, '2025-11-06 10:34:10'),
(9, 4, 'rudramiyani2008@gmail.com', 'fca6de7eaa7b9fcb21677ea081969cdfad5386eb01b8332547e4ac7bfa5cf2a2', '2025-11-06 07:04:13', 1, '2025-11-06 10:34:13'),
(10, 4, 'rudramiyani2008@gmail.com', '5730be8046bbcaf493b30f2537deb942c129b68ef7e9004937c239d9b195f1e4', '2025-11-06 07:04:32', 1, '2025-11-06 10:34:32'),
(11, 4, 'rudramiyani2008@gmail.com', 'ed2a6834170b91c9bc4d84a27504004ba3afdc97b330e98f332149eb9dadc062', '2025-11-06 07:05:31', 1, '2025-11-06 10:35:31'),
(12, 4, 'rudramiyani2008@gmail.com', '5d59b80c978aa5ca982d6d1902c60b45d78575ef273caf4efc7418b77b11f663', '2025-11-06 07:06:43', 0, '2025-11-06 10:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(500) NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `option_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`option_id`, `question_id`, `option_text`, `is_correct`, `option_order`) VALUES
(1, 1, 'True', 1, 1),
(2, 1, 'False', 0, 2),
(3, 2, 'True', 1, 1),
(4, 2, 'False', 0, 2),
(5, 3, 'True', 1, 1),
(6, 3, 'False', 0, 2),
(7, 4, 'True', 1, 1),
(8, 4, 'False', 0, 2),
(9, 5, 'True', 1, 1),
(10, 5, 'False', 0, 2),
(11, 6, 'True', 1, 1),
(12, 6, 'False', 0, 2),
(13, 7, 'True', 1, 1),
(14, 7, 'False', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_exam_responses`
--

CREATE TABLE `student_exam_responses` (
  `response_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_exam_responses`
--

INSERT INTO `student_exam_responses` (`response_id`, `user_id`, `course_id`, `question_id`, `selected_option_id`, `answer_text`, `is_correct`, `submitted_at`) VALUES
(1, 2, 163, 1, 1, NULL, 1, '2025-10-31 10:45:12'),
(2, 2, 163, 1, 1, NULL, 1, '2025-10-31 10:45:20'),
(3, 2, 163, 1, 1, NULL, 1, '2025-10-31 10:48:21'),
(4, 2, 163, 1, 1, NULL, 1, '2025-10-31 10:49:59'),
(5, 2, 163, 1, 2, NULL, 0, '2025-10-31 10:50:23'),
(6, 2, 163, 1, 1, NULL, 1, '2025-10-31 10:50:30'),
(7, 2, 161, 2, 1, NULL, 1, '2025-10-31 10:53:13'),
(8, 2, 161, 2, 1, NULL, 1, '2025-10-31 10:55:27'),
(9, 2, 163, 1, 1, NULL, 1, '2025-10-31 10:56:50'),
(10, 2, 163, 1, 1, NULL, 1, '2025-10-31 11:00:56'),
(11, 2, 163, 1, 1, NULL, 1, '2025-10-31 11:01:10'),
(12, 2, 163, 1, 1, NULL, 1, '2025-10-31 11:01:42'),
(13, 2, 163, 1, 1, NULL, 1, '2025-10-31 11:02:12'),
(14, 2, 161, 2, 1, NULL, 1, '2025-10-31 11:06:39'),
(15, 2, 164, 3, 1, NULL, 1, '2025-10-31 14:37:15'),
(16, 2, 165, 6, 1, NULL, 1, '2025-11-01 14:41:03'),
(17, 4, 165, 6, 1, NULL, 1, '2025-11-01 16:33:55'),
(18, 5, 164, 3, 1, NULL, 1, '2025-11-01 16:57:13'),
(19, 2, 167, 7, 1, NULL, 1, '2025-11-01 17:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `study_logs`
--

CREATE TABLE `study_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `study_date` date DEFAULT NULL,
  `hours_studied` decimal(5,2) DEFAULT NULL,
  `courses_studied` int(11) DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `study_logs`
--

INSERT INTO `study_logs` (`log_id`, `user_id`, `study_date`, `hours_studied`, `courses_studied`, `activities`, `created_at`) VALUES
(1, 2, '2025-10-27', 2.40, 1, 'Course completion, quiz practice', '2025-10-30 14:21:44'),
(2, 2, '2025-11-01', 30.00, 0, 'Completed course and passed exam', '2025-11-01 17:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `study_streaks`
--

CREATE TABLE `study_streaks` (
  `streak_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `streak_count` int(11) DEFAULT 0,
  `last_study_date` date DEFAULT NULL,
  `longest_streak` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `study_streaks`
--

INSERT INTO `study_streaks` (`streak_id`, `user_id`, `streak_count`, `last_study_date`, `longest_streak`, `created_at`) VALUES
(1, 1, 0, '2025-10-30', 0, '2025-10-30 11:02:45');

-- --------------------------------------------------------

--
-- Table structure for table `trainer_requests`
--

CREATE TABLE `trainer_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainer_requests`
--

INSERT INTO `trainer_requests` (`request_id`, `user_id`, `request_message`, `status`, `admin_note`, `reviewed_by`, `created_at`, `reviewed_at`) VALUES
(1, 4, NULL, 'pending', NULL, NULL, '2025-11-06 10:46:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `oauth_provider` varchar(20) DEFAULT NULL,
  `oauth_id` varchar(255) DEFAULT NULL,
  `role` enum('trainee','trainer','admin') NOT NULL DEFAULT 'trainee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `oauth_provider`, `oauth_id`, `role`, `created_at`, `last_login`, `status`) VALUES
(1, 'Admin', 'User', 'admin@trainai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', '2025-10-30 09:57:58', NULL, 'active'),
(2, 'Rudra', 'Miyani', 'admin2002@gmail.com', '$2y$10$AW.WkfVdw5oR.unw8KBXee17iNoxbjmiOL5tzpuzTJ115qRGKlpmy', NULL, NULL, 'trainee', '2025-10-30 10:04:00', '2025-11-04 05:09:51', 'active'),
(3, 'Rudra', 'Miyani1', 'admin2003@gmail.com', '$2y$10$Mr6ivC76EiHcR0k.aV9r4uHvG71GppkOxoyPNx2VSAVL5SE.dGH1m', NULL, NULL, 'trainee', '2025-10-30 15:36:00', '2025-11-06 05:23:11', 'active'),
(4, 'Rudra1', 'Miyani', 'rudramiyani2008@gmail.com', '$2y$10$uoqQqrt18kA0p5oeVaY7Q.jyH7lZ5rGIjxjHgbqLi3V.0/8hH0s.2', 'github', '183459605', 'trainee', '2025-11-01 16:10:10', '2025-11-06 05:07:36', 'active'),
(5, 'asutosh', 'kumar', 'asutosh2008@gmail.com', '$2y$10$3kow8VS1zp3UTeV/fAEfneMLJDR.FXc64TB05On/t2bZisxBiEDpu', NULL, NULL, 'trainee', '2025-11-01 16:56:06', '2025-11-04 05:14:05', 'active'),
(6, 'Rudra', 'Miyani', 'rudramiyani2006@gmail.com', '$2y$10$PausuEuOFRjH.bj5XLdF/.GpW4JWLxRIKgtRlTRwDr3JQVSi4BT/q', 'google', '115447543651445376732', 'admin', '2025-11-01 18:27:13', '2025-11-06 05:16:37', 'active'),
(7, 'Rudra', 'Miyani', 'rudramiyani2007@gmail.com', NULL, 'google', '116116806076016517280', 'trainee', '2025-11-06 04:45:01', '2025-11-06 04:45:01', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_achievement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_achievements`
--

INSERT INTO `user_achievements` (`user_achievement_id`, `user_id`, `achievement_id`, `earned_at`) VALUES
(3, 2, 9, '2025-10-31 13:34:33'),
(4, 2, 13, '2025-10-31 13:34:33'),
(5, 2, 1, '2025-11-01 15:19:40'),
(6, 2, 2, '2025-11-01 15:19:40'),
(7, 2, 10, '2025-11-01 15:19:40'),
(8, 2, 3, '2025-11-01 15:19:40'),
(9, 2, 11, '2025-11-01 15:19:40'),
(10, 2, 4, '2025-11-01 15:19:40'),
(11, 2, 12, '2025-11-01 15:19:40'),
(12, 2, 6, '2025-11-01 15:19:40'),
(13, 2, 14, '2025-11-01 15:19:40'),
(14, 2, 8, '2025-11-01 15:19:40'),
(15, 2, 16, '2025-11-01 15:19:40'),
(16, 2, 5, '2025-11-01 15:20:45'),
(17, 2, 7, '2025-11-01 15:20:45'),
(18, 2, 15, '2025-11-01 15:20:45'),
(19, 4, 1, '2025-11-01 16:33:55'),
(20, 5, 1, '2025-11-01 16:57:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_bookmarks`
--

CREATE TABLE `user_bookmarks` (
  `bookmark_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `bookmarked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_bookmarks`
--

INSERT INTO `user_bookmarks` (`bookmark_id`, `user_id`, `course_id`, `bookmarked_at`) VALUES
(5, 2, 158, '2025-10-31 12:48:21'),
(6, 2, 142, '2025-10-31 13:11:39'),
(8, 2, 163, '2025-10-31 13:09:29'),
(9, 2, 161, '2025-10-31 13:09:30'),
(10, 2, 138, '2025-10-31 14:45:40'),
(13, 2, 136, '2025-10-31 14:45:34'),
(20, 2, 162, '2025-10-31 13:09:29'),
(31, 5, 164, '2025-11-01 17:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_certificates`
--

CREATE TABLE `user_certificates` (
  `cert_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `certificate_code` varchar(100) DEFAULT NULL,
  `issue_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `certificate_number` varchar(50) DEFAULT NULL,
  `issued_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `certificate_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_certificates`
--

INSERT INTO `user_certificates` (`cert_id`, `user_id`, `course_id`, `certificate_code`, `issue_date`, `certificate_number`, `issued_date`, `certificate_url`) VALUES
(8, 2, 161, 'CERT-6904983F0F941-20251031120639', '2025-10-31 11:06:39', 'CERT-0002-0161', '2025-10-31 11:06:39', NULL),
(9, 2, 164, 'CERT-6904C99BEB39B-20251031153715', '2025-10-31 14:37:15', 'CERT-0002-0164', '2025-10-31 14:37:15', NULL),
(10, 2, 165, 'CERT-69061BFF1D64B-20251101154103', '2025-11-01 14:41:03', 'CERT-0002-0165', '2025-11-01 14:41:03', NULL),
(11, 4, 165, 'CERT-690636735897F-20251101173355', '2025-11-01 16:33:55', 'CERT-0004-0165', '2025-11-01 16:33:55', NULL),
(12, 5, 164, 'CERT-69063BE9BD0C2-20251101175713', '2025-11-01 16:57:13', 'CERT-0005-0164', '2025-11-01 16:57:13', NULL),
(13, 2, 167, 'CERT-690642C92A236-20251101182633', '2025-11-01 17:26:33', 'CERT-0002-0167', '2025-11-01 17:26:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_challenge_progress`
--

CREATE TABLE `user_challenge_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `progress_percentage` int(11) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_interests`
--

CREATE TABLE `user_interests` (
  `interest_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `interest_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_interests`
--

INSERT INTO `user_interests` (`interest_id`, `user_id`, `interest_name`, `created_at`) VALUES
(1, 4, 'Web Development', '2025-11-01 16:10:10'),
(2, 4, 'C++ Programming', '2025-11-01 16:10:10'),
(3, 4, 'Java Programming', '2025-11-01 16:10:10'),
(4, 4, 'Machine Learning', '2025-11-01 16:10:10'),
(5, 4, 'Mobile Development', '2025-11-01 16:10:10'),
(6, 5, 'Java Programming', '2025-11-01 16:56:06'),
(7, 5, 'Data Science', '2025-11-01 16:56:06'),
(8, 5, 'UI/UX Design', '2025-11-01 16:56:06'),
(9, 5, 'Artificial Intelligence', '2025-11-01 16:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `point_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `level` int(11) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`point_id`, `user_id`, `total_points`, `level`, `last_updated`) VALUES
(3, 2, 450, 5, '2025-11-01 17:26:33'),
(11, 4, 100, 2, '2025-11-01 16:33:55'),
(13, 5, 100, 2, '2025-11-01 16:57:13'),
(19, 7, 0, 1, '2025-11-06 04:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `video_watch_logs`
--

CREATE TABLE `video_watch_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `watch_duration` decimal(10,2) NOT NULL COMMENT 'Duration in hours',
  `watch_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`achievement_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `fk_instructor` (`instructor_id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`module_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_rating` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD PRIMARY KEY (`challenge_id`);

--
-- Indexes for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `module_lessons`
--
ALTER TABLE `module_lessons`
  ADD PRIMARY KEY (`lesson_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token_hash` (`token_hash`);

--
-- Indexes for table `student_exam_responses`
--
ALTER TABLE `student_exam_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `study_logs`
--
ALTER TABLE `study_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_date` (`user_id`,`study_date`);

--
-- Indexes for table `study_streaks`
--
ALTER TABLE `study_streaks`
  ADD PRIMARY KEY (`streak_id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `trainer_requests`
--
ALTER TABLE `trainer_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_oauth` (`oauth_provider`,`oauth_id`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_achievement_id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexes for table `user_bookmarks`
--
ALTER TABLE `user_bookmarks`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `user_certificates`
--
ALTER TABLE `user_certificates`
  ADD PRIMARY KEY (`cert_id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD UNIQUE KEY `certificate_code` (`certificate_code`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `user_challenge_progress`
--
ALTER TABLE `user_challenge_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_user_challenge` (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD PRIMARY KEY (`interest_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`point_id`),
  ADD UNIQUE KEY `unique_user_points` (`user_id`);

--
-- Indexes for table `video_watch_logs`
--
ALTER TABLE `video_watch_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `watch_date` (`watch_date`),
  ADD KEY `video_watch_logs_ibfk_3` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `achievement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `course_ratings`
--
ALTER TABLE `course_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  MODIFY `challenge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `module_lessons`
--
ALTER TABLE `module_lessons`
  MODIFY `lesson_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_exam_responses`
--
ALTER TABLE `student_exam_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `study_logs`
--
ALTER TABLE `study_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `study_streaks`
--
ALTER TABLE `study_streaks`
  MODIFY `streak_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trainer_requests`
--
ALTER TABLE `trainer_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `user_achievement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_bookmarks`
--
ALTER TABLE `user_bookmarks`
  MODIFY `bookmark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `user_certificates`
--
ALTER TABLE `user_certificates`
  MODIFY `cert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_challenge_progress`
--
ALTER TABLE `user_challenge_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_interests`
--
ALTER TABLE `user_interests`
  MODIFY `interest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `point_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `video_watch_logs`
--
ALTER TABLE `video_watch_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD CONSTRAINT `course_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_ratings_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `module_lessons`
--
ALTER TABLE `module_lessons`
  ADD CONSTRAINT `module_lessons_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`module_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `exam_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_exam_responses`
--
ALTER TABLE `student_exam_responses`
  ADD CONSTRAINT `student_exam_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `student_exam_responses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `student_exam_responses_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `exam_questions` (`question_id`);

--
-- Constraints for table `study_logs`
--
ALTER TABLE `study_logs`
  ADD CONSTRAINT `study_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `study_streaks`
--
ALTER TABLE `study_streaks`
  ADD CONSTRAINT `study_streaks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `trainer_requests`
--
ALTER TABLE `trainer_requests`
  ADD CONSTRAINT `trainer_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trainer_requests_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`achievement_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_bookmarks`
--
ALTER TABLE `user_bookmarks`
  ADD CONSTRAINT `user_bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_bookmarks_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_certificates`
--
ALTER TABLE `user_certificates`
  ADD CONSTRAINT `user_certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_challenge_progress`
--
ALTER TABLE `user_challenge_progress`
  ADD CONSTRAINT `user_challenge_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_challenge_progress_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `daily_challenges` (`challenge_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_interests`
--
ALTER TABLE `user_interests`
  ADD CONSTRAINT `user_interests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_points`
--
ALTER TABLE `user_points`
  ADD CONSTRAINT `user_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `video_watch_logs`
--
ALTER TABLE `video_watch_logs`
  ADD CONSTRAINT `video_watch_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_watch_logs_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `module_lessons` (`lesson_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_watch_logs_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
