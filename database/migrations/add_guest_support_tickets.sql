-- Migration: Add guest support for support_ticket table
-- Date: 2025-11-30
-- Description: Allow non-logged-in users to create support tickets

-- Modify user_id to allow NULL (guest users)
ALTER TABLE `support_ticket` 
MODIFY COLUMN `user_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = guest user';

-- Add columns for guest information
ALTER TABLE `support_ticket` 
ADD COLUMN `guest_name` VARCHAR(150) DEFAULT NULL COMMENT 'Tên khách vãng lai' AFTER `user_id`,
ADD COLUMN `guest_email` VARCHAR(190) DEFAULT NULL COMMENT 'Email khách vãng lai' AFTER `guest_name`,
ADD COLUMN `guest_phone` VARCHAR(30) DEFAULT NULL COMMENT 'SĐT khách vãng lai' AFTER `guest_email`;

-- Add index for guest email lookups
ALTER TABLE `support_ticket` 
ADD KEY `ix_ticket_guest_email` (`guest_email`);

-- Note: At least one of user_id or guest_email must be present
-- Application logic should enforce: user_id IS NOT NULL OR guest_email IS NOT NULL
