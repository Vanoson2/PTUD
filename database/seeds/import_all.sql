-- =====================================================
-- WEGO Complete Database Setup
-- =====================================================
-- Purpose: Import all seed data in correct order
-- Date: 2025-12-24
-- =====================================================

-- Set up environment
SET NAMES utf8mb4;
SET time_zone='+07:00';
SET FOREIGN_KEY_CHECKS=0;

-- Fix CONFIRMED bookings (already paid)
UPDATE bookings
SET 
    created_at = DATE_SUB(NOW(), INTERVAL FLOOR(1 + RAND() * 30) DAY) + INTERVAL FLOOR(RAND() * 24) HOUR,
    paid_at = DATE_ADD(created_at, INTERVAL FLOOR(5 + RAND() * 10) MINUTE),
    expires_at = NULL,
    updated_at = paid_at
WHERE status = 'confirmed' AND payment_status = 'paid';

-- Fix COMPLETED bookings
UPDATE bookings
SET 
    created_at = DATE_SUB(check_in, INTERVAL FLOOR(7 + RAND() * 30) DAY),
    paid_at = DATE_ADD(created_at, INTERVAL FLOOR(10 + RAND() * 20) MINUTE),
    expires_at = NULL,
    updated_at = check_out
WHERE status = 'completed' AND payment_status = 'paid';

-- Fix CANCELLED bookings
UPDATE bookings
SET 
    created_at = DATE_SUB(NOW(), INTERVAL FLOOR(30 + RAND() * 60) DAY),
    cancelled_at = DATE_ADD(created_at, INTERVAL FLOOR(1 + RAND() * 14) DAY),
    expires_at = NULL,
    updated_at = cancelled_at
WHERE status = 'cancelled';

-- Fix PENDING bookings
UPDATE bookings
SET 
    created_at = DATE_SUB(NOW(), INTERVAL FLOOR(1 + RAND() * 180) MINUTE),
    expires_at = DATE_ADD(created_at, INTERVAL 10 MINUTE),
    paid_at = NULL,
    updated_at = created_at
WHERE status = 'pending' AND payment_status IN ('unpaid', 'pending');

-- Auto-cancel expired pending bookings
UPDATE bookings
SET 
    status = 'cancelled',
    payment_status = 'unpaid',
    cancelled_by = 'system',
    cancel_reason = 'Hết hạn thanh toán (10 phút)',
    cancelled_at = expires_at,
    updated_at = expires_at
WHERE status = 'pending' 
  AND payment_status IN ('unpaid', 'pending')
  AND expires_at < NOW();

-- =====================================================
-- Step 5: Verification & Summary Report
-- =====================================================

SELECT '========================================' as '';
SELECT 'DATABASE IMPORT COMPLETE' as '';
SELECT '========================================' as '';

-- Users summary
SELECT 
    'Users' as table_name,
    COUNT(*) as total_records,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    (SELECT COUNT(*) FROM host WHERE status = 'approved') as hosts
FROM user;

-- Listings summary
SELECT 
    'Listings' as table_name,
    COUNT(*) as total_records,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    CONCAT(FORMAT(AVG(price), 0), ' VND') as avg_price
FROM listing;

-- Bookings summary
SELECT 
    'Bookings by Status' as report_name,
    status,
    payment_status,
    COUNT(*) as total,
    CONCAT(FORMAT(SUM(total_amount), 0), ' VND') as revenue
FROM bookings
GROUP BY status, payment_status
ORDER BY status, payment_status;

-- Recent bookings
SELECT '========================================' as '';
SELECT 'Recent Bookings (Last 10):' as '';

SELECT 
    booking_id,
    code,
    status,
    payment_status,
    DATE_FORMAT(check_in, '%Y-%m-%d') as check_in,
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as created,
    CASE 
        WHEN expires_at IS NULL THEN '-'
        WHEN expires_at > NOW() THEN CONCAT(TIMESTAMPDIFF(MINUTE, NOW(), expires_at), 'm left')
        ELSE 'Expired'
    END as expiry
FROM bookings
ORDER BY created_at DESC
LIMIT 10;

-- Reviews summary
SELECT 
    'Reviews' as table_name,
    COUNT(*) as total_reviews,
    ROUND(AVG(rating), 2) as avg_rating,
    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_reviews
FROM review;

-- Score config
SELECT '========================================' as '';
SELECT 'Trust Score Actions:' as '';

SELECT 
    action_type,
    score_change,
    description,
    is_active
FROM score_config
WHERE action_type LIKE '%cancel%'
ORDER BY score_change DESC;

SELECT '========================================' as '';
SELECT '✅ All data imported successfully!' as '';
SELECT 'You can now access the application at: http://localhost/PTUD/' as '';
SELECT '========================================' as '';

SET FOREIGN_KEY_CHECKS=1;
