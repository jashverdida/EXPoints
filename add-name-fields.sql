-- Add middle_name and suffix fields to user_info table
-- Run this in phpMyAdmin to add the missing fields

ALTER TABLE `user_info`
ADD COLUMN `middle_name` varchar(50) DEFAULT NULL AFTER `first_name`,
ADD COLUMN `suffix` varchar(10) DEFAULT NULL AFTER `last_name`;

-- Verify the changes
SELECT * FROM user_info LIMIT 1;
