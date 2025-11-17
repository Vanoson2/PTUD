
-- Vietnam admin units
DROP TABLE IF EXISTS `wards`;
DROP TABLE IF EXISTS `provinces`;
DROP TABLE IF EXISTS `administrative_units`;
DROP TABLE IF EXISTS `administrative_regions`;

CREATE TABLE `administrative_regions` (
  `id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `code_name` VARCHAR(255) NULL,
  `code_name_en` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `administrative_units` (
  `id` INT NOT NULL,
  `full_name` VARCHAR(255) NULL,
  `full_name_en` VARCHAR(255) NULL,
  `short_name` VARCHAR(255) NULL,
  `short_name_en` VARCHAR(255) NULL,
  `code_name` VARCHAR(255) NULL,
  `code_name_en` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `provinces` (
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `full_name_en` VARCHAR(255) NULL,
  `code_name` VARCHAR(255) NULL,
  `administrative_unit_id` INT NULL,
  PRIMARY KEY (`code`),
  KEY `idx_provinces_unit` (`administrative_unit_id`),
  CONSTRAINT `fk_provinces_unit` FOREIGN KEY (`administrative_unit_id`) REFERENCES `administrative_units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wards` (
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NULL,
  `full_name` VARCHAR(255) NULL,
  `full_name_en` VARCHAR(255) NULL,
  `code_name` VARCHAR(255) NULL,
  `province_code` VARCHAR(20) NULL,
  `administrative_unit_id` INT NULL,
  PRIMARY KEY (`code`),
  KEY `idx_wards_province` (`province_code`),
  KEY `idx_wards_unit` (`administrative_unit_id`),
  CONSTRAINT `fk_wards_unit` FOREIGN KEY (`administrative_unit_id`) REFERENCES `administrative_units` (`id`),
  CONSTRAINT `fk_wards_province` FOREIGN KEY (`province_code`) REFERENCES `provinces` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Core business tables (no `availability`)
DROP TABLE IF EXISTS `support_message`;
DROP TABLE IF EXISTS `support_ticket`;
DROP TABLE IF EXISTS `invoice`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `listing_service`;
DROP TABLE IF EXISTS `listing_amenity`;
DROP TABLE IF EXISTS `listing_image`;
DROP TABLE IF EXISTS `listing`;

DROP TABLE IF EXISTS `review`;
DROP TABLE IF EXISTS `service`;
DROP TABLE IF EXISTS `amenity`;
DROP TABLE IF EXISTS `place_type`;
DROP TABLE IF EXISTS `host_document`;
DROP TABLE IF EXISTS `host_application`;
DROP TABLE IF EXISTS `host`;
DROP TABLE IF EXISTS `user_profile`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `admin_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `role` ENUM('superadmin','manager','support') NOT NULL DEFAULT 'support',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user` (
  `user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) DEFAULT NULL,
  `is_email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verification_code_expires` DATETIME DEFAULT NULL,
  `status` ENUM('active','locked') NOT NULL DEFAULT 'active',
  `token_version` INT UNSIGNED NOT NULL DEFAULT 0,
  `verify_version` INT UNSIGNED NOT NULL DEFAULT 0,
  `reset_version` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_email` (`email`),
  UNIQUE KEY `uq_user_phone` (`phone`),
  KEY `ix_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_profile` (
  `user_id` BIGINT UNSIGNED NOT NULL,
  `job` VARCHAR(150) DEFAULT NULL,
  `hobbies` TEXT DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  `gender` ENUM('male','female','other','unknown') NOT NULL DEFAULT 'unknown',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `host` (
  `host_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `legal_name` VARCHAR(255) NOT NULL,
  `tax_code` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`host_id`),
  UNIQUE KEY `uq_host_user` (`user_id`),
  UNIQUE KEY `uq_host_tax_code` (`tax_code`),
  KEY `ix_host_status` (`status`),
  CONSTRAINT `fk_host_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `host_application` (
  `host_application_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `business_name` VARCHAR(255) NOT NULL,
  `tax_code` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by_admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `rejection_reason` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`host_application_id`),
  UNIQUE KEY `uq_tax_code` (`tax_code`),
  KEY `ix_hostapp_user_status` (`user_id`,`status`),
  KEY `fk_hostapp_admin` (`reviewed_by_admin_id`),
  CONSTRAINT `fk_hostapp_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_hostapp_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `host_document` (
  `host_document_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `host_application_id` BIGINT UNSIGNED NOT NULL,
  `doc_type` ENUM('cccd_front','cccd_back','business_license') NOT NULL,
  `file_url` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `file_size_bytes` INT UNSIGNED DEFAULT NULL,
  `file_hash_sha256` BINARY(32) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`host_document_id`),
  UNIQUE KEY `uq_hostdoc_app_type` (`host_application_id`,`doc_type`),
  KEY `ix_hostdoc_app_type` (`host_application_id`,`doc_type`),
  CONSTRAINT `fk_hostdoc_app` FOREIGN KEY (`host_application_id`) REFERENCES `host_application` (`host_application_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `place_type` (
  `place_type_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (`place_type_id`),
  UNIQUE KEY `uq_place_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `amenity` (
  `amenity_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `group_name` VARCHAR(120) DEFAULT NULL, -- bỏ 
  `description` VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (`amenity_id`),
  UNIQUE KEY `uq_amenity_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service` (
  `service_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (`service_id`),
  UNIQUE KEY `uq_service_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `listing` (
  `listing_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `host_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `ward_code` VARCHAR(20) DEFAULT NULL,
  `place_type_id` BIGINT UNSIGNED DEFAULT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `capacity` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `latitude` DECIMAL(10,7) DEFAULT NULL,
  `longitude` DECIMAL(10,7) DEFAULT NULL,
  `status` ENUM('draft','pending','active','inactive','rejected') NOT NULL DEFAULT 'draft',
  `submitted_at` DATETIME DEFAULT NULL,
  `reviewed_by_admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `rejection_reason` VARCHAR(500) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `booking_window_days` SMALLINT UNSIGNED NOT NULL DEFAULT 90,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`listing_id`),
  FULLTEXT KEY `ft_listing_text` (`title`,`description`,`address`),
  KEY `ix_listing_status` (`status`),
  KEY `ix_listing_price` (`price`),
  KEY `ix_listing_host` (`host_id`),
  KEY `ix_listing_pending` (`status`,`submitted_at`),
  KEY `ix_listing_place_type` (`place_type_id`),
  KEY `ix_listing_ward` (`ward_code`),
  KEY `ix_listing_geo` (`latitude`,`longitude`),
  KEY `fk_listing_review_admin` (`reviewed_by_admin_id`),
  CONSTRAINT `fk_listing_host` FOREIGN KEY (`host_id`) REFERENCES `host` (`host_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_listing_place_type` FOREIGN KEY (`place_type_id`) REFERENCES `place_type` (`place_type_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_listing_review_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_listing_ward` FOREIGN KEY (`ward_code`) REFERENCES `wards` (`code`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `review` (
  `review_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `ix_review_listing` (`listing_id`),
  KEY `ix_review_user` (`user_id`),
  CONSTRAINT `chk_review_rating` CHECK (`rating` BETWEEN 1 AND 5),
  CONSTRAINT `fk_review_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `review_image` (
  `image_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `review_id` BIGINT UNSIGNED NOT NULL,
  `file_url` VARCHAR(500) NOT NULL,
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `ix_review_image_review` (`review_id`, `sort_order`),
  CONSTRAINT `fk_ri_review` FOREIGN KEY (`review_id`) REFERENCES `review` (`review_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `listing_image` (
  `image_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id` BIGINT UNSIGNED NOT NULL,
  `file_url` VARCHAR(500) NOT NULL,
  `is_cover` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `ix_listing_image_cover` (`listing_id`,`is_cover`,`sort_order`),
  CONSTRAINT `fk_li_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `listing_amenity` (
  `listing_id` BIGINT UNSIGNED NOT NULL,
  `amenity_id` SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_id`,`amenity_id`),
  KEY `ix_lt_amenity` (`amenity_id`),
  CONSTRAINT `fk_lt_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lt_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenity` (`amenity_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `listing_service` (
  `listing_id` BIGINT UNSIGNED NOT NULL,
  `service_id` SMALLINT UNSIGNED NOT NULL,
  `price` DECIMAL(12,2) DEFAULT NULL,
  PRIMARY KEY (`listing_id`,`service_id`),
  KEY `ix_ld_service` (`service_id`),
  CONSTRAINT `fk_ld_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ld_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookings` (
  `booking_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `listing_id` BIGINT UNSIGNED NOT NULL,
  `guests` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `check_in` DATE NOT NULL,
  `check_out` DATE NOT NULL,
  `status` ENUM('confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `payment_method` ENUM('momo','cash','bank_transfer') DEFAULT NULL,
  `payment_status` ENUM('unpaid','pending','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_id` VARCHAR(50) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `cancelled_by` ENUM('user','admin','system') DEFAULT NULL,
  `cancel_reason` VARCHAR(500) DEFAULT NULL,
  `note` VARCHAR(500) DEFAULT NULL,
  `is_rated` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `uq_booking_code` (`code`),
  KEY `ix_booking_listing_dates` (`listing_id`,`check_in`,`check_out`),
  KEY `ix_booking_user_status` (`user_id`,`status`),
  KEY `ix_booking_rated` (`is_rated`),
  KEY `ix_booking_payment_status` (`payment_status`),
  KEY `ix_booking_payment_id` (`payment_id`),
  CONSTRAINT `fk_booking_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `invoice` (
  `invoice_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(30) NOT NULL,
  `status` ENUM('issued','voided') NOT NULL DEFAULT 'issued',
  `issued_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `service_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `uq_invoice_code` (`code`),
  UNIQUE KEY `uq_invoice_booking` (`booking_id`),
  CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_ticket` (
  `ticket_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `category` ENUM('dat_phong','tai_khoan','nha_cung_cap','khac') NOT NULL DEFAULT 'khac',
  `priority` ENUM('normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status` ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `last_message_at` DATETIME DEFAULT NULL,
  `last_message_by` ENUM('user','admin') DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`),
  KEY `ix_ticket_user_status` (`user_id`,`status`),
  KEY `ix_ticket_lastmsg` (`status`,`last_message_at`),
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_message` (
  `message_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` BIGINT UNSIGNED NOT NULL,
  `sender_type` ENUM('user','admin') NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `admin_id` BIGINT UNSIGNED DEFAULT NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `ix_msg_ticket_time` (`ticket_id`,`created_at`),
  KEY `ix_msg_sender_user` (`user_id`),
  KEY `ix_msg_sender_admin` (`admin_id`),
  CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_ticket` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_msg_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_msg_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng payment_transaction để lưu lịch sử giao dịch MoMo
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


