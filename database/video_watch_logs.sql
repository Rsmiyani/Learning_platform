-- Create video_watch_logs table to track time spent watching lesson videos
CREATE TABLE IF NOT EXISTS `video_watch_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `watch_duration` decimal(10,2) NOT NULL COMMENT 'Duration in hours',
  `watch_date` date NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `watch_date` (`watch_date`),
  CONSTRAINT `video_watch_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `video_watch_logs_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `module_lessons` (`lesson_id`) ON DELETE CASCADE,
  CONSTRAINT `video_watch_logs_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
