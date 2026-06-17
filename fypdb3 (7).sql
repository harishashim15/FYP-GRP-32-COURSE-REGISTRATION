-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2026 at 03:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fypdb3`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `matrix_number` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`, `matrix_number`, `user_name`) VALUES
(4, 'AD235KL2', 'Ashraff Bin Muhd Hakimi'),
(4, 'AD235KL2', 'Ashraff Bin Muhd Hakimi'),
(30, 'ADM001', 'Azhar Bin Abdullah'),
(31, 'ADM002', 'Siti Aishah Binti Ramli'),
(32, 'ADM003', 'Muhammad Fauzi Bin Hassan'),
(33, 'ADM004', 'Nurul Huda Binti Zakaria'),
(34, 'ADM005', 'Rashid Bin Omar'),
(35, 'ADM006', 'Zainab Binti Ahmad'),
(36, 'ADM007', 'Firdaus Bin Ismail'),
(37, 'ADM008', 'Hasmah Binti Sulaiman'),
(38, 'ADM009', 'Shahrul Bin Mohd Noor'),
(39, 'ADM010', 'Roslina Binti Yusof'),
(4, 'AD235KL2', 'Ashraff Bin Muhd Hakimi'),
(30, 'ADM001', 'Azhar Bin Abdullah'),
(31, 'ADM002', 'Siti Aishah Binti Ramli'),
(32, 'ADM003', 'Muhammad Fauzi Bin Hassan'),
(33, 'ADM004', 'Nurul Huda Binti Zakaria'),
(34, 'ADM005', 'Rashid Bin Omar'),
(35, 'ADM006', 'Zainab Binti Ahmad'),
(36, 'ADM007', 'Firdaus Bin Ismail'),
(37, 'ADM008', 'Hasmah Binti Sulaiman'),
(38, 'ADM009', 'Shahrul Bin Mohd Noor'),
(39, 'ADM010', 'Roslina Binti Yusof');

-- --------------------------------------------------------

--
-- Table structure for table `advisor`
--

