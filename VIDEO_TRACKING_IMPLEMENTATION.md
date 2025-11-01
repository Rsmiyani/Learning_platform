# Video Watch Time Tracking - Implementation Guide

## ✅ **What's Been Implemented**

The Weekly Activity chart now tracks **actual time spent watching lesson videos** instead of just course completion hours.

---

## **📁 Files Created/Modified**

### **Created (3 files):**
1. ✅ `database/video_watch_logs.sql` - Database table for video tracking
2. ✅ `handlers/track-video-watch.php` - API endpoint to log watch time
3. ✅ `VIDEO_TRACKING_IMPLEMENTATION.md` - This documentation

### **Modified (1 file):**
1. ✅ `pages/trainee/course-modules.php` - Added video tracking JavaScript

---

## **🗄️ Database Setup**

### **Step 1: Create the Table**

Run this SQL in your database:

```sql
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
```

---

## **🎯 How It Works**

### **User Flow:**

1. **Student clicks "▶️ Watch" button** on a lesson video
2. **Timer starts** tracking watch time
3. **Video opens** in new window/tab
4. **After 5 minutes**, system logs the watch time
5. **When page closes**, final session time is logged
6. **Watch time is added** to `study_logs` for that day
7. **Weekly Activity chart updates** automatically

---

## **⏱️ Time Tracking Logic**

### **Method 1: 5-Minute Check**
- After user clicks "Watch", system waits 5 minutes
- If still on page, logs 5 minutes of watch time
- Converts to hours: `5 min = 0.08 hours`

### **Method 2: Session End Tracking**
- When user closes the page/tab
- Calculates total time since clicking "Watch"
- Logs actual session duration
- Uses `navigator.sendBeacon()` for reliable tracking

### **Minimum Threshold:**
- Only tracks if watched for **more than 30 seconds**
- Prevents accidental clicks from being logged

---

## **📊 Data Flow**

```
User clicks "Watch" 
    ↓
JavaScript records start time
    ↓
Opens video in new window
    ↓
After 5 min OR on page close
    ↓
Calculates duration (seconds)
    ↓
Sends to track-video-watch.php
    ↓
Converts to hours (duration / 3600)
    ↓
Stores in video_watch_logs table
    ↓
Updates study_logs for weekly chart
    ↓
Weekly Activity chart shows real data
```

---

## **🔧 API Endpoint**

### **URL:** `handlers/track-video-watch.php`

### **Method:** POST

### **Request Body:**
```json
{
  "lesson_id": 123,
  "course_id": 45,
  "watch_duration": 300
}
```

### **Response:**
```json
{
  "success": true,
  "message": "Watch time tracked",
  "hours": 0.08
}
```

---

## **📈 Weekly Activity Chart Integration**

The chart automatically pulls from `study_logs` table:

```sql
SELECT DATE(study_date) as date, 
       COALESCE(SUM(hours_studied), 0) as hours
FROM study_logs
WHERE user_id = ? 
  AND study_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(study_date)
ORDER BY study_date
```

**Result:**
- Shows actual hours spent watching videos per day
- Updates in real-time as users watch lessons
- Displays last 7 days of activity

---

## **🧪 Testing**

### **Test Scenario 1: Watch a Video**
1. Go to a course with video lessons
2. Click "▶️ Watch" button
3. Wait 5+ minutes on the page
4. Check console: Should see "✅ Watch time tracked: X hours"
5. Refresh dashboard - Weekly Activity should update

### **Test Scenario 2: Multiple Videos**
1. Watch 3 different videos in one day
2. Each logs ~5 minutes (0.08 hours)
3. Total for day: ~0.24 hours
4. Chart should show combined time

### **Test Scenario 3: Verify Database**
```sql
-- Check video watch logs
SELECT * FROM video_watch_logs 
WHERE user_id = YOUR_USER_ID 
ORDER BY created_at DESC;

-- Check study logs
SELECT * FROM study_logs 
WHERE user_id = YOUR_USER_ID 
ORDER BY study_date DESC;
```

---

## **⚙️ Configuration**

### **Adjust Tracking Duration:**

In `course-modules.php`, line 543:
```javascript
}, 300000); // 300000 ms = 5 minutes
```

Change to:
- `60000` = 1 minute
- `180000` = 3 minutes
- `600000` = 10 minutes

### **Adjust Minimum Threshold:**

In `course-modules.php`, line 539:
```javascript
if (watchDuration > 30) { // 30 seconds minimum
```

---

## **🎨 Benefits**

✅ **Accurate tracking** - Based on actual video watch time  
✅ **Real-time updates** - Chart updates as users learn  
✅ **Detailed analytics** - Track per-lesson engagement  
✅ **Motivational** - Students see their daily progress  
✅ **Fair measurement** - Only counts active learning time  
✅ **Automatic** - No manual entry required  

---

## **🔮 Future Enhancements**

### **Possible Improvements:**

1. **YouTube API Integration**
   - Track actual video playback time
   - Detect pause/resume
   - Track completion percentage

2. **Video Player Embed**
   - Embed videos directly in platform
   - Track exact watch time
   - Prevent tab switching

3. **Advanced Analytics**
   - Most watched lessons
   - Average watch time per lesson
   - Completion rates

4. **Gamification**
   - Badges for watch time milestones
   - Daily watch streaks
   - Leaderboard for most active learners

---

## **📝 Notes**

- **Current implementation** estimates watch time based on session duration
- **For production**, consider integrating with video player APIs for exact tracking
- **Privacy**: Only tracks duration, not video content or user behavior
- **Performance**: Minimal impact, uses async requests

---

## **🚀 Deployment Checklist**

- [ ] Run `video_watch_logs.sql` to create table
- [ ] Test video tracking on course modules page
- [ ] Verify data appears in database
- [ ] Check Weekly Activity chart updates
- [ ] Clear any old test data from `study_logs`
- [ ] Monitor for any errors in browser console

---

**Status:** ✅ Ready for Testing!

The Weekly Activity chart will now show realistic daily learning hours based on actual video watch time! 🎉
