-- Migration: Add 'pending' status to bookings table
-- Date: 2025-12-07
-- Description: Adds 'pending' status to bookings.status ENUM and changes default to 'pending'
--              Also adds comment to payment_id column

-- Step 1: Add 'pending' to the status ENUM (if not exists)
ALTER TABLE `bookings` 
MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') 
NOT NULL DEFAULT 'pending' 
COMMENT 'Trạng thái đơn đặt chỗ: pending-chờ thanh toán, confirmed-đã xác nhận, cancelled-đã hủy, completed-đã hoàn thành';

-- Step 2: Add comment to payment_id column
ALTER TABLE `bookings` 
MODIFY COLUMN `payment_id` VARCHAR(100) DEFAULT NULL 
COMMENT 'MoMo orderId hoặc transaction ID';

-- Step 3: Update existing bookings with NULL payment_status to 'unpaid' if they are pending
UPDATE `bookings` 
SET `payment_status` = 'unpaid' 
WHERE `status` = 'pending' AND `payment_status` IS NULL;

-- Step 4: Update existing confirmed bookings with NULL payment_status to 'paid'
UPDATE `bookings` 
SET `payment_status` = 'paid' 
WHERE `status` = 'confirmed' AND `payment_status` IS NULL;

-- Verification queries (comment out when running migration)
-- SELECT status, payment_status, COUNT(*) as count FROM bookings GROUP BY status, payment_status;
-- SHOW COLUMNS FROM bookings LIKE 'status';
-- SHOW COLUMNS FROM bookings LIKE 'payment_id';
