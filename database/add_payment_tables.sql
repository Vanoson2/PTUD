-- SQL để thêm bảng payment_transaction và cập nhật bookings
-- Chạy file này sau khi đã chạy we_go.sql

-- Thêm bảng payment_transaction
CREATE TABLE `payment_transaction` (
  `transaction_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `partner_code` VARCHAR(50) NOT NULL DEFAULT 'MOMOBKUN20180529',
  `order_id` VARCHAR(50) NOT NULL,
  `request_id` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `order_info` VARCHAR(255) NOT NULL,
  `order_type` VARCHAR(50) DEFAULT 'momo_wallet',
  `trans_id` VARCHAR(50) DEFAULT NULL,
  `result_code` INT DEFAULT NULL,
  `message` VARCHAR(500) DEFAULT NULL,
  `pay_type` VARCHAR(50) DEFAULT NULL,
  `response_time` BIGINT DEFAULT NULL,
  `extra_data` TEXT DEFAULT NULL,
  `signature` VARCHAR(255) DEFAULT NULL,
  `payment_url` VARCHAR(500) DEFAULT NULL,
  `deeplink` VARCHAR(500) DEFAULT NULL,
  `qr_code_url` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `uq_order_id` (`order_id`),
  UNIQUE KEY `uq_booking_transaction` (`booking_id`),
  KEY `ix_trans_status` (`status`),
  KEY `ix_trans_result` (`result_code`),
  CONSTRAINT `fk_trans_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm các cột vào bảng bookings để hỗ trợ thanh toán
ALTER TABLE `bookings` 
ADD COLUMN `payment_method` ENUM('momo','cash','bank_transfer') DEFAULT NULL AFTER `status`,
ADD COLUMN `payment_status` ENUM('unpaid','pending','paid','refunded') NOT NULL DEFAULT 'unpaid' AFTER `payment_method`,
ADD COLUMN `payment_id` VARCHAR(50) DEFAULT NULL AFTER `payment_status`,
ADD COLUMN `paid_at` DATETIME DEFAULT NULL AFTER `payment_id`;

-- Thêm index
ALTER TABLE `bookings`
ADD INDEX `ix_booking_payment_status` (`payment_status`),
ADD INDEX `ix_booking_payment_id` (`payment_id`);

-- Cập nhật các booking cũ đã confirmed sang paid
UPDATE `bookings` 
SET `payment_status` = 'paid', 
    `payment_method` = 'cash',
    `paid_at` = `created_at`
WHERE `status` = 'confirmed' OR `status` = 'completed';
