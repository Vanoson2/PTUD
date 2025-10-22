-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 22, 2025 lúc 05:37 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `we_go`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
--

CREATE TABLE `admin` (
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `taikhoan` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `role` enum('superadmin','manager','support') NOT NULL DEFAULT 'support'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `availability`
--

CREATE TABLE `availability` (
  `availability_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `ngay` date NOT NULL,
  `booked` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `nguoidung_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `status` enum('confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `total_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dichvu`
--

CREATE TABLE `dichvu` (
  `dichvu_id` smallint(5) UNSIGNED NOT NULL,
  `ten` varchar(120) NOT NULL,
  `mo_ta` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `host_application`
--

CREATE TABLE `host_application` (
  `host_application_id` bigint(20) UNSIGNED NOT NULL,
  `nguoidung_id` bigint(20) UNSIGNED NOT NULL,
  `ten_doanh_nghiep` varchar(255) NOT NULL,
  `ma_so_thue` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `host_document`
--

CREATE TABLE `host_document` (
  `host_document_id` bigint(20) UNSIGNED NOT NULL,
  `host_application_id` bigint(20) UNSIGNED NOT NULL,
  `doc_type` enum('cccd_front','cccd_back','business_license') NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size_bytes` int(10) UNSIGNED DEFAULT NULL,
  `file_hash_sha256` binary(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `listing`
--

CREATE TABLE `listing` (
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `nhacungcap_id` bigint(20) UNSIGNED NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `dia_chi` varchar(255) NOT NULL,
  `tinhthanh_id` smallint(5) UNSIGNED DEFAULT NULL,
  `loaicho_id` bigint(20) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `trang_thai` enum('draft','pending','active','inactive','rejected') NOT NULL DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_by_admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(500) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `booking_window_days` smallint(5) UNSIGNED NOT NULL DEFAULT 90,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `listing_dichvu`
--

CREATE TABLE `listing_dichvu` (
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `dichvu_id` smallint(5) UNSIGNED NOT NULL,
  `gia` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `listing_tiennghi`
--

CREATE TABLE `listing_tiennghi` (
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `tiennghi_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaicho`
--

CREATE TABLE `loaicho` (
  `loaicho_id` bigint(20) UNSIGNED NOT NULL,
  `ten` varchar(120) NOT NULL,
  `mo_ta` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `nguoidung_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `ho_ten` varchar(150) DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','locked') NOT NULL DEFAULT 'active',
  `token_version` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `verify_version` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reset_version` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `nhacungcap_id` bigint(20) UNSIGNED NOT NULL,
  `nguoidung_id` bigint(20) UNSIGNED NOT NULL,
  `ten_phap_ly` varchar(255) NOT NULL,
  `mst` varchar(50) DEFAULT NULL,
  `trang_thai` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `support_message`
--

CREATE TABLE `support_message` (
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `nguoidung_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `noi_dung` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `support_ticket`
--

CREATE TABLE `support_ticket` (
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `nguoidung_id` bigint(20) UNSIGNED NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `danh_muc` enum('dat_phong','tai_khoan','nha_cung_cap','khac') NOT NULL DEFAULT 'khac',
  `muc_do` enum('normal','high','urgent') NOT NULL DEFAULT 'normal',
  `trang_thai` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `last_message_at` datetime DEFAULT NULL,
  `last_message_by` enum('user','admin') DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tiennghi`
--

CREATE TABLE `tiennghi` (
  `tiennghi_id` smallint(5) UNSIGNED NOT NULL,
  `ten` varchar(120) NOT NULL,
  `nhom` varchar(120) DEFAULT NULL,
  `mo_ta` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tinhthanh`
--

CREATE TABLE `tinhthanh` (
  `tinhthanh_id` smallint(5) UNSIGNED NOT NULL,
  `ten` varchar(100) NOT NULL,
  `loai` enum('tinh','thanh_pho') NOT NULL DEFAULT 'tinh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_profile`
--

CREATE TABLE `user_profile` (
  `nguoidung_id` bigint(20) UNSIGNED NOT NULL,
  `cong_viec` varchar(150) DEFAULT NULL,
  `so_thich` text DEFAULT NULL,
  `noi_song` varchar(150) DEFAULT NULL,
  `gioi_tinh` enum('male','female','other','unknown') NOT NULL DEFAULT 'unknown',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `uq_admin_taikhoan` (`taikhoan`);

--
-- Chỉ mục cho bảng `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD UNIQUE KEY `uq_av_listing_day` (`listing_id`,`ngay`);

--
-- Chỉ mục cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `uq_booking_code` (`code`),
  ADD KEY `ix_booking_listing_dates` (`listing_id`,`check_in`,`check_out`),
  ADD KEY `ix_booking_user_status` (`nguoidung_id`,`status`);

--
-- Chỉ mục cho bảng `dichvu`
--
ALTER TABLE `dichvu`
  ADD PRIMARY KEY (`dichvu_id`),
  ADD UNIQUE KEY `uq_dichvu_ten` (`ten`);

--
-- Chỉ mục cho bảng `host_application`
--
ALTER TABLE `host_application`
  ADD PRIMARY KEY (`host_application_id`),
  ADD KEY `ix_hostapp_user_status` (`nguoidung_id`,`status`),
  ADD KEY `fk_hostapp_admin` (`reviewed_by_admin_id`);

--
-- Chỉ mục cho bảng `host_document`
--
ALTER TABLE `host_document`
  ADD PRIMARY KEY (`host_document_id`),
  ADD KEY `ix_hostdoc_app_type` (`host_application_id`,`doc_type`);

--
-- Chỉ mục cho bảng `listing`
--
ALTER TABLE `listing`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `ix_listing_status` (`trang_thai`),
  ADD KEY `ix_listing_price` (`price`),
  ADD KEY `ix_listing_host` (`nhacungcap_id`),
  ADD KEY `ix_listing_pending` (`trang_thai`,`submitted_at`),
  ADD KEY `ix_listing_tinhthanh_stt` (`tinhthanh_id`,`trang_thai`),
  ADD KEY `ix_listing_loaicho` (`loaicho_id`),
  ADD KEY `fk_listing_review_admin` (`reviewed_by_admin_id`);

--
-- Chỉ mục cho bảng `listing_dichvu`
--
ALTER TABLE `listing_dichvu`
  ADD PRIMARY KEY (`listing_id`,`dichvu_id`),
  ADD KEY `ix_ld_dichvu` (`dichvu_id`);

--
-- Chỉ mục cho bảng `listing_tiennghi`
--
ALTER TABLE `listing_tiennghi`
  ADD PRIMARY KEY (`listing_id`,`tiennghi_id`),
  ADD KEY `ix_lt_tiennghi` (`tiennghi_id`);

--
-- Chỉ mục cho bảng `loaicho`
--
ALTER TABLE `loaicho`
  ADD PRIMARY KEY (`loaicho_id`),
  ADD UNIQUE KEY `uq_loaicho_ten` (`ten`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`nguoidung_id`),
  ADD UNIQUE KEY `uq_user_email` (`email`),
  ADD UNIQUE KEY `uq_user_phone` (`phone`),
  ADD KEY `ix_user_status` (`status`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`nhacungcap_id`),
  ADD UNIQUE KEY `uq_nhacungcap_nguoidung` (`nguoidung_id`),
  ADD KEY `ix_nhacungcap_status` (`trang_thai`);

--
-- Chỉ mục cho bảng `support_message`
--
ALTER TABLE `support_message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `ix_msg_ticket_time` (`ticket_id`,`created_at`),
  ADD KEY `ix_msg_sender_user` (`nguoidung_id`),
  ADD KEY `ix_msg_sender_admin` (`admin_id`);

--
-- Chỉ mục cho bảng `support_ticket`
--
ALTER TABLE `support_ticket`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `ix_ticket_user_status` (`nguoidung_id`,`trang_thai`),
  ADD KEY `ix_ticket_lastmsg` (`trang_thai`,`last_message_at`);

--
-- Chỉ mục cho bảng `tiennghi`
--
ALTER TABLE `tiennghi`
  ADD PRIMARY KEY (`tiennghi_id`),
  ADD UNIQUE KEY `uq_tiennghi_ten` (`ten`);

--
-- Chỉ mục cho bảng `tinhthanh`
--
ALTER TABLE `tinhthanh`
  ADD PRIMARY KEY (`tinhthanh_id`),
  ADD UNIQUE KEY `uq_tinhthanh_ten` (`ten`);

--
-- Chỉ mục cho bảng `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`nguoidung_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `availability`
--
ALTER TABLE `availability`
  MODIFY `availability_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `dichvu`
--
ALTER TABLE `dichvu`
  MODIFY `dichvu_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `host_application`
--
ALTER TABLE `host_application`
  MODIFY `host_application_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `host_document`
--
ALTER TABLE `host_document`
  MODIFY `host_document_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `listing`
--
ALTER TABLE `listing`
  MODIFY `listing_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `loaicho`
--
ALTER TABLE `loaicho`
  MODIFY `loaicho_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `nguoidung_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  MODIFY `nhacungcap_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `support_message`
--
ALTER TABLE `support_message`
  MODIFY `message_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `support_ticket`
--
ALTER TABLE `support_ticket`
  MODIFY `ticket_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tiennghi`
--
ALTER TABLE `tiennghi`
  MODIFY `tiennghi_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tinhthanh`
--
ALTER TABLE `tinhthanh`
  MODIFY `tinhthanh_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `fk_av_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`nguoidung_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `host_application`
--
ALTER TABLE `host_application`
  ADD CONSTRAINT `fk_hostapp_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hostapp_user` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`nguoidung_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `host_document`
--
ALTER TABLE `host_document`
  ADD CONSTRAINT `fk_hostdoc_app` FOREIGN KEY (`host_application_id`) REFERENCES `host_application` (`host_application_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `listing`
--
ALTER TABLE `listing`
  ADD CONSTRAINT `fk_listing_host` FOREIGN KEY (`nhacungcap_id`) REFERENCES `nhacungcap` (`nhacungcap_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listing_loaicho` FOREIGN KEY (`loaicho_id`) REFERENCES `loaicho` (`loaicho_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listing_review_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listing_tinhthanh` FOREIGN KEY (`tinhthanh_id`) REFERENCES `tinhthanh` (`tinhthanh_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `listing_dichvu`
--
ALTER TABLE `listing_dichvu`
  ADD CONSTRAINT `fk_ld_dichvu` FOREIGN KEY (`dichvu_id`) REFERENCES `dichvu` (`dichvu_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ld_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `listing_tiennghi`
--
ALTER TABLE `listing_tiennghi`
  ADD CONSTRAINT `fk_lt_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lt_tiennghi` FOREIGN KEY (`tiennghi_id`) REFERENCES `tiennghi` (`tiennghi_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD CONSTRAINT `fk_ncc_nguoidung` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`nguoidung_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `support_message`
--
ALTER TABLE `support_message`
  ADD CONSTRAINT `fk_msg_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_ticket` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_user` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`nguoidung_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `support_ticket`
--
ALTER TABLE `support_ticket`
  ADD CONSTRAINT `fk_ticket_user` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`nguoidung_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `fk_profile_nguoidung` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`nguoidung_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
