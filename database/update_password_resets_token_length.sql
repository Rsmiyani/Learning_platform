-- Update token column length if table already exists
-- Run this if you already created the table with varchar(64)
ALTER TABLE `password_resets` MODIFY COLUMN `token` VARCHAR(128) NOT NULL;

