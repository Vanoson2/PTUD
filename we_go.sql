-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 24, 2025 lúc 06:03 AM
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
  `guests` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `status` enum('confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `total_amount` decimal(12,2) NOT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by` enum('user','admin','system') DEFAULT NULL,
  `cancel_reason` varchar(500) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
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
-- Cấu trúc bảng cho bảng `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `status` enum('issued','voided') NOT NULL DEFAULT 'issued',
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(12,2) NOT NULL,
  `service_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL
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
  `ward_id` int(10) UNSIGNED DEFAULT NULL,
  `loaicho_id` bigint(20) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
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
-- Cấu trúc bảng cho bảng `listing_image`
--

CREATE TABLE `listing_image` (
  `image_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
-- Cấu trúc bảng cho bảng `phuong_xa`
--

CREATE TABLE `phuong_xa` (
  `ward_id` int(10) UNSIGNED NOT NULL,
  `district_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `type` enum('phuong','xa','thi_tran') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quan_huyen`
--

CREATE TABLE `quan_huyen` (
  `district_id` int(10) UNSIGNED NOT NULL,
  `province_id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `type` enum('quan','huyen','thi_xa','thanh_pho') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
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
) ;

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
-- Cấu trúc bảng cho bảng `tinh_thanh`
--

CREATE TABLE `tinh_thanh` (
  `province_id` smallint(5) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(120) NOT NULL,
  `type` enum('tinh','thanh_pho_twt') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
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

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_listing_with_address`
-- (See below for the actual view)
--
CREATE TABLE `v_listing_with_address` (
`listing_id` bigint(20) unsigned
,`nhacungcap_id` bigint(20) unsigned
,`tieu_de` varchar(255)
,`mo_ta` text
,`dia_chi` varchar(255)
,`latitude` decimal(10,7)
,`longitude` decimal(10,7)
,`ward_id` int(10) unsigned
,`ward_name` varchar(120)
,`ward_type` enum('phuong','xa','thi_tran')
,`district_id` int(10) unsigned
,`district_name` varchar(120)
,`district_type` enum('quan','huyen','thi_xa','thanh_pho')
,`province_id` smallint(5) unsigned
,`province_name` varchar(120)
,`province_type` enum('tinh','thanh_pho_twt')
,`loaicho_id` bigint(20) unsigned
,`price` decimal(12,2)
,`trang_thai` enum('draft','pending','active','inactive','rejected')
,`submitted_at` datetime
,`reviewed_by_admin_id` bigint(20) unsigned
,`reviewed_at` datetime
,`rejection_reason` varchar(500)
,`approved_at` datetime
,`booking_window_days` smallint(5) unsigned
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_listing_with_address`
--
DROP TABLE IF EXISTS `v_listing_with_address`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_listing_with_address`  AS SELECT `l`.`listing_id` AS `listing_id`, `l`.`nhacungcap_id` AS `nhacungcap_id`, `l`.`tieu_de` AS `tieu_de`, `l`.`mo_ta` AS `mo_ta`, `l`.`dia_chi` AS `dia_chi`, `l`.`latitude` AS `latitude`, `l`.`longitude` AS `longitude`, `w`.`ward_id` AS `ward_id`, `w`.`name` AS `ward_name`, `w`.`type` AS `ward_type`, `d`.`district_id` AS `district_id`, `d`.`name` AS `district_name`, `d`.`type` AS `district_type`, `p`.`province_id` AS `province_id`, `p`.`name` AS `province_name`, `p`.`type` AS `province_type`, `l`.`loaicho_id` AS `loaicho_id`, `l`.`price` AS `price`, `l`.`trang_thai` AS `trang_thai`, `l`.`submitted_at` AS `submitted_at`, `l`.`reviewed_by_admin_id` AS `reviewed_by_admin_id`, `l`.`reviewed_at` AS `reviewed_at`, `l`.`rejection_reason` AS `rejection_reason`, `l`.`approved_at` AS `approved_at`, `l`.`booking_window_days` AS `booking_window_days`, `l`.`created_at` AS `created_at`, `l`.`updated_at` AS `updated_at` FROM (((`listing` `l` left join `phuong_xa` `w` on(`l`.`ward_id` = `w`.`ward_id`)) left join `quan_huyen` `d` on(`w`.`district_id` = `d`.`district_id`)) left join `tinh_thanh` `p` on(`d`.`province_id` = `p`.`province_id`)) ;

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
  ADD UNIQUE KEY `uq_av_listing_day` (`listing_id`,`ngay`),
  ADD KEY `ix_av_day_list_booked` (`ngay`,`listing_id`,`booked`);

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
  ADD UNIQUE KEY `uq_hostdoc_app_type` (`host_application_id`,`doc_type`),
  ADD KEY `ix_hostdoc_app_type` (`host_application_id`,`doc_type`);

--
-- Chỉ mục cho bảng `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `uq_invoice_code` (`code`),
  ADD UNIQUE KEY `uq_invoice_booking` (`booking_id`);

--
-- Chỉ mục cho bảng `listing`
--
ALTER TABLE `listing`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `ix_listing_status` (`trang_thai`),
  ADD KEY `ix_listing_price` (`price`),
  ADD KEY `ix_listing_host` (`nhacungcap_id`),
  ADD KEY `ix_listing_pending` (`trang_thai`,`submitted_at`),
  ADD KEY `ix_listing_loaicho` (`loaicho_id`),
  ADD KEY `ix_listing_ward` (`ward_id`),
  ADD KEY `ix_listing_geo` (`latitude`,`longitude`),
  ADD KEY `fk_listing_review_admin` (`reviewed_by_admin_id`);
ALTER TABLE `listing` ADD FULLTEXT KEY `ft_listing_text` (`tieu_de`,`mo_ta`,`dia_chi`);

--
-- Chỉ mục cho bảng `listing_dichvu`
--
ALTER TABLE `listing_dichvu`
  ADD PRIMARY KEY (`listing_id`,`dichvu_id`),
  ADD KEY `ix_ld_dichvu` (`dichvu_id`);

--
-- Chỉ mục cho bảng `listing_image`
--
ALTER TABLE `listing_image`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `ix_listing_image_cover` (`listing_id`,`is_cover`,`sort_order`);

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
  ADD UNIQUE KEY `uq_ncc_mst` (`mst`),
  ADD KEY `ix_nhacungcap_status` (`trang_thai`);

--
-- Chỉ mục cho bảng `phuong_xa`
--
ALTER TABLE `phuong_xa`
  ADD PRIMARY KEY (`ward_id`),
  ADD UNIQUE KEY `uq_ward_in_district` (`district_id`,`name`),
  ADD KEY `ix_ward_district` (`district_id`);

--
-- Chỉ mục cho bảng `quan_huyen`
--
ALTER TABLE `quan_huyen`
  ADD PRIMARY KEY (`district_id`),
  ADD UNIQUE KEY `uq_district_in_province` (`province_id`,`name`),
  ADD KEY `ix_district_province` (`province_id`);

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
-- Chỉ mục cho bảng `tinh_thanh`
--
ALTER TABLE `tinh_thanh`
  ADD PRIMARY KEY (`province_id`),
  ADD UNIQUE KEY `uq_province_code` (`code`),
  ADD UNIQUE KEY `uq_province_name` (`name`);

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
-- AUTO_INCREMENT cho bảng `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `listing`
--
ALTER TABLE `listing`
  MODIFY `listing_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `listing_image`
--
ALTER TABLE `listing_image`
  MODIFY `image_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT cho bảng `phuong_xa`
--
ALTER TABLE `phuong_xa`
  MODIFY `ward_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `quan_huyen`
--
ALTER TABLE `quan_huyen`
  MODIFY `district_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT cho bảng `tinh_thanh`
--
ALTER TABLE `tinh_thanh`
  MODIFY `province_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Các ràng buộc cho bảng `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `listing`
--
ALTER TABLE `listing`
  ADD CONSTRAINT `fk_listing_host` FOREIGN KEY (`nhacungcap_id`) REFERENCES `nhacungcap` (`nhacungcap_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listing_loaicho` FOREIGN KEY (`loaicho_id`) REFERENCES `loaicho` (`loaicho_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listing_review_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_listing_ward` FOREIGN KEY (`ward_id`) REFERENCES `phuong_xa` (`ward_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `listing_dichvu`
--
ALTER TABLE `listing_dichvu`
  ADD CONSTRAINT `fk_ld_dichvu` FOREIGN KEY (`dichvu_id`) REFERENCES `dichvu` (`dichvu_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ld_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `listing_image`
--
ALTER TABLE `listing_image`
  ADD CONSTRAINT `fk_li_listing` FOREIGN KEY (`listing_id`) REFERENCES `listing` (`listing_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Các ràng buộc cho bảng `phuong_xa`
--
ALTER TABLE `phuong_xa`
  ADD CONSTRAINT `fk_ward_district` FOREIGN KEY (`district_id`) REFERENCES `quan_huyen` (`district_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `quan_huyen`
--
ALTER TABLE `quan_huyen`
  ADD CONSTRAINT `fk_district_province` FOREIGN KEY (`province_id`) REFERENCES `tinh_thanh` (`province_id`) ON UPDATE CASCADE;

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
