-- Add pdf_url column to module_lessons table
-- This allows trainers to upload PDF file links along with video links

ALTER TABLE `module_lessons` 
ADD COLUMN `pdf_url` TEXT NULL AFTER `video_url`,
ADD COLUMN `content_type` ENUM('video', 'pdf', 'both') DEFAULT 'video' AFTER `pdf_url`;

-- Update existing records to have content_type based on video_url
UPDATE `module_lessons` 
SET `content_type` = CASE 
    WHEN `video_url` IS NOT NULL AND `video_url` != '' THEN 'video'
    ELSE 'video'
END;
