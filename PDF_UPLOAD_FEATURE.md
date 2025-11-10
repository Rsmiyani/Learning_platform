# PDF Upload Feature - Implementation Guide

## ✅ **What's Been Implemented**

Trainers can now upload **PDF file links** along with video links when creating lessons. Students can view both PDFs and videos for each lesson.

---

## **📁 Files Created/Modified**

### **Created (2 files):**
1. ✅ `database/add_pdf_link_column.sql` - SQL to add PDF columns
2. ✅ `PDF_UPLOAD_FEATURE.md` - This documentation

### **Modified (2 files):**
1. ✅ `pages/trainer/add-module.php` - Added PDF URL field and logic
2. ✅ `pages/trainee/course-modules.php` - Added PDF viewing for students

---

## **🗄️ Database Setup**

### **Step 1: Add PDF Columns to Database**

Run this SQL in your database:

```sql
-- Add pdf_url column to module_lessons table
ALTER TABLE `module_lessons` 
ADD COLUMN `pdf_url` TEXT NULL AFTER `video_url`,
ADD COLUMN `content_type` ENUM('video', 'pdf', 'both') DEFAULT 'video' AFTER `pdf_url`;

-- Update existing records
UPDATE `module_lessons` 
SET `content_type` = CASE 
    WHEN `video_url` IS NOT NULL AND `video_url` != '' THEN 'video'
    ELSE 'video'
END;
```

---

## **🎯 Features**

### **For Trainers:**

✅ **Add Video URL** - YouTube, Vimeo, or any video link  
✅ **Add PDF URL** - Google Drive, Dropbox, or any PDF link  
✅ **Add Both** - Can provide both video and PDF for same lesson  
✅ **Flexible** - At least one (video OR PDF) is required  
✅ **Visual Feedback** - See both links in lesson list  

### **For Students:**

✅ **Watch Videos** - Click "▶️ Watch" button (teal color)  
✅ **View PDFs** - Click "📄 View PDF" button (red color)  
✅ **Easy Access** - Both options displayed clearly  
✅ **Track Time** - Video watch time is tracked automatically  

---

## **📝 How Trainers Use It**

### **Step 1: Go to Add Module Page**
Navigate to: `Trainer Dashboard → My Courses → Manage Modules`

### **Step 2: Add a Lesson**
Scroll to "➕ Add Video/Lesson/PDF" form

### **Step 3: Fill Out Form**
- **Select Module:** Choose which module
- **Lesson Title:** e.g., "Introduction to Python"
- **Description:** What students will learn
- **📹 Video URL:** (Optional) YouTube or video link
- **📄 PDF URL:** (Optional) Google Drive or PDF link

### **Step 4: Submit**
Click "Add Lesson" button

---

## **🔗 Supported PDF Hosting Services**

### **Google Drive:**
```
https://drive.google.com/file/d/FILE_ID/view
```
**How to get link:**
1. Upload PDF to Google Drive
2. Right-click → Share → Copy link
3. Make sure it's set to "Anyone with the link can view"

### **Dropbox:**
```
https://www.dropbox.com/s/FILE_ID/filename.pdf?dl=0
```
**How to get link:**
1. Upload PDF to Dropbox
2. Click Share → Create link
3. Copy the link

### **OneDrive:**
```
https://onedrive.live.com/embed?cid=...&resid=...
```

### **Direct PDF Links:**
```
https://example.com/documents/file.pdf
```

---

## **📊 Content Types**

The system automatically determines content type:

| Video URL | PDF URL | Content Type |
|-----------|---------|--------------|
| ✅ Yes    | ❌ No   | `video`      |
| ❌ No     | ✅ Yes  | `pdf`        |
| ✅ Yes    | ✅ Yes  | `both`       |

---

## **🎨 Student View**

### **Lesson with Video Only:**
```
📹 Video Link:
▶️ [Video URL]  [▶️ Watch]
```

### **Lesson with PDF Only:**
```
📄 PDF Document:
📑 [PDF URL]  [📄 View PDF]
```

### **Lesson with Both:**
```
📹 Video Link:
▶️ [Video URL]  [▶️ Watch]

📄 PDF Document:
📑 [PDF URL]  [📄 View PDF]
```

---

## **🎨 Button Styling**

