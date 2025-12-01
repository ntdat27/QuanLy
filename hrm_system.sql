-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 01, 2025 lúc 08:00 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `hrm_system`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','late') DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `check_in`, `check_out`, `status`) VALUES
(1, 2, '2025-12-01', '07:55:00', '17:05:00', 'present'),
(2, 4, '2025-12-01', '08:45:00', '17:30:00', 'late'),
(3, 3, '2025-12-01', '08:00:00', '17:00:00', 'present'),
(4, 8, '2025-12-01', '07:55:00', '17:05:00', 'present'),
(5, 9, '2025-12-01', '08:00:00', '17:00:00', 'present'),
(6, 10, '2025-12-01', '08:35:00', '17:30:00', 'late'),
(7, 11, '2025-12-01', '07:50:00', '17:00:00', 'present'),
(8, 12, '2025-12-01', '08:05:00', '17:10:00', 'present'),
(9, 13, '2025-12-01', '07:59:00', '17:00:00', 'present'),
(10, 14, '2025-12-01', '08:00:00', '17:00:00', 'present'),
(11, 15, '2025-12-01', '08:15:00', '17:15:00', 'late'),
(12, 16, '2025-12-01', '07:45:00', '16:45:00', 'present'),
(13, 17, '2025-12-01', '08:00:00', '17:00:00', 'present'),
(14, 8, '2025-12-02', '07:55:00', '17:05:00', 'present'),
(15, 10, '2025-12-02', '08:00:00', '17:00:00', 'present');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `score` varchar(50) DEFAULT NULL,
  `image_proof` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `schedule` varchar(255) NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `schedule`, `room`, `teacher_id`, `student_count`) VALUES
(1, 'IELTS K12', 'T2-T4-T6 19h', '301', 4, 0),
(2, 'IELTS Foundation K14', 'Thứ 2 - Thứ 4 (18:00 - 19:30)', 'Phòng 101', 5, 15),
(3, 'IELTS Speaking Master', 'Thứ 3 - Thứ 5 (19:30 - 21:00)', 'Phòng 202', 5, 12),
(4, 'Intensive Writing', 'Thứ 7 - CN (09:00 - 11:00)', 'Phòng Online Zoom', 4, 20),
(5, 'Giao tiếp nâng cao', 'Thứ 2 - Thứ 6 (18:00 - 19:30)', 'Phòng 305', 4, 8);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'img/default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'Phòng Marketing', 'Quảng bá thương hiệu & Truyền thông.', 'img/Strategy.jpg', '2025-12-01 14:21:18'),
(2, 'Hành chính - Kế toán', 'Quản lý tài chính, thu chi & CSVC.', 'img/Finance.jpg', '2025-12-01 14:21:18'),
(3, 'Phòng Đào Tạo', 'Quản lý chất lượng dạy & học (Giáo viên).', 'img/vision1.jpg', '2025-12-01 14:21:18'),
(4, 'Phòng Tuyển Sinh', 'Tư vấn bán hàng & CSKH.', 'img/Operations.jpg', '2025-12-01 14:21:18'),
(5, 'Phòng Nhân Sự', 'Tuyển dụng, C&B, Đào tạo nội bộ.', 'img/HR.jpg', '2025-12-01 14:21:18');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `employee_details`
--

