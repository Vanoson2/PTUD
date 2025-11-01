-- Tạo bảng support_tickets để lưu các yêu cầu hỗ trợ

CREATE TABLE IF NOT EXISTS `support_tickets` (
  `ticket_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `ticket_code` VARCHAR(20) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `category` ENUM('account', 'booking', 'payment', 'technical', 'other') NOT NULL DEFAULT 'other',
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
  `admin_response` TEXT DEFAULT NULL,
  `admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `responded_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`),
  UNIQUE KEY `uq_ticket_code` (`ticket_code`),
  KEY `idx_user_status` (`user_id`, `status`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chèn dữ liệu mẫu (optional)
-- INSERT INTO support_tickets (user_id, ticket_code, subject, message, category, priority) 
-- VALUES (1, 'TK20250001', 'Không thể đăng nhập', 'Tôi quên mật khẩu và không nhận được email đặt lại', 'account', 'high');
