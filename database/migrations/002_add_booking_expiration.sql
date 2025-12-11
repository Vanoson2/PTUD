-- Migration: Add booking expiration feature
-- Date: 2025-12-11
-- Description: Adds expires_at column to bookings table for 10-minute payment timeout
--              Automatically cancels expired bookings to free up listings

-- Step 1: Add expires_at column
ALTER TABLE `bookings` 
ADD COLUMN `expires_at` DATETIME DEFAULT NULL 
COMMENT 'Thời điểm hết hạn thanh toán (10 phút sau khi tạo booking)'
AFTER `paid_at`;

-- Step 2: Add index for efficient expiration queries
ALTER TABLE `bookings`
ADD KEY `ix_booking_expires_at` (`expires_at`,`status`,`payment_status`);

-- Step 3: Set expires_at for existing pending bookings (10 minutes from created_at)
UPDATE `bookings` 
SET `expires_at` = DATE_ADD(`created_at`, INTERVAL 10 MINUTE)
WHERE `status` = 'pending' 
  AND `payment_status` IN ('unpaid', 'pending')
  AND `expires_at` IS NULL;

-- Verification queries (comment out when running migration)
-- SELECT booking_id, code, status, payment_status, created_at, expires_at 
-- FROM bookings 
-- WHERE status = 'pending' 
-- ORDER BY created_at DESC 
-- LIMIT 10;