### **Watch Video Button:**
- **Color:** Teal gradient (#009B95)
- **Icon:** ▶️
- **Action:** Opens video + tracks watch time

### **View PDF Button:**
- **Color:** Red gradient (#dc2626)
- **Icon:** 📄
- **Action:** Opens PDF in new tab

---

## **✅ Validation Rules**

### **When Adding Lesson:**
1. ✅ **Lesson title** is required
2. ✅ **Module selection** is required
3. ✅ **At least one** of video URL or PDF URL must be provided
4. ❌ Cannot submit with both fields empty

### **Error Messages:**
- "Lesson title and module are required!"
- "Please provide at least a video URL or PDF URL!"

---

## **🧪 Testing**

### **Test Scenario 1: Add Video Only**
1. Go to Add Module page
2. Fill lesson title
3. Add only video URL
4. Submit → Should work ✅
5. Check student view → Only video button shows

### **Test Scenario 2: Add PDF Only**
1. Fill lesson title
2. Add only PDF URL
3. Submit → Should work ✅
4. Check student view → Only PDF button shows

### **Test Scenario 3: Add Both**
1. Fill lesson title
2. Add both video URL and PDF URL
3. Submit → Should work ✅
4. Check student view → Both buttons show

### **Test Scenario 4: Add Neither**
1. Fill lesson title
2. Leave both URLs empty
3. Submit → Should show error ❌
4. Error: "Please provide at least a video URL or PDF URL!"

---

## **📱 Example Use Cases**

### **Use Case 1: Programming Course**
- **Video:** Code-along tutorial on YouTube
- **PDF:** Code snippets and reference guide

### **Use Case 2: Theory Course**
- **Video:** Lecture recording
- **PDF:** Lecture slides and notes

### **Use Case 3: Math Course**
- **Video:** Problem-solving walkthrough
- **PDF:** Practice problems worksheet

### **Use Case 4: Design Course**
- **Video:** Design process tutorial
- **PDF:** Design principles cheat sheet

---

## **🔧 Customization**

### **Change Button Colors:**

In `course-modules.php`, modify the style:

```php
<!-- For PDF button -->
<button class="watch-btn" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);">
```

Change to:
- Blue: `#3b82f6 0%, #1d4ed8 100%`
- Green: `#10b981 0%, #059669 100%`
- Purple: `#8b5cf6 0%, #6d28d9 100%`

### **Change Button Text:**

```php
📄 View PDF  →  📄 Download PDF
▶️ Watch     →  ▶️ Play Video
```

---

## **🚀 Future Enhancements**

### **Possible Improvements:**

1. **File Upload**
   - Direct PDF file upload (not just links)
   - Store files on server
   - Automatic file hosting

2. **PDF Viewer**
   - Embed PDF viewer in platform
   - No need to open new tab
   - Track reading progress

3. **Download Option**
   - Allow students to download PDFs
   - Offline access
   - Print option

4. **Multiple Files**
   - Add multiple PDFs per lesson
   - Add supplementary materials
   - Add exercise files

5. **File Management**
   - Edit/delete uploaded files
   - Replace old versions
   - File size limits

---

## **📝 Important Notes**

### **PDF Link Requirements:**
- ✅ Must be publicly accessible
- ✅ Must allow viewing without login
- ✅ Should be a direct link or embed link
- ❌ Don't use download-only links

### **Google Drive Tips:**
- Set sharing to "Anyone with the link"
- Use `/view` at the end of URL for viewing
- Use `/preview` for embedded viewing

### **Security:**
- Links are sanitized with `htmlspecialchars()`
- URLs are validated as proper URLs
- XSS protection in place

---

## **🎉 Benefits**

✅ **Flexible Learning** - Students can choose video or PDF  
✅ **Better Resources** - Provide multiple formats  
✅ **Offline Study** - PDFs can be downloaded  
✅ **Comprehensive** - Video for visual, PDF for reference  
✅ **Easy to Use** - Simple interface for trainers  
✅ **Professional** - Modern, clean design  

---

## **📋 Deployment Checklist**

- [ ] Run `add_pdf_link_column.sql` to update database
- [ ] Test adding lesson with video only
- [ ] Test adding lesson with PDF only
- [ ] Test adding lesson with both
- [ ] Test student view for all scenarios
- [ ] Verify PDF links open correctly
- [ ] Verify video tracking still works
- [ ] Check mobile responsiveness

---

**Status:** ✅ Ready for Use!

Trainers can now provide both video and PDF resources for comprehensive learning! 🎉📚
