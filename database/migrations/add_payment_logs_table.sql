-- Migration: Add payment_logs table for audit trail
-- Date: 2025-12-06

CREATE TABLE IF NOT EXISTS `payment_logs` (
  `log_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED DEFAULT NULL,
  `transaction_id` BIGINT UNSIGNED DEFAULT NULL,
  `event_type` ENUM('init','ipn_received','ipn_verified','return_received','return_verified','retry','query','error') NOT NULL,
  `request_data` JSON DEFAULT NULL,
  `response_data` JSON DEFAULT NULL,
  `result_code` INT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `ix_logs_booking` (`booking_id`),
  KEY `ix_logs_transaction` (`transaction_id`),
  KEY `ix_logs_event_time` (`event_type`, `created_at`),
  KEY `ix_logs_result` (`result_code`),
  KEY `ix_logs_created_at` (`created_at` DESC),
  CONSTRAINT `fk_logs_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_logs_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `payment_transaction` (`transaction_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