CREATE TABLE `advisor` (
  `user_id` int(11) NOT NULL,
  `advisor_name` varchar(100) NOT NULL,
  `matrix_number` varchar(100) NOT NULL,
  `utm_email` varchar(100) NOT NULL,
  `second_email` varchar(100) NOT NULL,
  `faculty` enum('Faculty Of SPACE','Faculty Of Computer Science','','') NOT NULL,
  `department` enum('Computer Science','Mathematics','Sports Science','Electrical Engineering','Pengajian Islam') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advisor`
--

INSERT INTO `advisor` (`user_id`, `advisor_name`, `matrix_number`, `utm_email`, `second_email`, `faculty`, `department`) VALUES
(6, 'Mr Halim bin Muhammad', 'LE234AD', 'halimlect@utm.my', 'halimmihammad@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(28, 'Miss Nurul Asyikin', 'PS0193', 'asyikin@utmspace.edu.my', 'asyikinmuhamad8@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(28, 'Miss Nurul Asyikin', 'PS0193', 'asyikin@utmspace.edu.my', 'asyikinmuhamad8@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(6, 'Mr Halim bin Muhammad', 'LE234AD', 'halimlect@utm.my', 'halimmihammad@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(40, 'Dr. Ahmad Tarmizi Bin Mohd', 'LEC001', 'tarmizi@utm.my', 'tarmizi@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(41, 'Prof. Madya Salmiah Binti Idris', 'LEC002', 'salmiah@utm.my', 'salmiah@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(42, 'Ts. Mohd Redzuan Bin Ali', 'LEC003', 'redzuan@utm.my', 'redzuan@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(43, 'Dr. Norazlina Binti Mustafa', 'LEC004', 'norazlina@utm.my', 'norazlina@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(44, 'Ts. Khairul Anuar Bin Ismail', 'LEC005', 'khairul.ismail@utm.my', 'khairul@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(45, 'Dr. Faridah Binti Hamzah', 'LEC006', 'faridah@utm.my', 'faridah@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(46, 'Encik Zulkifli Bin Rahman', 'LEC007', 'zulkifli@utm.my', 'zulkifli@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(47, 'Dr. Maznah Binti Abdullah', 'LEC008', 'maznah@utm.my', 'maznah@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(48, 'Ts. Azman Bin Rashid', 'LEC009', 'azman@utm.my', 'azman@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(49, 'Dr. Suhaila Binti Othman', 'LEC010', 'suhaila@utm.my', 'suhaila@gmail.com', 'Faculty Of SPACE', 'Computer Science'),
(6, 'Mr Halim bin Muhammad', 'LE234AD', 'halimlect@utm.my', 'halimmihammad@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(28, 'Miss Nurul Asyikin', 'PS0193', 'asyikin@utmspace.edu.my', 'asyikinmuhamad8@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(40, 'Dr. Ahmad Tarmizi Bin Mohd', 'LEC001', 'tarmizi@utm.my', 'tarmizi@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(41, 'Prof. Madya Salmiah Binti Idris', 'LEC002', 'salmiah@utm.my', 'salmiah@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(42, 'Ts. Mohd Redzuan Bin Ali', 'LEC003', 'redzuan@utm.my', 'redzuan@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(43, 'Dr. Norazlina Binti Mustafa', 'LEC004', 'norazlina@utm.my', 'norazlina@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(44, 'Ts. Khairul Anuar Bin Ismail', 'LEC005', 'khairul.ismail@utm.my', 'khairul@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(45, 'Dr. Faridah Binti Hamzah', 'LEC006', 'faridah@utm.my', 'faridah@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(46, 'Encik Zulkifli Bin Rahman', 'LEC007', 'zulkifli@utm.my', 'zulkifli@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(47, 'Dr. Maznah Binti Abdullah', 'LEC008', 'maznah@utm.my', 'maznah@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(48, 'Ts. Azman Bin Rashid', 'LEC009', 'azman@utm.my', 'azman@gmail.com', 'Faculty Of Computer Science', 'Computer Science'),
(49, 'Dr. Suhaila Binti Othman', 'LEC010', 'suhaila@utm.my', 'suhaila@gmail.com', 'Faculty Of Computer Science', 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_date` datetime NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `advisor_remarks` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `session` varchar(50) DEFAULT '2025/2026 - Semester 2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_registrations`
--

INSERT INTO `course_registrations` (`id`, `student_id`, `submission_date`, `status`, `advisor_remarks`, `reviewed_by`, `reviewed_at`, `section`, `session`) VALUES
(1, 2, '2026-05-14 00:00:00', 'approved', '', NULL, '2026-05-14 10:22:37', NULL, '2025/2026 - Semester 2'),
(2, 2, '2026-05-14 00:00:00', 'approved', '', NULL, '2026-05-14 10:25:45', NULL, '2025/2026 - Semester 2'),
(3, 2, '2026-05-14 00:00:00', 'rejected', '', NULL, '2026-05-14 10:51:35', NULL, '2025/2026 - Semester 2'),
(4, 2, '2026-05-14 00:00:00', 'approved', '', NULL, '2026-05-14 11:20:51', NULL, '2025/2026 - Semester 2'),
(5, 2, '2026-05-25 00:00:00', 'pending', NULL, NULL, NULL, NULL, '2025/2026 - Semester 2'),
(6, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '3', '2025/2026 - Semester 2'),
(7, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '4', '2025/2026 - Semester 2'),
(8, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '4', '2025/2026 - Semester 2'),
(9, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '1', '2025/2026 - Semester 2'),
(10, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '1', '2025/2026 - Semester 2'),
(11, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '1', '2025/2026 - Semester 2'),
(12, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '3', '2025/2026 - Semester 2'),
(13, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '1', '2025/2026 - Semester 2'),
(14, 24, '2026-05-26 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', '2', '2025/2026 - Semester 2'),
(15, 24, '2026-05-28 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', NULL, '2025/2026 - Semester 2'),
(16, 24, '2026-05-30 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', NULL, '2025/2026 - Semester 2'),
(17, 24, '2026-05-30 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', NULL, '2025/2026 - Semester 2'),
(18, 24, '2026-06-04 00:00:00', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', NULL, '2025/2026 - Semester 2'),
(31, 29, '2026-06-04 15:13:08', 'approved', '', 28, '2026-06-04 15:13:44', NULL, '2025/2026 - Semester 2'),
(32, 24, '2026-06-04 22:37:09', 'rejected', 'Ada problem', 6, '2026-06-04 22:38:18', NULL, '2025/2026 - Semester 2'),
(33, 24, '2026-06-05 10:28:51', 'approved', '', 6, '2026-06-05 10:31:39', NULL, '2025/2026 - Semester 2');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(5, 'harisomniverse2@gmail.com', '1fa243bf2aeb3316b737eccd0136491f3b8affbe2edb3524d1016b00d65abff6', '2026-05-27 13:12:55', '2026-05-27 18:12:55');

-- --------------------------------------------------------

--
-- Table structure for table `registration_cart`
--

CREATE TABLE `registration_cart` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `added_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_courses`
--

CREATE TABLE `registration_courses` (
  `id` int(11) UNSIGNED NOT NULL,
  `registration_id` int(11) UNSIGNED NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `section` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_courses`
--

INSERT INTO `registration_courses` (`id`, `registration_id`, `subject_code`, `section`) VALUES
(1, 1, 'SECJ2213', 'A'),
(2, 2, 'DSPD1693', 'A'),
(3, 3, 'DSPD1483', 'A'),
(4, 4, 'SECJ2123', 'A'),
(5, 5, 'DSPD2334', 'A'),
(6, 5, 'DSPD2533', 'A'),
(7, 5, 'DSPD2353', 'A'),
(8, 5, 'DSPD2713', 'A'),
(9, 5, 'SECJ2152', 'A'),
(10, 15, 'DSPD1483', '3'),
(11, 15, 'DSPD1693', '4'),
(12, 15, 'DSPD2334', '3'),
(13, 15, 'DSPD2353', '2'),
(14, 16, 'DSPD1693', '3'),
(15, 16, 'DSPD2533', '2'),
(16, 16, 'DSPD2353', '3'),
(17, 17, 'SECJ2213', '2'),
(18, 17, 'DSPD2353', '2'),
(19, 18, 'DSPD1693', '3'),
(20, 18, 'DSPD2353', '3'),
(21, 18, 'DSPD2533', '2'),
(55, 31, 'DSPD2713', '2'),
(56, 31, 'SECJ2152', '1'),
(57, 31, 'SECJ2123', '3'),
(58, 32, 'DSPD1693', '2'),
(59, 32, 'DSPD2353', '3'),
(60, 33, 'DSPD1693', '2'),
(61, 33, 'DSPD2353', '3'),
(62, 33, 'DDWD2053', '3');

-- --------------------------------------------------------

--
-- Table structure for table `reset_attempts`
--

CREATE TABLE `reset_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) NOT NULL,
  `attempted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reset_attempts`
--

INSERT INTO `reset_attempts` (`id`, `ip`, `attempted_at`) VALUES
(1, '::1', '2026-05-22 08:52:32'),
(2, '::1', '2026-05-22 09:07:50'),
(3, '::1', '2026-05-22 09:12:07'),
(4, '::1', '2026-05-22 09:13:37'),
(5, '::1', '2026-05-27 18:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `semester_registration_periods`
--

CREATE TABLE `semester_registration_periods` (
  `id` int(11) UNSIGNED NOT NULL,
  `session_semester` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_open` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester_registration_periods`
--

INSERT INTO `semester_registration_periods` (`id`, `session_semester`, `start_date`, `end_date`, `is_open`) VALUES
(1, '2025/2026-2', '2026-05-06', '2026-06-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `user_id` int(11) NOT NULL,
  `matrix_number` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `utm_email` varchar(100) NOT NULL,
  `second_email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `ic_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `programme` enum('Computer Science','Electrical Engineering','Sport Science','Pengajian Islam') NOT NULL,
  `year` enum('1','2','3','4') NOT NULL,
  `semester` enum('1','2','3') NOT NULL,
  `advisor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`user_id`, `matrix_number`, `user_name`, `utm_email`, `second_email`, `phone`, `ic_number`, `address`, `programme`, `year`, `semester`, `advisor_id`) VALUES
(24, 'A24DW0421', 'Haris', 'muhammadharishashim@graduate.utm.my', 'harisomniverse2@gmail.com', '012-282-3151', NULL, NULL, 'Computer Science', '1', '1', 6),
(26, 'A24WERT45', 'Ahmad Danish', 'Ahmad04@graduate.utm.my', 'harishashimpersonal@gmail.com', '+60 12-345 6789', NULL, NULL, 'Computer Science', '1', '1', 6),
(27, 'A34DW0432', 'Anaqi', 'anaqi06@graduate.utm.my', 'anaqi@gmail.com', '012-267-8765', NULL, NULL, 'Computer Science', '1', '1', 6),
(29, 'A34PE5678', 'Ravi A/L Daneswaran', 'ravi06@graduate.utm.my', 'ravi@gmail.com', '017-962-8405', NULL, NULL, 'Computer Science', '1', '1', 6),
(50, 'A24DW0001', 'Ali Bin Abu', 'ali.abu@graduate.utm.my', 'ali@gmail.com', '014-3453001', NULL, NULL, 'Computer Science', '1', '1', 6),
(51, 'A24DW0002', 'Fatimah Binti Hassan', 'fatimah.hassan@graduate.utm.my', 'fatimah@gmail.com', '014-3453002', NULL, NULL, 'Computer Science', '1', '1', 6),
(52, 'A24DW0003', 'Muhammad Idris Bin Othman', 'idris.othman@graduate.utm.my', 'idris@gmail.com', '014-3453003', NULL, NULL, 'Computer Science', '1', '1', 28),
(53, 'A24DW0004', 'Nur Ain Binti Zulkifli', 'nur.ain@graduate.utm.my', 'nurain@gmail.com', '014-3453004', NULL, NULL, 'Computer Science', '1', '1', 28),
(54, 'A24DW0005', 'Hafiz Bin Zainal', 'hafiz.zainal@graduate.utm.my', 'hafiz@gmail.com', '014-3453005', NULL, NULL, 'Computer Science', '1', '1', 40),
(55, 'A24DW0006', 'Syarifah Sofia Binti Syed', 'sofia@graduate.utm.my', 'sofia@gmail.com', '014-3453006', NULL, NULL, 'Computer Science', '1', '1', 40),
(56, 'A24DW0007', 'Irfan Bin Rosli', 'irfan.rosli@graduate.utm.my', 'irfan@gmail.com', '014-3453007', NULL, NULL, 'Computer Science', '1', '1', 41),
(57, 'A24DW0008', 'Nadia Binti Aziz', 'nadia.aziz@graduate.utm.my', 'nadia@gmail.com', '014-3453008', NULL, NULL, 'Computer Science', '1', '1', 41),
(58, 'A24DW0009', 'Farhan Bin Saleh', 'farhan.saleh@graduate.utm.my', 'farhan@gmail.com', '014-3453009', NULL, NULL, 'Computer Science', '1', '1', 6),
(59, 'A24DW0010', 'Nurul Izzati Binti Kamal', 'izzati.kamal@graduate.utm.my', 'izzati@gmail.com', '014-3453010', NULL, NULL, 'Computer Science', '1', '1', 6),
(60, 'A24DW0213', 'chong wei lee', 'weilee@graduate.utm.my', 'WeiLee2@gmail.com', '011-974-378', NULL, NULL, 'Computer Science', '1', '1', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_semesters`
--

CREATE TABLE `student_semesters` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `session_semester` varchar(20) NOT NULL,
  `programme` varchar(50) NOT NULL,
  `no_semester` int(2) NOT NULL,
  `reg_date` date NOT NULL,
  `active_code` varchar(20) DEFAULT 'A-Active',
  `cpa` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_semesters`
--

INSERT INTO `student_semesters` (`id`, `student_id`, `session_semester`, `programme`, `no_semester`, `reg_date`, `active_code`, `cpa`) VALUES
(1, 2, '2024/2025-1', '1 / DSPD', 1, '2024-07-27', 'A-Active', 3.67),
(2, 2, '2024/2025-2', '1 / DSPD', 2, '2024-12-26', 'A-Active', 3.70),
(3, 2, '2024/2025-3', '1 / DSPD', 3, '2025-05-16', 'A-Active', 3.76),
(4, 2, '2025/2026-1', '2 / DSPD', 4, '2025-07-10', 'A-Active', 3.77),
(5, 2, '2025/2026-2', '2 / DSPD', 5, '2025-12-08', 'A-Active', 3.83),
(6, 2, '2025/2026-3', '2 / DSPD', 6, '2026-05-06', 'A-Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `credits` int(2) NOT NULL,
  `programme` varchar(100) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_code`, `subject_name`, `credits`, `programme`, `is_hidden`) VALUES
('DDWD1003', 'Computer System and Applications', 3, 'General', 1),
('DDWD1023', 'Information Technologist and Communication Application', 3, 'General', 0),
('DDWD2033', 'Teaching and Learning Technology', 3, 'General', 0),
('DDWD2043', 'Introduction to Multimedia', 3, 'General', 0),
('DDWD2053', 'Information Management System in Education', 3, 'General', 0),
('DDWI1012', 'Educational Philosophy', 2, 'Islamic Studies Education', 0),
('DDWI1022', 'Principles of Islamic Jurisprudence I', 2, 'Islamic Studies Education', 0),
('DDWI1032', 'Arabic Grammar I', 2, 'Islamic Studies Education', 0),
('DDWI1042', 'Jawi and Islamic Calligraphy', 2, 'Islamic Studies Education', 0),
('DDWI1052', 'Al-Quran Memorization', 2, 'Islamic Studies Education', 0),
('DDWI1062', 'Educational Psychology', 2, 'Islamic Studies Education', 0),
('DDWI1072', 'Fundamental of Pedagogy', 2, 'Islamic Studies Education', 0),
('DDWI1082', 'Principles of Islamic Jurisprudence II', 2, 'Islamic Studies Education', 0),
('DDWI1092', 'Fiqh Ibadah', 2, 'Islamic Studies Education', 0),
('DDWI2102', 'Measurement and Evaluation in Education', 2, 'Islamic Studies Education', 0),
('DDWI2112', 'Islamic Faith', 2, 'Islamic Studies Education', 0),
('DDWI2122', 'Fiqh Muamalat', 2, 'Islamic Studies Education', 0),
('DDWI2132', 'Arabic Grammar II', 2, 'Islamic Studies Education', 0),
('DDWI2142', 'Arabic Language Teaching Methods', 2, 'Islamic Studies Education', 0),
('DDWI2152', 'Methods of Teaching Islamic Education', 2, 'Islamic Studies Education', 0),
('DDWI2162', 'Educational Sociology', 2, 'Islamic Studies Education', 0),
('DDWI2172', 'Introduction to Ulum Quran', 2, 'Islamic Studies Education', 0),
('DDWI2182', 'Introduction to Ulum Hadis', 2, 'Islamic Studies Education', 0),
('DDWI2192', 'Fiqh Sirah', 2, 'Islamic Studies Education', 0),
('DDWI2202', 'Fiqh Jinayat', 2, 'Islamic Studies Education', 0),
('DDWI2212', 'Islamic Morals', 2, 'Islamic Studies Education', 0),
('DDWI2222', 'Maharat Al-Qur’an', 2, 'Islamic Studies Education', 0),
('DDWI3232', 'Guidance and Counselling', 2, 'Islamic Studies Education', 0),
('DDWI3242', 'Arabic Grammar III', 2, 'Islamic Studies Education', 0),
('DDWI3252', 'Introduction to Islamic Da\'wah', 2, 'Islamic Studies Education', 0),
('DDWI3262', 'Verses and Hadith Related to Legislation', 2, 'Islamic Studies Education', 0),
('DDWI3272', 'Fiqh Mirath', 2, 'Islamic Studies Education', 0),
('DDWI3282', 'Islamic Civilizational Heritage in the Malay World', 2, 'Islamic Studies Education', 0),
('DDWI3291', 'Micro Teaching', 1, 'Islamic Studies Education', 0),
('DDWI3308', 'Practicum', 8, 'Islamic Studies Education', 0),
('DSPA1012', 'Introduction to Civil Engineering', 2, 'Civil Engineering', 0),
('DSPA1023', 'Engineering Drawing and Building Information Modelling', 3, 'Civil Engineering', 0),
('DSPA1033', 'Engineering Surveying', 3, 'Civil Engineering', 0),
('DSPA1041', 'Survey Camp', 1, 'Civil Engineering', 0),
('DSPA1052', 'Mechanical and Electrical Systems', 2, 'Civil Engineering', 0),
('DSPA1062', 'Occupational Safety and Health', 2, 'Civil Engineering', 0),
('DSPA1213', 'Materials and Construction', 3, 'Civil Engineering', 0),
('DSPA1513', 'Fluid Mechanics', 3, 'Civil Engineering', 0),
('DSPA1522', 'Hydrology', 2, 'Civil Engineering', 0),
('DSPA1613', 'Engineering Mechanics', 3, 'Civil Engineering', 0),
('DSPA1623', 'Mechanics of Materials and Structures', 3, 'Civil Engineering', 0),
('DSPA2012', 'Civil Engineering Laboratory 1', 2, 'Civil Engineering', 0),
('DSPA2022', 'Civil Engineering Laboratory 2', 2, 'Civil Engineering', 0),
('DSPA2032', 'Final Year Project 1', 2, 'Civil Engineering', 0),
('DSPA2223', 'Contract and Estimation', 3, 'Civil Engineering', 0),
('DSPA2313', 'Environmental Engineering', 3, 'Civil Engineering', 0),
('DSPA2413', 'Soil Mechanics', 3, 'Civil Engineering', 0),
('DSPA2423', 'Geotechnical Engineering', 3, 'Civil Engineering', 0),
('DSPA2533', 'Hydraulics', 3, 'Civil Engineering', 0),
('DSPA2633', 'Theory of Structure', 3, 'Civil Engineering', 0),
('DSPA2643', 'Structural Steel Design', 3, 'Civil Engineering', 0),
('DSPA2653', 'Reinforced Concrete Design', 3, 'Civil Engineering', 0),
('DSPA2713', 'Highway and Traffic Engineering', 3, 'Civil Engineering', 0),
('DSPA3908', 'Industrial Training', 8, 'Civil Engineering', 0),
('DSPD1223', 'Computer Organization and Assembly Language', 3, 'Computer Science', 0),
('DSPD1243', 'Digital Logic', 3, 'Computer Science', 0),
('DSPD1483', 'Database', 3, '', 0),
('DSPD1573', 'Programming Fundamental', 3, 'Computer Science', 0),
('DSPD1603', 'C++ Programming', 3, 'Computer Science', 0),
('DSPD1683', 'Introduction to Computer Science', 3, 'Computer Science', 0),
('DSPD1693', 'Discrete Mathematics', 3, '', 0),
('DSPD1703', 'Web Programming', 3, 'Computer Science', 0),
('DSPD1733', 'Data Structures and Algorithms', 3, 'Computer Science', 0),
('DSPD2213', 'Ethics in Computing', 3, 'Computer Science', 0),
('DSPD2334', 'WEB PROGRAMMING', 3, '', 0),
('DSPD2343', 'Computer Security', 3, 'Computer Science', 0),
('DSPD2353', 'IT Support and Maintenance', 3, '', 0),
('DSPD2453', 'System Analysis and Design Methods', 3, 'Computer Science', 0),
('DSPD2533', 'Computer Graphics', 3, '', 0),
('DSPD2543', 'Digital Audio Video', 3, 'Computer Science', 0),
('DSPD2563', 'Computer Animation', 3, 'Computer Science', 0),
('DSPD2623', 'Object-Oriented Programming Using Java', 3, 'Computer Science', 0),
('DSPD2653', 'VB.NET Programming', 3, 'Computer Science', 0),
('DSPD2663', 'Operating System', 3, 'Computer Science', 0),
('DSPD2673', 'Data Communication and Networking', 3, 'Computer Science', 0),
('DSPD2713', 'Mobile Programming', 3, '', 0),
('DSPD2763', 'Human Computer Interaction', 3, 'Computer Science', 0),
('DSPD2783', 'Current Topics in Computer Science', 3, 'Computer Science', 0),
('DSPD2794', 'Project', 4, 'Computer Science', 0),
('DSPD3908', 'Industrial Training', 8, 'Computer Science', 0),
('DSPE1002', 'Introduction to Electrical Engineering', 2, 'Electrical/Electronic Engineering', 0),
('DSPE1013', 'Electrical Circuit Analysis 1', 3, 'Electrical/Electronic Engineering', 0),
('DSPE1023', 'Digital Electronic System', 3, 'Electrical/Electronic Engineering', 0),
('DSPE1033', 'Electronics Devices and Circuit', 3, 'Electrical/Electronic Engineering', 0),
('DSPE1043', 'Electronic Instrumentation and Measurement', 3, 'Electrical/Electronic Engineering', 0),
('DSPE1053', 'Differential Equation', 3, 'Electrical/Electronic Engineering', 0),
('DSPE1902', 'Electrical and Electronics Workshop', 2, 'Electrical/Electronic Engineering', 0),
('DSPE1912', 'Electrical and Electronic Laboratory 1', 2, 'Electrical/Electronic Engineering', 0),
('DSPE1922', 'Programmable Logic Controller', 2, 'Electrical/Electronic Engineering', 0),
('DSPE2002', 'Engineering Management Principles', 2, 'Electrical/Electronic Engineering', 0),
('DSPE2023', 'Electrical Circuit Analysis 2', 3, 'Electrical/Electronic Engineering', 0),
('DSPE2043', 'Scientific Programming', 3, 'Electrical/Electronic Engineering', 0),
('DSPE2053', 'Microprocessor', 3, 'Electrical/Electronic Engineering', 0),
('DSPE2063', 'C Programming for Engineers', 3, 'Electrical/Electronic Engineering', 0),
('DSPE2072', 'Industrial Automation', 2, 'Electronic Engineering', 0),
('DSPE2073', 'Network and Systems', 3, 'Electrical/Electronic Engineering', 0),
('DSPE2083', 'Electronic Manufacturing Process', 3, 'Electronic Engineering', 0),
('DSPE2093', 'Industrial Electronic', 3, 'Electronic Engineering', 0),
('DSPE2102', 'Advance Electronic Communication System', 2, 'Electronic Engineering', 0),
('DSPE2113', 'Digital Interfacing', 3, 'Electronic Engineering', 0),
('DSPE2902', 'Electrical and Electronic Laboratory 2', 2, 'Electrical/Electronic Engineering', 0),
('DSPE2912', 'Final Year Project 1', 2, 'Electronic Engineering', 0),
('DSPE2922', 'Final Year Project 2', 2, 'Electronic Engineering', 0),
('DSPE2932', 'Electronic Laboratory', 2, 'Electronic Engineering', 0),
('DSPE3908', 'Industrial Training', 8, 'Electronic Engineering', 0),
('DSPF1013', 'Principles of Economics', 3, 'Property Management', 0),
('DSPF1123', 'Introduction to Accounting and Finance', 3, 'Property Management', 0),
('DSPF1313', 'Valuation Mathematics', 3, 'Property Management', 0),
('DSPF1413', 'Valuation Principles', 3, 'Property Management', 0),
('DSPF1423', 'Valuation Methodology', 3, 'Property Management', 0),
('DSPF1433', 'Investment Valuation', 3, 'Property Management', 0),
('DSPF1513', 'Building Technology', 3, 'Property Management', 0),
('DSPF1523', 'Building Services', 3, 'Property Management', 0),
('DSPF1613', 'Malaysian Legal System', 3, 'Property Management', 0),
('DSPF1623', 'Law of Contract, Agency and Torts', 3, 'Property Management', 0),
('DSPF1733', 'Real Estate Marketing Practice', 3, 'Property Management', 0),
('DSPF2053', 'Real Estate Economics', 3, 'Property Management', 0),
('DSPF2253', 'Computer Application in Real Estate', 3, 'Property Management', 0),
('DSPF2343', 'Property Management', 3, 'Property Management', 0),
('DSPF2363', 'Real Estate Agency Practice', 3, 'Property Management', 0),
('DSPF2443', 'Applied Valuation', 3, 'Property Management', 0),
('DSPF2453', 'Statutory Valuation', 3, 'Property Management', 0),
('DSPF2543', 'Building Maintenance', 3, 'Property Management', 0),
('DSPF2643', 'Real Estate Law', 3, 'Property Management', 0),
('DSPF2653', 'Real Estate Development Law', 3, 'Property Management', 0),
('DSPF2663', 'Professional Practice', 3, 'Property Management', 0),
('DSPF2743', 'Surveying and Computation', 3, 'Property Management', 0),
('DSPF2753', 'Introduction to Land Development', 3, 'Property Management', 0),
('DSPF2843', 'Urban Planning and Development Control', 3, 'Property Management', 0),
('DSPF3908', 'Industrial Training', 8, 'Property Management', 0),
('DSPG1113', 'Principles of Management', 3, 'Technology Management', 0),
('DSPG1123', 'Technology Management', 3, 'Technology Management', 0),
('DSPG1133', 'Organizational Behavior', 3, 'Technology Management', 0),
('DSPG1143', 'Quality Management', 3, 'Technology Management', 0),
('DSPG1153', 'Business Communication', 3, 'Technology Management', 0),
('DSPG1313', 'Introduction to Intellectual Property', 3, 'Technology Management', 0),
('DSPG1323', 'Business Law', 3, 'Accounting', 0),
('DSPG1333', 'Partnership and Company Law', 3, 'Accounting', 0),
('DSPG1413', 'Principles of Microeconomics', 3, 'Technology Management', 0),
('DSPG1423', 'Principles of Macroeconomics', 3, 'Technology Management', 0),
('DSPG1513', 'Principles of Marketing', 3, 'Technology Management', 0),
('DSPG1613', 'Introduction to Operations Management', 3, 'Technology Management', 0),
('DSPG2163', 'Human Resource Management', 3, 'Technology Management', 0),
('DSPG2233', 'Business Analytics', 3, 'Technology Management', 0),
('DSPG2323', 'Commercial Law', 3, 'Technology Management', 0),
('DSPG2523', 'Marketing for Innovative Product', 3, 'Technology Management', 0),
('DSPG2533', 'Innovation Management', 3, 'Technology Management', 0),
('DSPG2623', 'International Business and Globalization', 3, 'Technology Management', 0),
('DSPG2633', 'Supply Chain Management', 3, 'Technology Management', 0),
('DSPG2713', 'Technology Entrepreneurship', 3, 'Technology Management', 0),
('DSPG2723', 'Technology Commercialization', 3, 'Technology Management', 0),
('DSPG2733', 'Technology Financing', 3, 'Technology Management', 0),
('DSPG2905', 'Industrial Training', 5, 'Technology Management', 0),
('DSPJ1203', 'Statics', 3, 'Mechanical Engineering', 0),
('DSPJ1213', 'Dynamics', 3, 'Mechanical Engineering', 0),
('DSPJ1413', 'Thermodynamics', 3, 'Mechanical Engineering', 0),
('DSPJ1503', 'Engineering Drawing', 3, 'Mechanical Engineering', 0),
('DSPJ1513', 'Introduction to Design', 3, 'Mechanical Engineering', 0),
('DSPJ1902', 'Introduction to Mechanical Engineering', 2, 'Mechanical Engineering', 0),
('DSPJ1912', 'Experimental Method', 2, 'Mechanical Engineering', 0),
('DSPJ1922', 'Mechanical Workshop Practice', 2, 'Mechanical Engineering', 0),
('DSPJ1932', 'Mechanical Workshop Technology', 2, 'Mechanical Engineering', 0),
('DSPJ2013', 'Programming for Engineer', 3, 'Mechanical Engineering', 0),
('DSPJ2113', 'Solid Mechanics', 3, 'Mechanical Engineering', 0),
('DSPJ2123', 'Applied Solid Mechanics', 3, 'Mechanical Engineering', 0),
('DSPJ2213', 'Mechanics of Machine', 3, 'Mechanical Engineering', 0),
('DSPJ2303', 'Fluid Mechanics', 3, 'Mechanical Engineering', 0),
('DSPJ2313', 'Applied Fluid Mechanics', 3, 'Mechanical Engineering', 0),
('DSPJ2423', 'Applied Thermodynamics', 3, 'Mechanical Engineering', 0),
('DSPJ2502', 'Final Year Project 1', 2, 'Mechanical Engineering', 0),
('DSPJ2512', 'Final Year Project 2', 2, 'Mechanical Engineering', 0),
('DSPJ2603', 'Materials Science', 3, 'Mechanical Engineering', 0),
('DSPJ2703', 'Manufacturing Process', 3, 'Mechanical Engineering', 0),
('DSPJ2802', 'Occupational Safety and Health', 2, 'Mechanical Engineering', 0),
('DSPJ2813', 'Industrial Engineering', 3, 'Mechanical Engineering', 0),
('DSPJ2912', 'Engineering Laboratory 1', 2, 'Mechanical Engineering', 0),
('DSPJ2922', 'Engineering Laboratory 2', 2, 'Mechanical Engineering', 0),
('DSPJ3908', 'Industrial Training', 8, 'Mechanical Engineering', 0),
('DSPK1012', 'Electronics', 2, 'Mechanical Engineering', 0),
('DSPK1022', 'Basic Electrical Engineering', 2, 'Mechanical Engineering', 0),
('DSPK2003', 'Electrical Machine', 3, 'Electrical Engineering', 0),
('DSPK2013', 'Power System and Renewable Energy', 3, 'Electrical Engineering', 0),
('DSPK2023', 'Electrical Installation', 3, 'Electrical Engineering', 0),
('DSPK2033', 'Electrical Engineering System', 3, 'Electrical Engineering', 0),
('DSPK2042', 'Electrical Transportation System', 2, 'Electrical Engineering', 0),
('DSPK2052', 'Occupational Safety and Health', 2, 'Electrical Engineering', 0),
('DSPK2062', 'Control System', 2, 'Electrical Engineering', 0),
('DSPK2103', 'Electrical Principle', 3, 'Chemical Engineering', 0),
('DSPK2902', 'Electrical Laboratory', 2, 'Electrical Engineering', 0),
('DSPK2912', 'Final Year Project 1', 2, 'Electrical Engineering', 0),
('DSPK2922', 'Final Year Project 2', 2, 'Electrical Engineering', 0),
('DSPK3908', 'Industrial Training', 8, 'Electrical Engineering', 0),
('DSPL1103', 'Basic Surveying', 3, 'Land Surveying', 0),
('DSPL1133', 'Engineering Survey', 3, 'Land Surveying', 0),
('DSPL1203', 'Introduction to Geomatics', 3, 'Land Surveying', 0),
('DSPL1413', 'Field Astronomy', 3, 'Land Surveying', 0),
('DSPL1424', 'Geodesy', 4, 'Land Surveying', 0),
('DSPL1613', 'Computer Aided Design for Surveyors', 3, 'Land Surveying', 0),
('DSPL2154', 'Engineering Survey Technology', 4, 'Land Surveying', 0),
('DSPL2214', 'Cadastral Survey', 4, 'Land Surveying', 0),
('DSPL2233', 'Land Administration', 3, 'Land Surveying', 0),
('DSPL2323', 'Geographical Information System', 3, 'Land Surveying', 0),
('DSPL2333', 'Photogrammetry', 3, 'Land Surveying', 0),
('DSPL2453', 'Satellite Positioning', 3, 'Land Surveying', 0),
('DSPL2623', 'Computer Programming', 3, 'Land Surveying', 0),
('DSPL2633', 'Survey Adjustment', 3, 'Land Surveying', 0),
('DSPL3143', 'Hydrographic Surveying', 3, 'Land Surveying', 0),
('DSPL3223', 'Cadastral Practice', 3, 'Land Surveying', 0),
('DSPL3313', 'Cartography', 3, 'Land Surveying', 0),
('DSPL3343', 'Remote Sensing', 3, 'Land Surveying', 0),
('DSPL3363', 'Underground Utility Surveying', 3, 'Land Surveying', 0),
('DSPL3512', 'Survey Camp', 2, 'Land Surveying', 0),
('DSPL3908', 'Industrial Training', 8, 'Land Surveying', 0),
('DSPN1113', 'Introduction to Planning', 3, 'Urban Planning', 0),
('DSPN1116', 'Studio 1: Basic Planning Design', 6, 'Urban Planning', 0),
('DSPN1123', 'Site Planning', 3, 'Urban Planning', 0),
('DSPN1133', 'Information and Communications Technology in Planning', 3, 'Urban Planning', 0),
('DSPN1213', 'Planning Survey Techniques', 3, 'Urban Planning', 0),
('DSPN1223', 'Land Use Planning', 3, 'Urban Planning', 0),
('DSPN1226', 'Studio 2: Layout 1 (Housing)', 6, 'Urban Planning', 0),
('DSPN1313', 'Urban Planning Topical Study 1', 3, 'Urban Planning', 0),
('DSPN1323', 'Planning and Environment', 3, 'Urban Planning', 0),
('DSPN2113', 'Community Planning and Housing', 3, 'Urban Planning', 0),
('DSPN2116', 'Studio 3: Layout 2 (Mixed Development and Development Proposal Report)', 6, 'Urban Planning', 0),
('DSPN2123', 'Urban Design', 3, 'Urban Planning', 0),
('DSPN2133', 'Urban Engineering', 3, 'Urban Planning', 0),
('DSPN2143', 'Rural Planning and Development', 3, 'Urban Planning', 0),
('DSPN2213', 'Transportation Planning', 3, 'Urban Planning', 0),
('DSPN2223', 'Urban Economics', 3, 'Urban Planning', 0),
('DSPN2226', 'Studio 4: Urban Area Improvement Study', 6, 'Urban Planning', 0),
('DSPN2233', 'Geo-Spatial Information In Planning', 3, 'Urban Planning', 0),
('DSPN2243', 'Planning Law and Practice', 3, 'Urban Planning', 0),
('DSPN2313', 'Urban Planning Topical Study 2', 3, 'Urban Planning', 0),
('DSPN3908', 'Industrial Training', 8, 'Urban Planning', 0),
('DSPP1013', 'Business Accounting', 3, 'Technology Management', 0),
('DSPP1113', 'Financial Accounting and Reporting 1', 3, 'Accounting', 0),
('DSPP1123', 'Financial Accounting and Reporting 2', 3, 'Accounting', 0),
('DSPP1213', 'Islamic Financial System', 3, 'Accounting', 0),
('DSPP1314', 'Computer Application in Accounting', 4, 'Accounting', 0),
('DSPP2133', 'Financial Accounting and Reporting 3', 3, 'Accounting', 0),
('DSPP2143', 'Management Accounting 1', 3, 'Accounting', 0),
('DSPP2153', 'Financial Accounting and Reporting 4', 3, 'Accounting', 0),
('DSPP2163', 'Management Accounting 2', 3, 'Accounting', 0),
('DSPP2173', 'Financial Accounting and Reporting 5', 3, 'Accounting', 0),
('DSPP2223', 'Financial Management', 3, 'Accounting', 0),
('DSPP2324', 'Accounting Information System', 4, 'Accounting', 0),
('DSPP2513', 'Taxation 1', 3, 'Accounting', 0),
('DSPP2523', 'Taxation 2', 3, 'Accounting', 0),
('DSPP2613', 'Audit 1', 3, 'Accounting', 0),
('DSPP2623', 'Audit 2', 3, 'Accounting', 0),
('DSPP2713', 'Community Financial Awareness', 3, 'Accounting', 0),
('DSPQ1113', 'Construction Technology 1', 3, 'Quantity Surveying', 0),
('DSPQ1122', 'Construction Drawing', 2, 'Quantity Surveying', 0),
('DSPQ1133', 'Construction Materials', 3, 'Quantity Surveying', 0),
('DSPQ1143', 'Construction Technology 2', 3, 'Quantity Surveying', 0),
('DSPQ1152', 'Building Services 1', 2, 'Quantity Surveying', 0),
('DSPQ1162', 'Building Services 2', 2, 'Quantity Surveying', 0),
('DSPQ1213', 'Construction Measurement 1', 3, 'Quantity Surveying', 0),
('DSPQ1223', 'Construction Measurement 2', 3, 'Quantity Surveying', 0),
('DSPQ1233', 'Construction Measurement 3', 3, 'Quantity Surveying', 0),
('DSPQ1312', 'Principles of Economics', 2, 'Quantity Surveying', 0),
('DSPQ1323', 'Building Economics', 3, 'Quantity Surveying', 0),
('DSPQ1713', 'Construction Information Technology', 3, 'Quantity Surveying', 0),
('DSPQ1722', 'Data Analysis', 2, 'Quantity Surveying', 0),
('DSPQ2173', 'Construction Technology 3', 3, 'Quantity Surveying', 0),
('DSPQ2243', 'Construction Measurement 4', 3, 'Quantity Surveying', 0),
('DSPQ2253', 'Civil Engineering Measurement', 3, 'Quantity Surveying', 0),
('DSPQ2333', 'Cost Planning and Cost Control', 3, 'Quantity Surveying', 0),
('DSPQ2342', 'Cost Estimating 1', 2, 'Quantity Surveying', 0),
('DSPQ2352', 'Cost Estimating 2', 2, 'Quantity Surveying', 0),
('DSPQ2413', 'Principles of Law, Contract and Tort', 3, 'Quantity Surveying', 0),
('DSPQ2424', 'Construction Law and Contract', 4, 'Quantity Surveying', 0),
('DSPQ2513', 'Professional Practice 1', 3, 'Quantity Surveying', 0),
('DSPQ2523', 'Professional Practice 2', 3, 'Quantity Surveying', 0),
('DSPQ2613', 'Project Management in Construction', 3, 'Quantity Surveying', 0),
('DSPQ2734', 'Final Year Project', 4, 'Quantity Surveying', 0),
('DSPQ2742', 'Structure and Survey', 2, 'Quantity Surveying', 0),
('DSPQ3908', 'Industrial Training', 8, 'Quantity Surveying', 0),
('DSPR1116', 'Fundamental Design 1', 6, 'Architecture', 0),
('DSPR1126', 'Fundamental Design 2', 6, 'Architecture', 0),
('DSPR1212', 'Architectural Communication', 2, 'Architecture', 0),
('DSPR1222', 'Graphic and Digital Communication', 2, 'Architecture', 0),
('DSPR1313', 'Architecture History and Theory', 3, 'Architecture', 0),
('DSPR1323', 'Theory of Design', 3, 'Architecture', 0),
('DSPR1413', 'Structure and Construction 1', 3, 'Architecture', 0),
('DSPR1423', 'Construction Practice', 3, 'Architecture', 0),
('DSPR2138', 'Design 1', 8, 'Architecture', 0),
('DSPR2148', 'Design 2', 8, 'Architecture', 0),
('DSPR2232', 'Working Drawing 1', 2, 'Architecture', 0),
('DSPR2242', 'Working Drawing 2', 2, 'Architecture', 0),
('DSPR2333', 'Building Services 1', 3, 'Architecture', 0),
('DSPR2343', 'Environmental Science and Sustainability', 3, 'Architecture', 0),
('DSPR2433', 'Architectural Heritage of Malaysia', 3, 'Architecture', 0),
('DSPR2443', 'Structure and Construction 2', 3, 'Architecture', 0),
('DSPR2532', 'Basic Architectural Computing', 2, 'Architecture', 0),
('DSPR3158', 'Design 3', 8, 'Architecture', 0),
('DSPR3253', 'Working Drawing 3', 3, 'Architecture', 0),
('DSPR3353', 'Building Services 2', 3, 'Architecture', 0),
('DSPR3452', 'Architectural Leadership and Entrepreneurship', 2, 'Architecture', 0),
('DSPR3552', 'Design Competition', 2, 'Architecture', 0),
('DSPR3652', 'Design Portfolio', 2, 'Architecture', 0),
('DSPS1003', 'Fundamental Mathematics for Social Science', 3, 'General', 0),
('DSPS1013', 'Mathematics For Computer Science', 3, 'General Computing', 0),
('DSPS1023', 'Engineering Mathematics 1', 3, 'General Engineering', 0),
('DSPS1132', 'Mathematic for Surveyor 1', 2, 'General Surveying', 0),
('DSPS1133', 'Engineering Mathematics 2', 3, 'General Engineering', 0),
('DSPS1142', 'Mathematic for Surveyor 2', 2, 'General Surveying', 0),
('DSPS1213', 'Business Mathematics', 3, 'General Business', 0),
('DSPS1712', 'Physics', 2, 'General Engineering', 0),
('DSPS1713', 'Physics', 3, 'General Engineering', 0),
('DSPS1733', 'Physics For Surveyors', 3, 'General Surveying', 0),
('DSPS2043', 'Mathematic for Surveyor 3', 3, 'General Surveying', 0),
('DSPS2223', 'Business Statistics', 3, 'General Business', 0),
('DSPS2313', 'Statistics', 3, 'General', 0),
('DSPT1012', 'Introduction to Chemical Engineering', 2, 'Chemical Engineering', 0),
('DSPT1023', 'Statics', 3, 'Chemical Engineering', 0),
('DSPT1113', 'Mass Balance', 3, 'Chemical Engineering', 0),
('DSPT1123', 'Energy Balance', 3, 'Chemical Engineering', 0),
('DSPT1133', 'Thermodynamics', 3, 'Chemical Engineering', 0),
('DSPT1213', 'Material Engineering', 3, 'Chemical Engineering', 0),
('DSPT1512', 'Process Control and Instrumentation', 2, 'Chemical Engineering', 0),
('DSPT1613', 'Chemistry', 3, 'Chemical Engineering', 0),
('DSPT1711', 'Thermodynamic and Material Engineering Laboratory', 1, 'Chemical Engineering', 0),
('DSPT2033', 'Engineering Drawing', 3, 'Chemical Engineering', 0),
('DSPT2143', 'Fluid Mechanics', 3, 'Chemical Engineering', 0),
('DSPT2223', 'Chemical Reaction Engineering', 3, 'Chemical Engineering', 0),
('DSPT2313', 'Transport Process', 3, 'Chemical Engineering', 0),
('DSPT2322', 'Refinery and Petrochemical Technology', 2, 'Chemical Engineering', 0),
('DSPT2333', 'Separation Processes', 3, 'Chemical Engineering', 0),
('DSPT2413', 'Environmental Engineering', 3, 'Chemical Engineering', 0),
('DSPT2523', 'Computer Engineering', 3, 'Chemical Engineering', 0),
('DSPT2532', 'Plant Operation and Maintenance', 2, 'Chemical Engineering', 0),
('DSPT2542', 'Occupational Safety and Health', 2, 'Chemical Engineering', 0),
('DSPT2623', 'Analytical Chemistry', 3, 'Chemical Engineering', 0),
('DSPT2721', 'Fluid Mechanics Laboratory', 1, 'Chemical Engineering', 0),
('DSPT2731', 'Chemical Reaction and Environmental Engineering Laboratory', 1, 'Chemical Engineering', 0),
('DSPT2741', 'Unit Operation Laboratory', 1, 'Chemical Engineering', 0),
('DSPT2802', 'Final Year Project 1', 2, 'Chemical Engineering', 0),
('DSPT2812', 'Final Year Project 2', 2, 'Chemical Engineering', 0),
('DSPT3908', 'Industrial Training', 8, 'Chemical Engineering', 0),
('DSPU1013', 'Human Anatomy and Physiology', 3, 'Sport and Fitness', 0),
('DSPU1023', 'Mechanics of Sport Movement', 3, 'Sport and Fitness', 0),
('DSPU1103', 'Health, Fitness and Sport', 3, 'Sport and Fitness', 0),
('DSPU1113', 'Psychology for Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU1203', 'Recreation and Outdoor Education', 3, 'Sport and Fitness', 0),
('DSPU1303', 'Motor Development and Skill Acquisition', 3, 'Sport and Fitness', 0),
('DSPU1313', 'Sport Massage', 3, 'Sport and Fitness', 0),
('DSPU1323', 'Racket Sports', 3, 'Sport and Fitness', 0),
('DSPU1333', 'Striking, Fielding and Target Sports', 3, 'Sport and Fitness', 0),
('DSPU2103', 'Coaching for Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU2113', 'Basic Sport and Fitness Performance Analysis', 3, 'Sport and Fitness', 0),
('DSPU2123', 'Sport and Fitness Management', 3, 'Sport and Fitness', 0),
('DSPU2133', 'First Aid and Injuries in Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU2203', 'Sport and Fitness for Special Population', 3, 'Sport and Fitness', 0),
('DSPU2206', 'Industrial Training', 6, 'Sport and Fitness', 0),
('DSPU2213', 'Training Methodology in Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU2303', 'Self Development in Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU2313', 'Rehabilitation in Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU2323', 'Sport and Fitness Analytic and Technology', 3, 'Sport and Fitness', 0),
('DSPU2333', 'Team Sports', 3, 'Sport and Fitness', 0),
('DSPU2343', 'Innovation and Creativity in Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU3103', 'Ethics and Current Issues in Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU3113', 'Sport and Fitness Measurement', 3, 'Sport and Fitness', 0),
('DSPU3123', 'Sport and Fitness Facilities Safety', 3, 'Sport and Fitness', 0),
('DSPU3133', 'Nutrition for Sport and Fitness', 3, 'Sport and Fitness', 0),
('DSPU3203', 'Sport and Fitness Prescription', 3, 'Sport and Fitness', 0),
('DSPU3303', 'Technopreneurs in Sports and Fitness', 3, 'Sport and Fitness', 0),
('DSPU3313', 'Sport Tourism', 3, 'Sport and Fitness', 0),
('SECJ2123', 'Data Structures', 3, '', 0),
('SECJ2152', 'Software Engineering', 4, '', 0),
('SECJ2154', 'Web Programming', 3, '', 0),
('SECJ2183', 'Operating Systems', 3, '', 0),
('SECJ2213', 'Computer Networks', 3, '', 0),
('UHLB1032', 'Introductory Academic English', 2, 'General', 0),
('UHLB1042', 'Intermediate Academic English', 2, 'General', 0),
('UHLLB1032', 'Introductory Academic English (Islamic Studies)', 2, 'General', 0),
('UHLM1122', 'Malay Language for Communication 1', 2, 'General', 0),
('ULRF2XX2', 'Service Learning and Community Engagement Courses', 2, 'General', 0),
('ULRS1012', 'Value and Identity', 2, 'General', 0),
('ULRS1032', 'Integrity and Anti-Corruption Course', 2, 'General', 0),
('ULRS1182', 'Appreciation of Ethics and Civilisation', 2, 'General', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `matrix_number` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `utm_email` varchar(100) NOT NULL,
  `second_email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('student','advisor','admin') NOT NULL,
  `login_cred` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `matrix_number`, `user_name`, `utm_email`, `second_email`, `phone`, `password`, `role`, `login_cred`) VALUES
(4, 'AD235KL2', 'Ashraff Bin Muhd Hakimi', 'Ashraffhakimi@admin.utm.my', 'Ashraffpersonal@gmail.com', '102-221-221', 'pass1234', 'admin', 'ADschraffhakimi'),
(6, 'LE234AD', 'Mr Halim bin Muhammad', 'halimlect@utm.my', 'halimmihammad@gmail.com', '012-987-3670', 'Pass1234#', 'advisor', 'halimmuhammad'),
(24, 'A24DW0421', 'Haris', 'muhammadharishashim@graduate.utm.my', 'harisomniverse2@gmail.com', '012-282-3151', 'pass1234', 'student', 'muhammadharishashim'),
(26, 'A24WERT45', 'Ahmad Danish', 'Ahmad04@graduate.utm.my', 'harishashimpersonal@gmail.com', '+60 12-345 6789', '$2y$10$dAoCY6zkimBqNctpJhXErerSX86b4cIyYooKlbGc.BPdAZuqnZck6', 'student', 'ahmaddanish'),
(27, 'A34DW0432', 'Anaqi bin mohd nazri', 'anaqi06@graduate.utm.my', 'anaqi@gmail.com', '012-267-8765', '$2y$10$Q38yQOCBftc2ds2pDylvJuh8PrZVoc2ULzYGudT.P3FqSKXTJDBAq', 'student', 'anaqi'),
(28, 'PS0193', 'Miss Nurul Asyikin', 'asyikin@utmspace.edu.my', 'asyikinmuhamad8@gmail.com', '017-962-8405', '$2y$10$cZTUXZfcUmOloMLwd71E5elC4PxWQ9EWiDAI3QOmf7KqsMv9eRvSK', 'advisor', 'missnurulasyikin'),
(29, 'A34PE5678', 'Ravi A/L Daneswaran', 'ravi06@graduate.utm.my', 'ravi@gmail.com', '017-962-8405', '$2y$10$kNGxnRhmvnOItXfHeXiCzub9zWrcrbQyp4zkz1rVJpskVi0jHBprW', 'student', 'ravialdaneswaran'),
(30, 'ADM001', 'Azhar Bin Abdullah', 'azhar.abdullah@admin.utm.my', 'azhar@gmail.com', '012-3451001', 'pass1234', 'admin', 'azharabdullah'),
(31, 'ADM002', 'Siti Aishah Binti Ramli', 'siti.aishah@admin.utm.my', 'siti.aishah@gmail.com', '012-3451002', 'pass1234', 'admin', 'sitiaisah'),
(32, 'ADM003', 'Muhammad Fauzi Bin Hassan', 'fauzi.hassan@admin.utm.my', 'fauzi@gmail.com', '012-3451003', 'pass1234', 'admin', 'fauzihassan'),
(33, 'ADM004', 'Nurul Huda Binti Zakaria', 'nurul.huda@admin.utm.my', 'nurulhuda@gmail.com', '012-3451004', 'pass1234', 'admin', 'nurulhuda'),
(34, 'ADM005', 'Rashid Bin Omar', 'rashid.omar@admin.utm.my', 'rashid@gmail.com', '012-3451005', 'pass1234', 'admin', 'rashidomar'),
(35, 'ADM006', 'Zainab Binti Ahmad', 'zainab.ahmad@admin.utm.my', 'zainab@gmail.com', '012-3451006', 'pass1234', 'admin', 'zainabahmad'),
(36, 'ADM007', 'Firdaus Bin Ismail', 'firdaus.ismail@admin.utm.my', 'firdaus@gmail.com', '012-3451007', 'pass1234', 'admin', 'firdausismail'),
(37, 'ADM008', 'Hasmah Binti Sulaiman', 'hasmah.sulaiman@admin.utm.my', 'hasmah@gmail.com', '012-3451008', 'pass1234', 'admin', 'hasmahsulaiman'),
(38, 'ADM009', 'Shahrul Bin Mohd Noor', 'shahrul.noor@admin.utm.my', 'shahrul@gmail.com', '012-3451009', 'pass1234', 'admin', 'shahrulnoor'),
(39, 'ADM010', 'Roslina Binti Yusof', 'roslina.yusof@admin.utm.my', 'roslina@gmail.com', '012-3451010', 'pass1234', 'admin', 'roslinayusof'),
(40, 'LEC001', 'Dr. Ahmad Tarmizi Bin Mohd', 'tarmizi@utm.my', 'tarmizi@gmail.com', '013-3452001', 'pass1234', 'advisor', 'tarmizi'),
(41, 'LEC002', 'Prof. Madya Salmiah Binti Idris', 'salmiah@utm.my', 'salmiah@gmail.com', '013-3452002', 'pass1234', 'advisor', 'salmiahidris'),
(42, 'LEC003', 'Ts. Mohd Redzuan Bin Ali', 'redzuan@utm.my', 'redzuan@gmail.com', '013-3452003', 'pass1234', 'advisor', 'redzuanali'),
(43, 'LEC004', 'Dr. Norazlina Binti Mustafa', 'norazlina@utm.my', 'norazlina@gmail.com', '013-3452004', 'pass1234', 'advisor', 'norazlina'),
(44, 'LEC005', 'Ts. Khairul Anuar Bin Ismail', 'khairul.ismail@utm.my', 'khairul@gmail.com', '013-3452005', 'pass1234', 'advisor', 'khairulismail'),
(45, 'LEC006', 'Dr. Faridah Binti Hamzah', 'faridah@utm.my', 'faridah@gmail.com', '013-3452006', 'pass1234', 'advisor', 'faridahhamzah'),
(46, 'LEC007', 'Encik Zulkifli Bin Rahman', 'zulkifli@utm.my', 'zulkifli@gmail.com', '013-3452007', 'pass1234', 'advisor', 'zulkiflirahman'),
(47, 'LEC008', 'Dr. Maznah Binti Abdullah', 'maznah@utm.my', 'maznah@gmail.com', '013-3452008', 'pass1234', 'advisor', 'maznahabdullah'),
(48, 'LEC009', 'Ts. Azman Bin Rashid', 'azman@utm.my', 'azman@gmail.com', '013-3452009', 'pass1234', 'advisor', 'azmanrashid'),
(49, 'LEC010', 'Dr. Suhaila Binti Othman', 'suhaila@utm.my', 'suhaila@gmail.com', '013-3452010', 'pass1234', 'advisor', 'suhailaothman'),
(50, 'A24DW0001', 'Ali Bin Abu', 'ali.abu@graduate.utm.my', 'ali@gmail.com', '014-3453001', 'pass1234', 'student', 'aliabu'),
(51, 'A24DW0002', 'Fatimah Binti Hassan', 'fatimah.hassan@graduate.utm.my', 'fatimah@gmail.com', '014-3453002', 'pass1234', 'student', 'fatimahhassan'),
(52, 'A24DW0003', 'Muhammad Idris Bin Othman', 'idris.othman@graduate.utm.my', 'idris@gmail.com', '014-3453003', 'pass1234', 'student', 'idrisothman'),
(53, 'A24DW0004', 'Nur Ain Binti Zulkifli', 'nur.ain@graduate.utm.my', 'nurain@gmail.com', '014-3453004', 'pass1234', 'student', 'nurainzulkifli'),
(54, 'A24DW0005', 'Hafiz Bin Zainal', 'hafiz.zainal@graduate.utm.my', 'hafiz@gmail.com', '014-3453005', 'pass1234', 'student', 'hafizzainal'),
(55, 'A24DW0006', 'Syarifah Sofia Binti Syed', 'sofia@graduate.utm.my', 'sofia@gmail.com', '014-3453006', 'pass1234', 'student', 'sofiasyed'),
(56, 'A24DW0007', 'Irfan Bin Rosli', 'irfan.rosli@graduate.utm.my', 'irfan@gmail.com', '014-3453007', 'pass1234', 'student', 'irfanrosli'),
(57, 'A24DW0008', 'Nadia Binti Aziz', 'nadia.aziz@graduate.utm.my', 'nadia@gmail.com', '014-3453008', 'pass1234', 'student', 'nadiaaziz'),
(58, 'A24DW0009', 'Farhan Bin Saleh', 'farhan.saleh@graduate.utm.my', 'farhan@gmail.com', '014-3453009', 'pass1234', 'student', 'farhansaleh'),
(59, 'A24DW0010', 'Nurul Izzati Binti Kamal', 'izzati.kamal@graduate.utm.my', 'izzati@gmail.com', '014-3453010', 'pass1234', 'student', 'izzati'),
(60, 'A24DW0213', 'chong wei lee', 'weilee@graduate.utm.my', 'WeiLee2@gmail.com', '011-974-378', 'pass1234', 'student', 'chongweilee');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_users_insert_admin` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'admin' THEN
        INSERT INTO admin (
            user_id,
            matrix_number,
            user_name
        ) VALUES (
            NEW.user_id,
            NEW.matrix_number,
            NEW.user_name
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_users_insert_advisor` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'advisor' THEN
        INSERT INTO advisor (
            user_id,
            advisor_name,
            matrix_number,
            utm_email,
            second_email,
            faculty,
            department
        ) VALUES (
            NEW.user_id,
            NEW.user_name,
            NEW.matrix_number,
            NEW.utm_email,
            NEW.second_email,
            'Faculty Of SPACE',   -- default faculty
            'Computer Science'    -- default department
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_users_insert_student` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'student' THEN
        INSERT INTO students (
            user_id, 
            matrix_number, 
            user_name, 
            utm_email, 
            second_email, 
            phone, 
            programme, 
            year, 
            semester, 
            advisor_id
        ) VALUES (
            NEW.user_id,
            NEW.matrix_number,
            NEW.user_name,
            NEW.utm_email,
            NEW.second_email,
            NEW.phone,
            'Computer Science',   -- default programme, change as needed
            '1',                  -- default year
            '1',                  -- default semester
            NULL                  -- advisor_id can be set later
        );
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `advisor`
--
ALTER TABLE `advisor`
  ADD KEY `fk_matrix` (`matrix_number`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `registration_cart`
--
ALTER TABLE `registration_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_code` (`subject_code`);

--
-- Indexes for table `registration_courses`
--
ALTER TABLE `registration_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `subject_code` (`subject_code`);

--
-- Indexes for table `reset_attempts`
--
ALTER TABLE `reset_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_time` (`ip`,`attempted_at`);

--
-- Indexes for table `semester_registration_periods`
--
ALTER TABLE `semester_registration_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_matrix_num` (`matrix_number`),
  ADD KEY `user_name` (`user_name`,`utm_email`,`second_email`,`phone`,`advisor_id`),
  ADD KEY `fk_student_advisor` (`advisor_id`);

--
-- Indexes for table `student_semesters`
--
ALTER TABLE `student_semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `matrix_number_2` (`matrix_number`),
  ADD UNIQUE KEY `utm_email` (`utm_email`),
  ADD UNIQUE KEY `idx_login_cred` (`login_cred`),
  ADD KEY `matrix_number` (`matrix_number`,`user_name`,`utm_email`,`second_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `registration_cart`
--
ALTER TABLE `registration_cart`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `registration_courses`
--
ALTER TABLE `registration_courses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `reset_attempts`
--
ALTER TABLE `reset_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `semester_registration_periods`
--
ALTER TABLE `semester_registration_periods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_semesters`
--
ALTER TABLE `student_semesters`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisor`
--
ALTER TABLE `advisor`
  ADD CONSTRAINT `fk_matrix` FOREIGN KEY (`matrix_number`) REFERENCES `users` (`matrix_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD CONSTRAINT `fk_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `registration_cart`
--
ALTER TABLE `registration_cart`
  ADD CONSTRAINT `fk_cart_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_subject` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`subject_code`) ON DELETE CASCADE;

--
-- Constraints for table `registration_courses`
--
ALTER TABLE `registration_courses`
  ADD CONSTRAINT `fk_rc_registration` FOREIGN KEY (`registration_id`) REFERENCES `course_registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rc_subject` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`subject_code`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_matrix_num` FOREIGN KEY (`matrix_number`) REFERENCES `users` (`matrix_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_advisor` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
