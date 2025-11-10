-- Add OAuth fields to users table
ALTER TABLE `users` 
ADD COLUMN `oauth_provider` VARCHAR(20) NULL DEFAULT NULL AFTER `password`,
ADD COLUMN `oauth_id` VARCHAR(255) NULL DEFAULT NULL AFTER `oauth_provider`,
ADD INDEX `idx_oauth` (`oauth_provider`, `oauth_id`);

-- Make password nullable for OAuth users (they don't have passwords)
ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NULL DEFAULT NULL;