CREATE TABLE `employee_details` (
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Nam','Nữ','Khác') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Việt Nam',
  `marital_status` enum('Độc thân','Đã kết hôn','Ly hôn') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `hometown` text DEFAULT NULL,
  `zalo` varchar(20) DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `certificate_type` varchar(50) DEFAULT 'None',
  `certificate_score` decimal(5,1) DEFAULT 0.0,
  `edu_proof` varchar(255) DEFAULT NULL,
  `cert_proof` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `contract_type` enum('Full-time','Part-time','CTV') DEFAULT 'Full-time',
  `biography` text DEFAULT NULL,
  `criminal_record_status` enum('Trong sạch','Có tiền án','Đang xác minh') DEFAULT 'Đang xác minh',
  `criminal_record_number` varchar(50) DEFAULT NULL,
  `criminal_record_date` date DEFAULT NULL,
  `criminal_record_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `employee_details`
--

INSERT INTO `employee_details` (`user_id`, `phone`, `dob`, `gender`, `nationality`, `marital_status`, `address`, `current_address`, `hometown`, `zalo`, `education_level`, `major`, `certificate_type`, `certificate_score`, `edu_proof`, `cert_proof`, `start_date`, `contract_type`, `biography`, `criminal_record_status`, `criminal_record_number`, `criminal_record_date`, `criminal_record_file`) VALUES
(2, '0901000111', NULL, NULL, 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Thạc sĩ', 'Quản lý giáo dục', 'IELTS', 7.5, NULL, NULL, '2018-01-01', 'Full-time', '10 năm kinh nghiệm quản lý.', 'Đang xác minh', NULL, NULL, NULL),
(3, '0902000222', '0000-00-00', 'Nam', 'Việt Nam', 'Độc thân', NULL, 'Ô diên hà NỘI', 'Haà Nội', '', 'Đại học', 'Kế toán', 'TOEIC', 700.0, NULL, NULL, '2021-05-01', 'Full-time', 'Kế toán viên chuyên nghiệp.', 'Đang xác minh', NULL, NULL, NULL),
(4, '0903000333', NULL, NULL, 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Thạc sĩ', 'TESOL', 'None', 0.0, NULL, NULL, '2023-01-15', 'Full-time', 'Giáo viên bản ngữ.', 'Đang xác minh', NULL, NULL, NULL),
(5, '0904000444', NULL, NULL, 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', 'Sư phạm Anh', 'IELTS', 8.0, NULL, NULL, '2022-09-01', 'Full-time', 'Giáo viên chuyên IELTS.', 'Đang xác minh', NULL, NULL, NULL),
(6, '0905000555', NULL, NULL, 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Cao đẳng', 'Kinh tế', 'None', 0.0, NULL, NULL, '2024-02-01', 'Full-time', 'Tư vấn viên.', 'Đang xác minh', NULL, NULL, NULL),
(7, '0906000666', NULL, NULL, 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', 'Marketing', 'TOEIC', 600.0, NULL, NULL, '2023-11-01', 'Full-time', 'Content Creator.', 'Đang xác minh', NULL, NULL, NULL),
(8, '0912345678', '1995-05-10', 'Nữ', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'IELTS', 7.5, NULL, NULL, '2025-10-01', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(9, '0912345679', '1990-08-20', 'Nam', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Thạc sĩ', NULL, 'IELTS', 8.0, NULL, NULL, '2025-10-01', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(10, '0912345680', '1998-12-15', 'Nữ', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Cao đẳng', NULL, 'TOEIC', 650.0, NULL, NULL, '2025-10-05', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(11, '0912345681', '1996-03-22', 'Nam', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'None', 0.0, NULL, NULL, '2025-10-05', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(12, '0912345682', '1997-07-30', 'Nữ', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'None', 0.0, NULL, NULL, '2025-10-10', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(13, '0912345683', '1994-11-11', 'Nam', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'TOEIC', 700.0, NULL, NULL, '2025-10-10', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(14, '0912345684', '1992-02-14', 'Nữ', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'None', 0.0, NULL, NULL, '2025-09-15', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(15, '0912345685', '1993-09-09', 'Nam', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'None', 0.0, NULL, NULL, '2025-09-15', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(16, '0912345686', '1999-06-01', 'Nữ', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Đại học', NULL, 'IELTS', 7.0, NULL, NULL, '2025-11-01', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL),
(17, '0912345687', '1995-04-30', 'Nam', 'Việt Nam', NULL, NULL, NULL, NULL, NULL, 'Cao đẳng', NULL, 'None', 0.0, NULL, NULL, '2025-11-01', 'Full-time', NULL, 'Đang xác minh', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `amount` decimal(15,0) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `expenses`
--

INSERT INTO `expenses` (`id`, `title`, `amount`, `category`, `expense_date`, `created_by`, `receipt_image`, `created_at`) VALUES
(1, 'In tờ rơi', 2000000, 'Marketing', '2025-12-01', 3, NULL, '2025-12-01 04:33:16'),
(2, 'Mua máy chiếu', 8500000, 'Cơ sở vật chất', '2025-11-28', 3, NULL, '2025-12-01 04:33:16'),
(3, 'Tổ chức Trung thu', 5000000, 'Sự kiện', '2025-09-15', 3, NULL, '2025-12-01 04:35:40'),
(4, 'Sửa chữa máy lạnh', 1200000, 'Cơ sở vật chất', '2025-09-20', 3, NULL, '2025-12-01 04:35:40'),
(5, 'Tiền điện nước tháng 9', 3500000, 'Điện nước', '2025-10-05', 3, NULL, '2025-12-01 04:35:40'),
(6, 'Quảng cáo Facebook tháng 10', 6000000, 'Marketing', '2025-10-10', 3, NULL, '2025-12-01 04:35:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `insurance`
--

CREATE TABLE `insurance` (
  `user_id` int(11) NOT NULL,
  `social_status` enum('Có đóng','Không đóng') DEFAULT 'Không đóng',
  `social_book_number` varchar(50) DEFAULT NULL,
  `health_card_number` varchar(50) DEFAULT NULL,
  `hospital_reg` varchar(100) DEFAULT NULL,
  `social_salary_base` decimal(15,0) DEFAULT NULL,
  `commercial_pkg_name` varchar(100) DEFAULT NULL,
  `commercial_contract_num` varchar(50) DEFAULT NULL,
  `commercial_expiry` date DEFAULT NULL,
  `insurance_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `insurance`
--

INSERT INTO `insurance` (`user_id`, `social_status`, `social_book_number`, `health_card_number`, `hospital_reg`, `social_salary_base`, `commercial_pkg_name`, `commercial_contract_num`, `commercial_expiry`, `insurance_file`) VALUES
(8, 'Có đóng', 'BH008', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Có đóng', 'BH009', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Không đóng', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'Không đóng', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Có đóng', 'BH012', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Có đóng', 'BH013', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Có đóng', 'BH014', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Có đóng', 'BH015', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Không đóng', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'Không đóng', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `labor_contracts`
--

CREATE TABLE `labor_contracts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contract_number` varchar(50) DEFAULT NULL,
  `contract_type` enum('Thử việc','Chính thức','Part-time') DEFAULT 'Thử việc',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `base_salary` decimal(15,0) DEFAULT 0,
  `hourly_rate` decimal(15,0) DEFAULT 0,
  `allowance` decimal(15,0) DEFAULT 0,
  `bank_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `tax_code` varchar(50) DEFAULT NULL,
  `contract_file` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `labor_contracts`
--

INSERT INTO `labor_contracts` (`id`, `user_id`, `contract_number`, `contract_type`, `start_date`, `end_date`, `base_salary`, `hourly_rate`, `allowance`, `bank_number`, `bank_name`, `tax_code`, `contract_file`, `is_active`) VALUES
(1, 8, 'HD008', 'Chính thức', '2025-10-01', '2026-10-01', 15000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(2, 9, 'HD009', 'Chính thức', '2025-10-01', '2026-10-01', 18000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(3, 10, 'HD010', 'Thử việc', '2025-10-05', '2025-12-05', 6000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(4, 11, 'HD011', 'Thử việc', '2025-10-05', '2025-12-05', 6000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(5, 12, 'HD012', 'Chính thức', '2025-10-10', '2026-10-10', 9000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(6, 13, 'HD013', 'Chính thức', '2025-10-10', '2026-10-10', 9500000, 0, 0, NULL, NULL, NULL, NULL, 1),
(7, 14, 'HD014', 'Chính thức', '2025-09-15', '2026-09-15', 12000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(8, 15, 'HD015', 'Chính thức', '2025-09-15', '2026-09-15', 11000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(9, 16, 'HD016', 'Thử việc', '2025-11-01', '2026-01-01', 10000000, 0, 0, NULL, NULL, NULL, NULL, 1),
(10, 17, 'HD017', 'Thử việc', '2025-11-01', '2026-01-01', 5500000, 0, 0, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `course_interest` varchar(100) DEFAULT NULL,
  `status` enum('new','contacted','enrolled','lost') DEFAULT 'new',
  `assigned_to` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `leads`
--

INSERT INTO `leads` (`id`, `name`, `email`, `phone`, `course_interest`, `status`, `assigned_to`, `created_at`) VALUES
(1, 'Trần A', NULL, '091111', 'IELTS', 'new', 6, '2025-12-01 04:33:17'),
(2, 'Trần Văn Bình', 'binh.tran@gmail.com', '0912345678', 'IELTS 6.5 Cam kết', 'new', 10, '2025-12-01 17:28:09'),
(3, 'Lê Thị Thu Thảo', 'thuthao@yahoo.com', '0987654321', 'Tiếng Anh Giao Tiếp', 'contacted', 6, '2025-12-01 17:28:09'),
(4, 'Nguyễn Hoàng Nam', NULL, '0909123123', 'Khóa trẻ em', 'enrolled', 6, '2025-12-01 17:28:09'),
(5, 'Phạm Minh Tuấn', NULL, '0911223344', 'Luyện đề cấp tốc', 'lost', 11, '2025-12-01 17:28:09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `user_id`, `start_date`, `end_date`, `reason`, `status`, `created_at`) VALUES
(1, 6, '2025-12-06', '2025-12-07', 'Về quê', 'pending', '2025-12-01 04:33:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `legal_documents`
--

CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doc_type` enum('CCCD','Hộ chiếu','Visa','Work Permit') NOT NULL,
  `doc_number` varchar(50) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `place_of_issue` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `doc_file_front` varchar(255) DEFAULT NULL,
  `doc_file_back` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'img/default.jpg',
  `created_at` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `news`
--

INSERT INTO `news` (`id`, `title`, `summary`, `content`, `image`, `created_at`) VALUES
(3, 'Thông báo tuyển sinh', 'Chào mừng năm học mới', 'Không có nội dung chi tiết', 'img/1764612548_692dd9c4b2a7b.jpg', '2025-12-02'),
(4, 'Thông báo Lịch Thi', 'Lịch Thi Mới', 'Thông báo về lịch thi', 'img/1764613215_692ddc5fb726f.jpg', '2025-12-02'),
(5, 'Thông báo Tuyên truyền vận động hiến máu', 'Tuyên truyền hiến máu', 'Hiến máu cứu người', 'img/1764615549_692de57d0368a.jpg', '2025-12-02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'Lịch nghỉ lễ', 'Toàn công ty nghỉ 1 ngày.', '2025-12-01 04:33:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `month` varchar(7) NOT NULL,
  `base_salary` decimal(15,0) DEFAULT 0,
  `allowance_degree` decimal(15,0) DEFAULT 0,
  `allowance_seniority` decimal(15,0) DEFAULT 0,
  `allowance_language` decimal(15,0) DEFAULT 0,
  `work_days` decimal(4,1) DEFAULT 0.0,
  `overtime_hours` decimal(5,1) DEFAULT 0.0,
  `overtime_money` decimal(15,0) DEFAULT 0,
  `bonus` decimal(15,0) DEFAULT 0,
  `tax` decimal(15,0) DEFAULT 0,
  `tax_percent` decimal(5,2) DEFAULT 0.00,
  `late_count` int(11) DEFAULT 0,
  `total_fine` decimal(15,0) DEFAULT 0,
  `unpaid_leave_days` decimal(4,1) DEFAULT 0.0,
  `note` text DEFAULT NULL,
  `total_salary` decimal(15,0) NOT NULL,
  `status` enum('paid','unpaid') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payroll`
--

INSERT INTO `payroll` (`id`, `user_id`, `month`, `base_salary`, `allowance_degree`, `allowance_seniority`, `allowance_language`, `work_days`, `overtime_hours`, `overtime_money`, `bonus`, `tax`, `tax_percent`, `late_count`, `total_fine`, `unpaid_leave_days`, `note`, `total_salary`, `status`, `created_at`) VALUES
(1, 2, '2025-10', 18000000, 1500000, 2000000, 1000000, 26.0, 5.0, 650000, 2000000, 0, 0.00, 0, 0, 0.0, 'Thưởng KPI quý 3', 25150000, 'paid', '2025-10-31 08:00:00'),
(2, 3, '2025-10', 10000000, 500000, 300000, 500000, 25.0, 0.0, 0, 500000, 0, 0.00, 0, 0, 1.0, 'Trừ 1 ngày nghỉ KP', 11415000, 'paid', '2025-10-31 08:00:00'),
(3, 4, '2025-10', 12000000, 1500000, 300000, 0, 26.0, 12.0, 1038000, 0, 0, 0.00, 0, 0, 0.0, 'OT dạy thay 12h', 14838000, 'paid', '2025-10-31 08:00:00'),
(4, 6, '2025-10', 7000000, 300000, 0, 0, 26.0, 0.0, 0, 4500000, 0, 0.00, 0, 0, 0.0, 'Hoa hồng: 15 HĐ', 11800000, 'paid', '2025-10-31 08:00:00'),
(5, 2, '2025-09', 18000000, 1500000, 2000000, 1000000, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, '', 22500000, 'paid', '2025-09-30 08:00:00'),
(6, 3, '2025-09', 10000000, 500000, 300000, 500000, 26.0, 2.0, 144000, 200000, 0, 0.00, 0, 0, 0.0, '', 11644000, 'paid', '2025-09-30 08:00:00'),
(7, 4, '2025-09', 12000000, 1500000, 300000, 0, 24.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, 'Nghỉ ốm 2 ngày', 12876000, 'paid', '2025-09-30 08:00:00'),
(8, 6, '2025-09', 7000000, 300000, 0, 0, 26.0, 0.0, 0, 500000, 0, 0.00, 1, 0, 0.0, '', 7800000, 'paid', '2025-09-30 08:00:00'),
(9, 8, '2025-10', 15000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 16500000, 'paid', '2025-10-30 17:00:00'),
(10, 9, '2025-10', 18000000, 0, 0, 0, 25.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 20000000, 'paid', '2025-10-30 17:00:00'),
(11, 14, '2025-10', 12000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 13000000, 'paid', '2025-10-30 17:00:00'),
(12, 15, '2025-10', 11000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 11500000, 'paid', '2025-10-30 17:00:00'),
(13, 8, '2025-11', 15000000, 0, 0, 0, 24.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 15800000, 'paid', '2025-11-29 17:00:00'),
(14, 9, '2025-11', 18000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 21000000, 'paid', '2025-11-29 17:00:00'),
(15, 10, '2025-11', 6000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 8500000, 'paid', '2025-11-29 17:00:00'),
(16, 11, '2025-11', 6000000, 0, 0, 0, 25.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 6200000, 'paid', '2025-11-29 17:00:00'),
(17, 12, '2025-11', 9000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 9500000, 'paid', '2025-11-29 17:00:00'),
(18, 13, '2025-11', 9500000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 10000000, 'paid', '2025-11-29 17:00:00'),
(19, 14, '2025-11', 12000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 13000000, 'paid', '2025-11-29 17:00:00'),
(20, 15, '2025-11', 11000000, 0, 0, 0, 23.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 10500000, 'paid', '2025-11-29 17:00:00'),
(21, 16, '2025-11', 10000000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 11000000, 'paid', '2025-11-29 17:00:00'),
(22, 17, '2025-11', 5500000, 0, 0, 0, 26.0, 0.0, 0, 0, 0, 0.00, 0, 0, 0.0, NULL, 6000000, 'paid', '2025-11-29 17:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `permissions`
--

INSERT INTO `permissions` (`id`, `code`, `description`) VALUES
(1, 'user.view', 'Xem DS nhân viên'),
(2, 'user.create', 'Thêm nhân viên'),
(3, 'user.edit', 'Sửa nhân viên'),
(4, 'user.delete', 'Xóa nhân viên'),
(5, 'salary.manage', 'Quản lý Lương'),
(6, 'expense.manage', 'Quản lý Chi tiêu'),
(7, 'leave.approve', 'Duyệt đơn'),
(8, 'news.manage', 'Đăng tin'),
(10, 'leave.create', 'Xin nghỉ'),
(11, 'attendance.check', 'Chấm công'),
(12, 'salary.read_personal', 'Xem lương'),
(20, 'class.view', 'Xem lớp dạy'),
(21, 'lead.manage', 'Quản lý khách');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `profile_requests`
--

CREATE TABLE `profile_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `data_content` longtext DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `profile_requests`
--

INSERT INTO `profile_requests` (`id`, `user_id`, `type`, `data_content`, `status`, `request_date`) VALUES
(1, 3, 'personal', '{\"full_name\":\"Lê Kế Toán\",\"email\":\"acc@center.com\",\"phone\":\"0902000222\",\"dob\":\"\",\"address\":null,\"gender\":\"Nam\",\"nationality\":\"Việt Nam\",\"marital_status\":null,\"zalo\":null,\"current_address\":\"Ô diên hà NỘI\",\"hometown\":null,\"biography\":\"Kế toán viên chuyên nghiệp.\"}', 'approved', '2025-12-01 17:07:07'),
(2, 3, 'personal', '{\"full_name\":\"Lê Kế Toán\",\"email\":\"acc@center.com\",\"phone\":\"0902000222\",\"dob\":\"\",\"address\":null,\"gender\":\"Nam\",\"nationality\":\"Việt Nam\",\"marital_status\":null,\"zalo\":null,\"current_address\":\"Ô diên hà NỘI\",\"hometown\":null,\"biography\":\"Kế toán viên chuyên nghiệp.\"}', 'approved', '2025-12-01 17:08:01'),
(3, 3, 'personal', '{\"full_name\":\"Lê Kế Toán\",\"email\":\"acc@center.com\",\"phone\":\"0902000222\",\"dob\":\"\",\"address\":null,\"gender\":\"Nam\",\"nationality\":\"Việt Nam\",\"marital_status\":null,\"zalo\":null,\"current_address\":\"Ô diên hà NỘI\",\"hometown\":null,\"biography\":\"Kế toán viên chuyên nghiệp.\"}', 'approved', '2025-12-01 17:13:37'),
(4, 3, 'personal', '{\"full_name\":\"Lê Kế Toán\",\"email\":\"acc@center.com\",\"phone\":\"0902000222\",\"dob\":\"\",\"address\":\"Không\",\"gender\":\"Nam\",\"nationality\":\"Việt Nam\",\"marital_status\":\"Độc thân\",\"zalo\":\"\",\"current_address\":\"Ô diên hà NỘI\",\"hometown\":\"Haà Nội\",\"biography\":\"Kế toán viên chuyên nghiệp.\"}', 'approved', '2025-12-01 17:13:58'),
(5, 3, 'teaching', '{\"education_level\":\"Đại học\",\"major\":\"Kế toán\",\"certificate_type\":\"None\",\"certificate_score\":\"700.0\",\"main_subject\":\"\",\"teaching_band\":\"\",\"demo_video_link\":\"\"}', 'rejected', '2025-12-01 17:14:30'),
(6, 3, 'personal', '{\"full_name\":\"Lê Kế Toán\",\"email\":\"acc@center.com\",\"phone\":\"0902000222\",\"dob\":\"\",\"address\":\"\",\"gender\":\"Nam\",\"nationality\":\"Việt Nam\",\"marital_status\":\"Độc thân\",\"zalo\":\"\",\"current_address\":\"Ô diên hà NỘI\",\"hometown\":\"Haà Nội\",\"biography\":\"Kế toán viên chuyên nghiệp.\",\"avatar\":\"img\\/proofs\\/1764609292_avt_z7242676947454_e321b0abf1b5ceb643642e31ab1ec773.jpg\"}', 'approved', '2025-12-01 17:14:52'),
(7, 3, 'personal', '{\"full_name\":\"Lê Kế Toán\",\"email\":\"acc@center.com\",\"phone\":\"0902000222\",\"dob\":\"\",\"address\":\"\",\"gender\":\"Nam\",\"nationality\":\"Việt Nam\",\"marital_status\":\"Độc thân\",\"zalo\":\"\",\"current_address\":\"Ô diên hà NỘI\",\"hometown\":\"Haà Nội\",\"biography\":\"Kế toán viên chuyên nghiệp.\",\"avatar\":\"img\\/proofs\\/1764609337_avt_[520x270-cr]thongbao.png\"}', 'approved', '2025-12-01 17:15:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `default_salary` decimal(15,0) DEFAULT 5000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `default_salary`) VALUES
(1, 'Admin', 'Quản trị viên', 25000000),
(2, 'Trưởng phòng', 'Quản lý bộ phận', 18000000),
(3, 'Kế toán', 'Chuyên viên tài chính', 10000000),
(4, 'Giáo viên', 'Giảng viên đứng lớp', 12000000),
(5, 'Tuyển sinh', 'Nhân viên Sale', 7000000),
(6, 'Nhân viên', 'Nhân viên hành chính', 6000000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 10),
(1, 11),
(1, 12),
(2, 1),
(2, 7),
(2, 8),
(2, 10),
(2, 11),
(2, 12),
(3, 1),
(3, 5),
(3, 6),
(3, 10),
(3, 11),
(3, 12),
(4, 10),
(4, 11),
(4, 12),
(4, 20),
(5, 10),
(5, 11),
(5, 12),
(5, 21);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `setting_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_name`) VALUES
(1, 'allowance_bachelor', '500000', 'Phụ cấp Đại học'),
(2, 'allowance_master', '1500000', 'Phụ cấp Thạc sĩ'),
(3, 'allowance_phd', '3000000', 'Phụ cấp Tiến sĩ'),
(4, 'allowance_intermediate', '200000', 'Phụ cấp Trung cấp'),
(5, 'allowance_college', '300000', 'Phụ cấp Cao đẳng'),
(6, 'allowance_sen_1y', '300000', 'Thâm niên > 1 năm'),
(7, 'allowance_sen_3y', '1000000', 'Thâm niên > 3 năm'),
(8, 'allowance_sen_5y', '2000000', 'Thâm niên > 5 năm'),
(9, 'allowance_ielts_6', '500000', 'IELTS 6.0+'),
(10, 'allowance_ielts_7', '1000000', 'IELTS 7.0+'),
(11, 'allowance_ielts_8', '2000000', 'IELTS 8.0+'),
(12, 'standard_work_days', '26', 'Số công chuẩn');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teaching_profile`
--

CREATE TABLE `teaching_profile` (
  `user_id` int(11) NOT NULL,
  `main_subject` varchar(100) DEFAULT NULL,
  `teaching_band` varchar(100) DEFAULT NULL,
  `demo_video_link` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `teaching_profile`
--

INSERT INTO `teaching_profile` (`user_id`, `main_subject`, `teaching_band`, `demo_video_link`) VALUES
(8, 'Speaking & Writing', '6.5+', 'youtube.com/demo8'),
(9, 'Listening & Reading', '7.5+', 'youtube.com/demo9'),
(16, 'General English', '5.5+', 'youtube.com/demo16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT 'img/default.jpg',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `role_id`, `department_id`, `username`, `password`, `email`, `full_name`, `avatar`, `status`, `created_at`) VALUES
(1, 1, NULL, 'admin', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'admin@center.com', 'Super Admin', 'img/default.jpg', 'active', '2025-12-01 04:33:16'),
(2, 2, 3, 'tp_daotao', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'manager@center.com', 'Trần Đào Tạo', 'img/default.jpg', 'active', '2025-12-01 04:33:16'),
(3, 3, 2, 'ketoan', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'acc@center.com', 'Lê Kế Toán', 'img/proofs/1764609337_avt_[520x270-cr]thongbao.png', 'active', '2025-12-01 04:33:16'),
(4, 4, NULL, 'teacher_native', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'teacher1@center.com', 'Mr. David Beck', 'img/default.jpg', 'active', '2025-12-01 04:33:16'),
(5, 4, 3, 'teacher_vn', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'teacher2@center.com', 'Cô Mai Anh', 'img/default.jpg', 'active', '2025-12-01 04:33:16'),
(6, 5, 4, 'sale_staff', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'sale@center.com', 'Nguyễn Văn Sale', 'img/default.jpg', 'active', '2025-12-01 04:33:16'),
(7, 6, 1, 'mkt_staff', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'mkt@center.com', 'Phạm Marketing', 'img/default.jpg', 'active', '2025-12-01 04:33:16'),
(8, 4, 3, 'gv_lan', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'lan.nguyen@center.com', 'Nguyễn Thị Lan', 'img/default.jpg', 'active', '2025-10-01 01:00:00'),
(9, 4, 3, 'gv_hung', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'hung.tran@center.com', 'Trần Văn Hùng', 'img/default.jpg', 'active', '2025-10-01 01:00:00'),
(10, 5, 4, 'sale_ngoc', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'ngoc.le@center.com', 'Lê Thị Ngọc', 'img/default.jpg', 'active', '2025-10-05 01:00:00'),
(11, 5, 4, 'sale_tuan', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'tuan.pham@center.com', 'Phạm Minh Tuấn', 'img/default.jpg', 'active', '2025-10-05 01:00:00'),
(12, 6, 1, 'mkt_mai', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'mai.hoang@center.com', 'Hoàng Thị Mai', 'img/default.jpg', 'active', '2025-10-10 01:00:00'),
(13, 6, 1, 'mkt_nam', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'nam.vu@center.com', 'Vũ Văn Nam', 'img/default.jpg', 'active', '2025-10-10 01:00:00'),
(14, 3, 2, 'kt_thu', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'thu.dang@center.com', 'Đặng Thị Thu', 'img/default.jpg', 'active', '2025-09-15 01:00:00'),
(15, 6, 5, 'hr_long', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'long.bui@center.com', 'Bùi Văn Long', 'img/default.jpg', 'active', '2025-09-15 01:00:00'),
(16, 4, 3, 'gv_hoa', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'hoa.do@center.com', 'Đỗ Thị Hoa', 'img/default.jpg', 'active', '2025-11-01 01:00:00'),
(17, 5, 4, 'sale_quyen', '$2y$10$1tzcfBb3Y1Z3dUIdd4eE2esR25Z4ARIVv2zLvJ8VMZOGnHEc4tENi', 'quyen.ngo@center.com', 'Ngô Văn Quyền', 'img/default.jpg', 'active', '2025-11-01 01:00:00');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Chỉ mục cho bảng `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `employee_details`
--
ALTER TABLE `employee_details`
  ADD PRIMARY KEY (`user_id`);

--
-- Chỉ mục cho bảng `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Chỉ mục cho bảng `insurance`
--
ALTER TABLE `insurance`
  ADD PRIMARY KEY (`user_id`);

--
-- Chỉ mục cho bảng `labor_contracts`
--
ALTER TABLE `labor_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Chỉ mục cho bảng `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `profile_requests`
--
ALTER TABLE `profile_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Chỉ mục cho bảng `teaching_profile`
--
ALTER TABLE `teaching_profile`
  ADD PRIMARY KEY (`user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_user_dept` (`department_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `labor_contracts`
--
ALTER TABLE `labor_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `legal_documents`
--
ALTER TABLE `legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `profile_requests`
--
ALTER TABLE `profile_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `employee_details`
--
ALTER TABLE `employee_details`
  ADD CONSTRAINT `employee_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `insurance`
--
ALTER TABLE `insurance`
  ADD CONSTRAINT `insurance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `labor_contracts`
--
ALTER TABLE `labor_contracts`
  ADD CONSTRAINT `labor_contracts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD CONSTRAINT `legal_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `profile_requests`
--
ALTER TABLE `profile_requests`
  ADD CONSTRAINT `profile_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teaching_profile`
--
ALTER TABLE `teaching_profile`
  ADD CONSTRAINT `teaching_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
