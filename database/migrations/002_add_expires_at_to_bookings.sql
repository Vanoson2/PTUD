-- Migration: Add expires_at column to bookings table
-- Date: 2025-12-14
-- Description: Adds expires_at column for 10-minute booking expiration feature
--              Bookings will automatically expire if payment not completed within 10 minutes

-- Step 1: Add expires_at column
ALTER TABLE `bookings` 
ADD COLUMN `expires_at` DATETIME DEFAULT NULL 
COMMENT 'Thời điểm booking hết hạn (10 phút sau khi tạo)' 
AFTER `paid_at`;

-- Step 2: Add index for performance when checking expired bookings
ALTER TABLE `bookings`
ADD INDEX `ix_booking_expires_at` (`expires_at`);

-- Step 3: Update existing pending bookings to have expiration time
-- Set expiration to 10 minutes from creation time
UPDATE `bookings` 
SET `expires_at` = DATE_ADD(`created_at`, INTERVAL 10 MINUTE)
WHERE `status` = 'pending' 
  AND `payment_status` IN ('unpaid', 'pending')
  AND `expires_at` IS NULL;

-- Verification queries (comment out when running migration)
-- Check if column was added successfully
-- SHOW COLUMNS FROM bookings LIKE 'expires_at';

-- Check pending bookings with expiration
-- SELECT booking_id, code, status, payment_status, created_at, expires_at 
-- FROM bookings 
-- WHERE status = 'pending' 
-- ORDER BY created_at DESC 
-- LIMIT 5;
