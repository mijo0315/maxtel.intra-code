-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2024 at 03:55 PM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 7.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hris_prl_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lib_bir_non_taxable`
--

CREATE TABLE `lib_bir_non_taxable` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_bir_non_taxable`
--

INSERT INTO `lib_bir_non_taxable` (`id`, `code`, `name`, `is_active`) VALUES
(1, '34', '13th Month Pay and Other Benefits\r\n', 1),
(2, '35', 'De Minimis Benefits', 1),
(3, '37', 'Salaries and Other Forms of Compensation', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_bir_taxable`
--

CREATE TABLE `lib_bir_taxable` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_bir_taxable`
--

INSERT INTO `lib_bir_taxable` (`id`, `code`, `name`, `is_active`) VALUES
(1, '40', 'Representation', 1),
(2, '41', 'Transportation', 1),
(3, '42', 'Cost of Living Allowance (COLA)', 1),
(4, '43', 'Fixed Housing Allowance', 1),
(5, '44', 'Others', 1),
(6, '45', 'Commission', 1),
(7, '46', 'Profit Sharing', 1),
(8, '48', 'Taxable 13th Month Benefits', 1),
(9, '47', 'Fees Including Director\'s Fees', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_designation`
--

CREATE TABLE `lib_designation` (
  `id` int(11) NOT NULL,
  `code` varchar(155) NOT NULL,
  `name` varchar(255) NOT NULL,
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` date DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_designation`
--

INSERT INTO `lib_designation` (`id`, `code`, `name`, `schedule_id`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, '0122', 'Team Leader', 2, 1, '2023-05-02 15:50:12', NULL, 1),
(2, '0124', 'Supervisor', 1, 1, '2023-05-02 15:50:14', '2023-04-06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_file_type`
--

CREATE TABLE `lib_file_type` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_file_type`
--

INSERT INTO `lib_file_type` (`id`, `code`, `name`) VALUES
(1, 'resume', 'Resume'),
(2, 'contract', 'Contract'),
(3, 'pds', 'Personal Data Sheet'),
(4, 'tor', 'Transacript of Records'),
(5, 'diploma', 'Diploma'),
(6, 'nbi', 'NBI Clearance'),
(7, 'brgy', 'Brgy Clearance'),
(8, 'police', 'Police Clearance'),
(9, '201 Files', '201 Files'),
(10, 'Medical Records', 'Medical Records'),
(11, 'oth', 'Others');

-- --------------------------------------------------------

--
-- Table structure for table `lib_hdmf`
--

CREATE TABLE `lib_hdmf` (
  `id` int(11) NOT NULL,
  `salary_from` double(10,2) NOT NULL,
  `salary_to` double(10,2) NOT NULL,
  `rate_employer` double(10,5) NOT NULL,
  `rate_employee` double(10,5) NOT NULL,
  `year_effect` varchar(20) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_hdmf`
--

INSERT INTO `lib_hdmf` (`id`, `salary_from`, `salary_to`, `rate_employer`, `rate_employee`, `year_effect`, `date_updated`, `user_id`) VALUES
(1, 1000.00, 1500.00, 0.02000, 0.01000, '2023', '2023-04-29 14:39:30', 2),
(2, 1500.01, 999999.00, 0.02000, 0.02000, '2023', '2023-04-29 14:36:29', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lib_income`
--

CREATE TABLE `lib_income` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(155) NOT NULL,
  `description` varchar(255) NOT NULL,
  `is_regular` int(11) NOT NULL DEFAULT 1,
  `tax_type` varchar(155) NOT NULL COMMENT 'NON | TAX',
  `tax_item` varchar(155) NOT NULL COMMENT 'if NON & 0 -> hidden; if TAX & 0 basic pay',
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_income`
--

INSERT INTO `lib_income` (`id`, `code`, `name`, `description`, `is_regular`, `tax_type`, `tax_item`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, '0011', 'Rice Allowances', 'Rice Allowances', 1, 'TAX', '7', 1, '2023-04-06 14:06:25', '2023-04-06 10:15:02', 1),
(2, '011', 'Load Allowance', 'Load Allowance', 1, 'NON', '3', 1, '2023-04-06 14:04:41', '2023-04-06 13:41:55', 1),
(3, '002', 'Bonus', 'Bonus', 0, 'NON', '1', 1, '2023-04-06 14:04:34', '2023-04-06 14:04:34', 1),
(4, 'ECOLA', 'Employee Cost Living Allowance', 'Employee Cost Living Allowance', 1, 'NON', '1', 1, '2023-10-10 06:36:47', '2023-10-10 06:36:47', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_loans`
--

CREATE TABLE `lib_loans` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `code` varchar(155) NOT NULL,
  `name` varchar(155) NOT NULL,
  `description` varchar(255) NOT NULL,
  `is_regular` int(11) NOT NULL DEFAULT 1,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_loans`
--

INSERT INTO `lib_loans` (`id`, `type`, `code`, `name`, `description`, `is_regular`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, 'SSS_SL', 'SL_SL', 'SSS Salary Loan', 'SSS Salary Loan', 1, 1, '2023-04-25 15:16:03', '2023-04-14 14:34:15', 2),
(2, 'OTH', 'SD-OTH', 'Salary Deduction', 'Salary Deduction', 0, 1, '2023-04-25 15:16:36', '2023-04-25 15:16:36', 2),
(3, 'HDMF_MPL', 'STL-PAGIBIG', 'Pag Ibig Short Term Loan', 'Pag Ibig Short Term Loan', 1, 1, '2024-11-24 11:12:04', '2024-11-24 11:12:04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_loan_type`
--

CREATE TABLE `lib_loan_type` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(155) NOT NULL,
  `is_government` int(11) NOT NULL DEFAULT 1,
  `is_active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_loan_type`
--

INSERT INTO `lib_loan_type` (`id`, `code`, `name`, `is_government`, `is_active`) VALUES
(1, 'SSS_SL', 'SSS Salary Loan', 1, 1),
(2, 'HDMF_MPL', 'PAG-IBIG Multi Purpose Loan', 1, 1),
(3, 'OTH', 'Other', 0, 1),
(4, 'GSIS_SL', 'GSIS Salary Loan', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_ot_table`
--

CREATE TABLE `lib_ot_table` (
  `id` int(11) NOT NULL,
  `code` varchar(155) NOT NULL,
  `name` varchar(255) NOT NULL,
  `rate` double(12,2) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_ot_table`
--

INSERT INTO `lib_ot_table` (`id`, `code`, `name`, `rate`, `date_updated`, `date_created`, `user_id`) VALUES
(1, 'ROT', 'Regular OT', 0.30, '2023-04-26 15:55:25', '2023-04-26 17:49:18', 2),
(2, 'SOT', 'Special OT', 0.25, '2023-04-26 15:50:05', '2023-04-26 17:49:47', 2),
(3, 'ND', 'Night Differential', 0.25, '2023-05-02 14:24:46', '2023-04-26 17:50:07', 2),
(4, 'RH', 'Regular Holiday', 1.00, '2023-04-26 15:50:35', '2023-04-26 17:50:22', 2),
(5, 'SH', 'Special Holiday', 0.30, '2023-04-26 15:51:02', '2023-04-26 17:50:41', 2);

-- --------------------------------------------------------

--
-- Table structure for table `lib_permission`
--

CREATE TABLE `lib_permission` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_permission`
--

INSERT INTO `lib_permission` (`id`, `name`, `icon`, `route`, `date_updated`, `date_created`) VALUES
(1, 'Dashboard', 'public/assets/img/home.svg', 'dashboard', '2023-03-21 14:01:21', NULL),
(2, 'Employees', 'public/assets/img/employee.svg', 'employees_management', '2023-03-21 14:01:25', NULL),
(3, 'File Management', 'public/assets/img/manage.svg', 'file_management', '2023-04-06 07:02:54', NULL),
(4, 'Payroll Management', 'public/assets/img/review.svg', 'payroll_management', '2023-04-06 07:03:18', NULL),
(5, 'Other Income Management', 'public/assets/img/dash5.png', 'income_management', '2023-04-06 07:03:26', NULL),
(6, 'Loan Management', 'public/assets/img/report.svg', 'loan_management', '2023-04-06 07:03:29', NULL),
(7, 'Timekeeping', 'public/assets/img/calendar.svg', 'timekeeping_management', '2023-04-06 07:03:52', NULL),
(8, 'Leave Management', 'public/assets/img/leave.svg', 'leave_management', '2023-04-06 07:03:58', NULL),
(9, 'Schedule Management', 'public/assets/img/calendar.svg', 'schedule_management', '2023-04-06 07:06:38', NULL),
(10, 'Statutories', 'public/assets/img/manage.svg', 'statutory_management', '2023-04-06 07:06:41', NULL),
(11, 'Report', 'public/assets/img/report.svg', 'report_management', '2023-04-06 07:06:43', NULL),
(12, 'User Management', 'public/assets/img/employee.svg', 'user_management', '2023-04-06 07:06:54', NULL),
(13, 'Permission Management', 'public/assets/img/settings.svg', 'permission_management', '2023-04-06 07:06:58', NULL),
(14, 'System Management', 'public/assets/img/settings.svg', 'system_management', '2023-04-06 07:07:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lib_philhealth`
--

CREATE TABLE `lib_philhealth` (
  `id` int(11) NOT NULL,
  `salary_from` double(10,2) NOT NULL,
  `salary_to` double(10,2) NOT NULL,
  `rate_employer` double(10,5) NOT NULL,
  `rate_employee` double(10,5) NOT NULL,
  `year_effect` varchar(20) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_philhealth`
--

INSERT INTO `lib_philhealth` (`id`, `salary_from`, `salary_to`, `rate_employer`, `rate_employee`, `year_effect`, `date_updated`, `user_id`) VALUES
(1, 10000.00, 999999.00, 0.02250, 0.02250, '2023', '2023-04-29 14:39:06', 2);

-- --------------------------------------------------------

--
-- Table structure for table `lib_position`
--

CREATE TABLE `lib_position` (
  `id` int(11) NOT NULL,
  `code` varchar(155) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'eg. Rank and File; Executive',
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_position`
--

INSERT INTO `lib_position` (`id`, `code`, `name`, `type`, `schedule_id`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, '012', 'Programmers', 'EX', 1, 1, '2023-05-02 15:49:02', '2023-03-27 15:01:23', 1),
(2, '10123', 'Science Research Analyst', 'EX', 2, 1, '2023-05-02 15:49:18', '2023-04-06 06:27:39', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_schedule`
--

CREATE TABLE `lib_schedule` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `required_hours` float(6,2) NOT NULL,
  `is_flexi` int(11) DEFAULT 0,
  `am_in` varchar(100) NOT NULL DEFAULT '00:00:00',
  `am_out` varchar(100) NOT NULL DEFAULT '00:00:00',
  `pm_in` varchar(100) NOT NULL DEFAULT '00:00:00',
  `pm_out` varchar(100) NOT NULL DEFAULT '00:00:00',
  `ot_in` varchar(100) NOT NULL DEFAULT '00:00:00',
  `ot_out` varchar(100) NOT NULL DEFAULT '00:00:00',
  `is_active` int(11) NOT NULL DEFAULT 1,
  `grace_period` varchar(155) NOT NULL DEFAULT '0' COMMENT 'in minutes',
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL,
  `user_updated` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_schedule`
--

INSERT INTO `lib_schedule` (`id`, `code`, `name`, `required_hours`, `is_flexi`, `am_in`, `am_out`, `pm_in`, `pm_out`, `ot_in`, `ot_out`, `is_active`, `grace_period`, `date_updated`, `date_created`, `user_updated`) VALUES
(1, 'ms_1', 'Morning Shift 1', 0.00, 0, '8:00:00', '12:00:00', '13:00:00', '17:00:00', '17:00:00', '23:59:00', 1, '10', '2023-03-24 16:03:59', NULL, 1),
(2, 'as_1', 'Afternoon Shift', 0.00, 0, '12:00:00', '04:00:00', '17:00:00', '21:00:00', '22:00:00', '00:00:00', 1, '5', '2023-05-04 13:29:01', NULL, 2),
(3, 'FLEX_1', 'Part Time Sched', 4.00, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', 1, '0', '2023-08-18 15:33:18', '2023-08-18 15:33:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lib_sss`
--

CREATE TABLE `lib_sss` (
  `id` int(11) NOT NULL,
  `salary_from` double(10,2) NOT NULL,
  `salary_to` double(10,2) NOT NULL,
  `credit_ec` double(10,2) NOT NULL,
  `credit_wisp` double(10,2) NOT NULL,
  `regular_er` double(10,2) NOT NULL,
  `regular_ee` double(10,2) NOT NULL,
  `ec` double(10,2) NOT NULL,
  `wisp_er` double(10,2) NOT NULL,
  `wisp_ee` double(10,2) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_sss`
--

INSERT INTO `lib_sss` (`id`, `salary_from`, `salary_to`, `credit_ec`, `credit_wisp`, `regular_er`, `regular_ee`, `ec`, `wisp_er`, `wisp_ee`, `date_updated`, `user_id`) VALUES
(1, 0.00, 4249.99, 4000.00, 0.00, 380.00, 180.00, 10.00, 0.00, 0.00, '2023-04-29 14:38:49', 2),
(2, 4250.00, 4749.99, 4500.00, 0.00, 427.50, 202.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(3, 4750.00, 5249.99, 5000.00, 0.00, 475.00, 225.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(4, 5250.00, 5749.99, 5500.00, 0.00, 522.50, 247.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(5, 5750.00, 6249.99, 6000.00, 0.00, 570.00, 270.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(6, 6250.00, 6749.99, 6500.00, 0.00, 617.50, 292.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(7, 6750.00, 7249.99, 7000.00, 0.00, 665.00, 315.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(8, 7250.00, 7749.99, 7500.00, 0.00, 712.50, 337.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(9, 7750.00, 8249.99, 8000.00, 0.00, 760.00, 360.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(10, 8250.00, 8749.99, 8500.00, 0.00, 807.50, 382.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(11, 8750.00, 9249.99, 9000.00, 0.00, 855.00, 405.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(12, 9250.00, 9749.99, 9500.00, 0.00, 902.50, 427.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(13, 9750.00, 10249.99, 10000.00, 0.00, 950.00, 450.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(14, 10250.00, 10749.99, 10500.00, 0.00, 997.50, 472.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(15, 10750.00, 11249.99, 11000.00, 0.00, 1045.00, 495.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(16, 11250.00, 11749.99, 11500.00, 0.00, 1092.50, 517.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(17, 11750.00, 12249.99, 12000.00, 0.00, 1140.00, 540.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(18, 12250.00, 12749.99, 12500.00, 0.00, 1187.50, 562.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(19, 12750.00, 13249.99, 13000.00, 0.00, 1235.00, 585.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(20, 13250.00, 13749.99, 13500.00, 0.00, 1282.50, 607.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(21, 13750.00, 14249.99, 14000.00, 0.00, 1330.00, 630.00, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(22, 14250.00, 14749.99, 14500.00, 0.00, 1377.50, 652.50, 10.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(23, 14750.00, 15249.99, 15000.00, 0.00, 1425.00, 675.00, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(24, 15250.00, 15749.99, 15500.00, 0.00, 1472.50, 697.50, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(25, 15750.00, 16249.99, 16000.00, 0.00, 1520.00, 720.00, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(26, 16250.00, 16749.99, 16500.00, 0.00, 1567.50, 742.50, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(27, 16750.00, 17249.99, 17000.00, 0.00, 1615.00, 765.00, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(28, 17250.00, 17749.99, 17500.00, 0.00, 1662.50, 787.50, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(29, 17750.00, 18249.99, 18000.00, 0.00, 1710.00, 810.00, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(30, 18250.00, 18749.99, 18500.00, 0.00, 1757.50, 832.50, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(31, 18750.00, 19249.99, 19000.00, 0.00, 1805.00, 855.00, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(32, 19250.00, 19749.99, 19500.00, 0.00, 1852.50, 877.50, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(33, 19750.00, 20249.99, 20000.00, 0.00, 1900.00, 900.00, 30.00, 0.00, 0.00, '2023-04-29 12:58:06', 0),
(34, 20250.00, 20749.99, 20000.00, 500.00, 1900.00, 900.00, 30.00, 47.50, 22.50, '2023-04-29 12:58:06', 0),
(35, 20750.00, 21249.99, 20000.00, 1000.00, 1900.00, 900.00, 30.00, 95.00, 45.00, '2023-04-29 12:58:06', 0),
(36, 21250.00, 21749.99, 20000.00, 1500.00, 1900.00, 900.00, 30.00, 142.50, 67.50, '2023-04-29 12:58:06', 0),
(37, 21750.00, 22249.99, 20000.00, 2000.00, 1900.00, 900.00, 30.00, 190.00, 90.00, '2023-04-29 12:58:06', 0),
(38, 22250.00, 22749.99, 20000.00, 2500.00, 1900.00, 900.00, 30.00, 237.50, 112.50, '2023-04-29 12:58:06', 0),
(39, 22750.00, 23249.99, 20000.00, 3000.00, 1900.00, 900.00, 30.00, 285.00, 135.00, '2023-04-29 12:58:06', 0),
(40, 23250.00, 23749.99, 20000.00, 3500.00, 1900.00, 900.00, 30.00, 332.50, 157.50, '2023-04-29 12:58:06', 0),
(41, 23750.00, 24249.99, 20000.00, 4000.00, 1900.00, 900.00, 30.00, 380.00, 180.00, '2023-04-29 12:58:06', 0),
(42, 24250.00, 24749.99, 20000.00, 4500.00, 1900.00, 900.00, 30.00, 427.50, 202.50, '2023-04-29 12:58:06', 0),
(43, 24750.00, 25249.99, 20000.00, 5000.00, 1900.00, 900.00, 30.00, 475.00, 225.00, '2023-04-29 12:58:06', 0),
(44, 25250.00, 25749.99, 20000.00, 5500.00, 1900.00, 900.00, 30.00, 522.50, 247.50, '2023-04-29 12:58:06', 0),
(45, 25750.00, 26249.99, 20000.00, 6000.00, 1900.00, 900.00, 30.00, 570.00, 270.00, '2023-04-29 12:58:06', 0),
(46, 26250.00, 26749.99, 20000.00, 6500.00, 1900.00, 900.00, 30.00, 617.50, 292.50, '2023-04-29 12:58:06', 0),
(47, 26750.00, 27249.99, 20000.00, 7000.00, 1900.00, 900.00, 30.00, 665.00, 315.00, '2023-04-29 12:58:06', 0),
(48, 27250.00, 27749.99, 20000.00, 7500.00, 1900.00, 900.00, 30.00, 712.50, 337.50, '2023-04-29 12:58:06', 0),
(49, 27750.00, 28249.99, 20000.00, 8000.00, 1900.00, 900.00, 30.00, 760.00, 360.00, '2023-04-29 12:58:06', 0),
(50, 28250.00, 28749.99, 20000.00, 8500.00, 1900.00, 900.00, 30.00, 807.50, 382.50, '2023-04-29 12:58:06', 0),
(51, 28750.00, 29249.99, 20000.00, 9000.00, 1900.00, 900.00, 30.00, 855.00, 405.00, '2023-04-29 12:58:06', 0),
(52, 29250.00, 29749.99, 20000.00, 9500.00, 1900.00, 900.00, 30.00, 902.50, 427.50, '2023-04-29 12:58:06', 0),
(53, 29750.00, 999999.00, 20000.00, 10000.00, 1900.00, 900.00, 30.00, 950.00, 450.00, '2023-04-29 12:58:06', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lib_tax_table`
--

CREATE TABLE `lib_tax_table` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `salary_from` double(12,2) NOT NULL,
  `salary_to` double(12,2) NOT NULL,
  `fix_amount` double(12,2) NOT NULL,
  `rate` double(12,2) NOT NULL,
  `rate_over` double(12,2) NOT NULL,
  `effective_year` varchar(4) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_tax_table`
--

INSERT INTO `lib_tax_table` (`id`, `type`, `salary_from`, `salary_to`, `fix_amount`, `rate`, `rate_over`, `effective_year`, `date_updated`, `user_id`) VALUES
(1, 'DAILY', 0.00, 684.99, 0.00, 0.00, 0.00, '2023', '2023-04-29 14:40:19', 2),
(2, 'DAILY', 685.00, 1095.99, 0.00, 0.15, 685.00, '2023', '2023-04-29 14:35:57', 0),
(3, 'DAILY', 1096.00, 2191.99, 82.19, 0.20, 1096.00, '2023', '2023-04-29 14:35:57', 0),
(4, 'DAILY', 2192.00, 5478.99, 356.16, 0.25, 2192.00, '2023', '2023-04-29 14:35:57', 0),
(5, 'DAILY', 5479.00, 21917.99, 1342.47, 0.30, 5479.00, '2023', '2023-04-29 14:35:57', 0),
(6, 'DAILY', 21918.00, 999999999.00, 6602.74, 0.35, 21918.00, '2023', '2023-04-29 14:35:57', 0),
(7, 'WEEKLY', 0.00, 4807.99, 0.00, 0.00, 0.00, '2023', '2023-04-29 14:35:57', 0),
(8, 'WEEKLY', 4808.00, 7691.99, 0.00, 0.15, 4808.00, '2023', '2023-04-29 14:35:57', 0),
(9, 'WEEKLY', 7692.00, 15384.99, 576.92, 0.20, 7692.00, '2023', '2023-04-29 14:35:57', 0),
(10, 'WEEKLY', 15385.00, 38461.99, 2500.00, 0.25, 15385.00, '2023', '2023-04-29 14:35:57', 0),
(11, 'WEEKLY', 38462.00, 153845.99, 9423.08, 0.30, 38462.00, '2023', '2023-04-29 14:35:57', 0),
(12, 'WEEKLY', 153846.00, 999999999.00, 46346.15, 0.35, 153846.00, '2023', '2023-04-29 14:35:57', 0),
(13, 'SEMI', 0.00, 10416.99, 0.00, 0.00, 0.00, '2023', '2023-04-29 14:35:57', 0),
(14, 'SEMI', 10417.00, 16666.99, 0.00, 0.15, 10417.00, '2023', '2023-04-29 14:35:57', 0),
(15, 'SEMI', 16667.00, 33332.99, 1250.00, 0.20, 1667.00, '2023', '2023-04-29 14:35:57', 0),
(16, 'SEMI', 33333.00, 83332.99, 5416.67, 0.25, 33333.00, '2023', '2023-04-29 14:35:57', 0),
(17, 'SEMI', 83333.00, 333332.99, 20416.67, 0.30, 83333.00, '2023', '2023-04-29 14:35:57', 0),
(18, 'SEMI', 333333.00, 999999999.00, 100416.67, 0.35, 333333.00, '2023', '2023-04-29 14:35:57', 0),
(19, 'MONTHLY', 0.00, 20832.99, 0.00, 0.00, 0.00, '2023', '2023-04-29 14:35:57', 0),
(20, 'MONTHLY', 20833.00, 33332.99, 0.00, 0.15, 20833.00, '2023', '2023-04-29 14:35:57', 0),
(21, 'MONTHLY', 33333.00, 66666.99, 2500.00, 0.20, 33333.00, '2023', '2023-04-29 14:35:57', 0),
(22, 'MONTHLY', 66667.00, 166666.99, 10833.33, 0.25, 66667.00, '2023', '2023-04-29 14:35:57', 0),
(23, 'MONTHLY', 166667.00, 666666.99, 40833.33, 0.30, 166667.00, '2023', '2023-04-29 14:35:57', 0),
(24, 'MONTHLY', 666667.00, 999999999.00, 200833.33, 0.35, 666667.00, '2023', '2023-04-29 14:35:57', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lib_week_schedule`
--

CREATE TABLE `lib_week_schedule` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `monday` varchar(100) NOT NULL,
  `tuesday` varchar(100) NOT NULL,
  `wednesday` varchar(100) NOT NULL,
  `thursday` varchar(100) NOT NULL,
  `friday` varchar(100) NOT NULL,
  `saturday` varchar(100) NOT NULL,
  `sunday` varchar(100) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `lib_week_schedule`
--

INSERT INTO `lib_week_schedule` (`id`, `code`, `name`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, 'def_sched', 'Default Schedule', '1', '1', '1', '1', '1', '0', '0', 1, '2023-03-24 15:41:55', NULL, 1),
(2, 'guard', 'Guard Schedule', '1', '1', '1', '1', '1', '1', '1', 1, '2023-03-24 15:22:37', NULL, 1),
(3, 'FLEXI', 'FLEXI SCHEDULE', '3', '3', '3', '3', '3', '0', '0', 1, '2023-08-18 15:59:31', '2023-08-18 15:59:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_branch`
--

CREATE TABLE `tbl_branch` (
  `id` int(11) NOT NULL,
  `code` varchar(155) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_branch`
--

INSERT INTO `tbl_branch` (`id`, `code`, `branch`, `schedule_id`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, '0122', 'Cabanatuan Branchs', 1, 1, '2023-10-10 06:21:59', '2023-03-27 15:00:09', 1),
(2, '0123', 'Talavera Branch', 2, 1, '2023-05-02 15:49:57', '2023-04-05 16:37:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_daily_schedule`
--

CREATE TABLE `tbl_daily_schedule` (
  `id` int(50) NOT NULL,
  `emp_id` int(11) NOT NULL COMMENT 'tbl_employee.id',
  `schedule_date` date NOT NULL,
  `schedule_id` int(11) NOT NULL COMMENT 'lib_schedule.id',
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_daily_schedule`
--

INSERT INTO `tbl_daily_schedule` (`id`, `emp_id`, `schedule_date`, `schedule_id`, `date_updated`, `date_created`, `user_id`) VALUES
(6, 1, '2023-05-03', 2, '2023-05-04 14:51:04', '2023-05-04 14:51:04', 2),
(7, 1, '2023-05-10', 2, '2023-05-04 14:54:27', '2023-05-04 14:54:27', 2),
(8, 1, '2023-05-24', 2, '2023-05-04 14:54:34', '2023-05-04 14:54:34', 2),
(9, 1, '2023-05-17', 2, '2023-05-04 14:54:38', '2023-05-04 14:54:38', 2),
(10, 1, '2023-06-19', 2, '2023-05-04 14:55:04', '2023-05-04 14:55:04', 2),
(11, 3, '2023-05-04', 2, '2023-05-04 15:02:51', '2023-05-04 15:02:51', 2),
(12, 3, '2023-06-08', 2, '2023-06-05 13:41:17', '2023-06-05 13:41:17', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_department`
--

CREATE TABLE `tbl_department` (
  `id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL COMMENT 'tbl_division_id',
  `code` varchar(155) NOT NULL,
  `department` varchar(255) NOT NULL,
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_department`
--

INSERT INTO `tbl_department` (`id`, `division_id`, `code`, `department`, `schedule_id`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, 2, '0111', 'Developer Departments', 1, 1, '2023-05-02 15:49:46', '2023-03-27 14:59:40', 1),
(2, 2, '12451', 'Finance Department', 1, 1, '2023-05-02 15:49:48', '2023-04-05 16:19:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_division`
--

CREATE TABLE `tbl_division` (
  `id` int(11) NOT NULL,
  `code` varchar(155) NOT NULL,
  `division` varchar(255) NOT NULL,
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_division`
--

INSERT INTO `tbl_division` (`id`, `code`, `division`, `schedule_id`, `is_active`, `date_updated`, `date_created`, `user_id`) VALUES
(1, '011', 'IT Division', 2, 1, '2023-04-05 15:23:23', '2023-03-27 14:58:45', 1),
(2, '0122', 'Planning Division', 0, 1, '2023-04-05 15:24:47', '2023-04-05 15:24:47', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_employee`
--

CREATE TABLE `tbl_employee` (
  `id` int(11) NOT NULL,
  `bio_id` varchar(25) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `emp_code` varchar(50) NOT NULL,
  `first_name` varchar(155) NOT NULL,
  `middle_name` varchar(155) DEFAULT NULL,
  `last_name` varchar(155) NOT NULL,
  `ext_name` varchar(50) DEFAULT NULL,
  `contact_no` varchar(25) DEFAULT NULL,
  `position_id` varchar(50) DEFAULT NULL COMMENT 'lib_position.id',
  `salary_type` varchar(155) DEFAULT NULL COMMENT 'daily/monthly',
  `salary_rate` double(12,5) DEFAULT NULL,
  `is_mwe` int(11) NOT NULL DEFAULT 1,
  `address` text DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL COMMENT 'tbl_department.id',
  `branch_id` int(11) DEFAULT NULL COMMENT 'tbl_branch.id',
  `designation` int(11) DEFAULT NULL,
  `is_direct` int(11) DEFAULT NULL COMMENT '1-Direct 0-indirect hiring',
  `agency_name` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) NOT NULL DEFAULT 0,
  `sss_number` varchar(100) DEFAULT NULL,
  `philhealth_number` varchar(100) DEFAULT NULL,
  `hdmf_number` varchar(100) DEFAULT NULL,
  `tin_number` varchar(100) NOT NULL,
  `fix_divisor` double(12,2) DEFAULT NULL,
  `fix_sss` double(12,5) DEFAULT NULL,
  `fix_philhealth` double(12,5) DEFAULT NULL,
  `fix_hdmf` double(12,5) DEFAULT NULL,
  `fix_tax_rate` double(12,5) DEFAULT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL,
  `user_id_added` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_employee`
--

INSERT INTO `tbl_employee` (`id`, `bio_id`, `profile_picture`, `emp_code`, `first_name`, `middle_name`, `last_name`, `ext_name`, `contact_no`, `position_id`, `salary_type`, `salary_rate`, `is_mwe`, `address`, `department`, `branch_id`, `designation`, `is_direct`, `agency_name`, `user_id`, `schedule_id`, `sss_number`, `philhealth_number`, `hdmf_number`, `tin_number`, `fix_divisor`, `fix_sss`, `fix_philhealth`, `fix_hdmf`, `fix_tax_rate`, `is_active`, `date_updated`, `date_created`, `user_id_added`) VALUES
(1, '12', 'public/images/20-0933.jpg', '20-0933', 'RONELL JOHN', 'MANALILI', 'BENEDICTO', NULL, '095614214011', '1', 'MONTHLY', 25000.00000, 0, 'asdasda', '1', 1, 1, 1, 'INTRA CODE', 1, 3, '20154541', '124545-454', '0545454545-44', '02-11144241', 0.00, 0.00000, 0.00000, 0.00000, 11.00000, 1, '2024-10-24 16:32:33', '2023-03-27 14:36:03', 1),
(3, '144', 'public/upload_images/emp_pic/download (19).png', '20-142451', 'Irwin', 'B', 'Pamintuan', NULL, '09454127112', '1', 'DAILY', 250.00000, 0, NULL, '1', 2, 2, 1, NULL, 3, 0, '1115424874', '154842148', '211542121', '23423423', NULL, NULL, NULL, NULL, NULL, 1, '2023-06-06 15:50:32', '2023-04-26 13:02:42', 1),
(4, '123', 'public/images/INTRA01.jpg', 'INTRA01', 'Rommel', 'Lopez', 'Lacap', NULL, '09512139839', '1', 'DAILY', 20000.00000, 1, 'San Isidro', '1', 1, 2, 1, NULL, 5, 0, '45345345', '35342534', '45654654', '576547', NULL, 200.00000, 100.00000, 100.00000, 100.00000, 1, '2024-10-29 13:54:42', '2024-10-29 13:32:40', 1),
(7, 'undefined', 'public/images/010101.webp', '010101', 'miguel', 'martin', 'buo', 'jr', '0999999', '1', 'DAILY', 25000.00000, 1, 'sdfsdfsdfds', '1', 1, 1, 1, NULL, 8, 0, '11111', '333333', '22222', '123456789', NULL, NULL, NULL, NULL, NULL, 1, '2024-11-19 13:15:19', '2024-11-19 13:15:18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_file`
--

CREATE TABLE `tbl_file` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL COMMENT 'tbl_employee.id',
  `file_name` varchar(255) NOT NULL,
  `id_type` int(11) NOT NULL COMMENT 'lib_file_type.id',
  `upload_path` text NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_holiday`
--

CREATE TABLE `tbl_holiday` (
  `id` int(50) NOT NULL,
  `holiday_date` date NOT NULL,
  `holiday_name` varchar(155) NOT NULL,
  `holiday_type` varchar(10) NOT NULL COMMENT 'lib_schedule.id',
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_holiday`
--

INSERT INTO `tbl_holiday` (`id`, `holiday_date`, `holiday_name`, `holiday_type`, `date_updated`, `date_created`, `user_id`) VALUES
(2, '2023-05-01', 'Labor Day', 'RH', '2023-05-05 15:13:21', '2023-05-05 15:13:21', 1),
(3, '2023-05-05', 'Special Non Working', 'SH', '2023-05-05 15:13:35', '2023-05-05 15:13:35', 1),
(4, '2023-05-10', 'Sample Holiday', 'RH', '2023-05-05 15:18:21', '2023-05-05 15:18:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_income_file`
--

CREATE TABLE `tbl_income_file` (
  `id` int(11) NOT NULL,
  `selected_emp` varchar(100) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `income_id` int(11) NOT NULL COMMENT 'lib_income.id',
  `amount` double(12,2) NOT NULL,
  `amount_2` double(12,2) NOT NULL,
  `amount_3` double(12,2) NOT NULL,
  `amount_4` double(12,2) NOT NULL,
  `amount_5` double(12,2) NOT NULL,
  `income_type` varchar(155) NOT NULL COMMENT 'DAILY/WEEKLY/MONTHLY',
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_income_file`
--

INSERT INTO `tbl_income_file` (`id`, `selected_emp`, `emp_id`, `income_id`, `amount`, `amount_2`, `amount_3`, `amount_4`, `amount_5`, `income_type`, `date_updated`, `date_created`, `user_id`) VALUES
(12, 'custom_emp', 1, 2, 200.00, 0.00, 0.00, 0.00, 0.00, 'DAILY', '2023-04-14 12:59:26', '2023-04-14 12:59:26', 1),
(13, 'custom_emp', 2, 2, 500.00, 600.00, 0.00, 0.00, 0.00, 'SEMI', '2023-04-14 12:59:26', '2023-04-14 12:59:26', 1),
(14, 'all_emp', 1, 1, 2.00, 3.00, 4.00, 5.00, 6.00, 'WEEKLY', '2023-04-14 13:00:52', '2023-04-14 13:00:52', 1),
(15, 'all_emp', 2, 1, 2.00, 3.00, 4.00, 5.00, 6.00, 'WEEKLY', '2023-04-14 13:00:52', '2023-04-14 13:00:52', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_leave_credits`
--

CREATE TABLE `tbl_leave_credits` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `leave_id` varchar(255) NOT NULL COMMENT 'tbl_leave_type.id',
  `leave_count` int(11) NOT NULL,
  `year_given` year(4) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_leave_credits`
--

INSERT INTO `tbl_leave_credits` (`id`, `emp_id`, `leave_id`, `leave_count`, `year_given`, `date_updated`, `date_created`, `user_id`) VALUES
(1, 1, '1', 10, 2023, '2023-08-07 13:46:39', '2023-05-01 13:21:39', 1),
(2, 2, '1', 10, 2023, '2023-08-07 13:46:39', '2023-05-01 13:21:39', 1),
(3, 3, '2', 8, 2023, '2023-08-07 13:46:55', '2023-05-01 13:21:39', 1),
(4, 3, '1', 10, 2023, '2023-08-07 13:46:39', '2023-05-01 13:33:43', 1),
(5, 1, '2', 8, 0000, '2023-08-07 13:46:55', '2023-08-07 13:46:55', 1),
(6, 2, '2', 8, 0000, '2023-08-07 13:46:55', '2023-08-07 13:46:55', 1),
(7, 7, '1', 15, 2024, '2024-11-24 14:51:34', '2024-11-24 14:51:34', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_leave_types`
--

CREATE TABLE `tbl_leave_types` (
  `id` int(11) NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `leave_name` varchar(100) NOT NULL,
  `is_with_credits` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_leave_types`
--

INSERT INTO `tbl_leave_types` (`id`, `leave_type`, `leave_name`, `is_with_credits`, `date_created`, `date_updated`, `user_id`) VALUES
(1, 'VL', 'Vacation Leave', 1, '2023-05-01 06:51:36', '2023-05-01 07:15:25', 2),
(2, 'SL', 'Sick Leave', 1, '2023-05-01 07:15:35', '2023-05-01 07:15:35', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_leave_used`
--

CREATE TABLE `tbl_leave_used` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `leave_source_id` int(11) NOT NULL COMMENT 'tbl_leave_credits_id',
  `leave_year` year(4) NOT NULL,
  `leave_date_from` date NOT NULL,
  `leave_date_to` date NOT NULL,
  `leave_status` varchar(255) NOT NULL,
  `leave_count` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_leave_used`
--

INSERT INTO `tbl_leave_used` (`id`, `emp_id`, `leave_source_id`, `leave_year`, `leave_date_from`, `leave_date_to`, `leave_status`, `leave_count`, `reason`, `date_updated`, `date_created`, `user_id`) VALUES
(3, 1, 1, 2023, '2023-05-02', '2023-05-02', 'APPROVED', 1, 'sample filling', '2023-05-04 15:29:08', '2023-05-02 13:41:24', 2),
(4, 3, 2, 2023, '2023-06-06', '2023-06-06', 'APPROVED', 1, 'TEST', '2023-06-08 14:35:42', '2023-06-06 13:23:04', 3),
(5, 7, 1, 2024, '2024-12-02', '2024-12-04', 'FILED', 3, 'boracay', '2024-11-27 12:09:40', '2024-11-27 12:09:40', 8);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_loan_file`
--

CREATE TABLE `tbl_loan_file` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL COMMENT 'lib_loans.id',
  `total_amount` double(12,2) NOT NULL,
  `payment_type` varchar(155) NOT NULL COMMENT 'full or partial',
  `variance` varchar(100) NOT NULL,
  `amount_to_pay` double(12,2) NOT NULL,
  `balance` double(12,2) NOT NULL,
  `is_done` int(11) NOT NULL DEFAULT 0,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `notes` varchar(255) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `loan_status` int(11) NOT NULL COMMENT '0->applied; 1->approved; 2->denied; 3->paused'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_loan_file`
--

INSERT INTO `tbl_loan_file` (`id`, `emp_id`, `loan_id`, `total_amount`, `payment_type`, `variance`, `amount_to_pay`, `balance`, `is_done`, `date_from`, `date_to`, `notes`, `date_updated`, `date_created`, `user_id`, `loan_status`) VALUES
(4, 2, 1, 20000.00, 'PARTIAL', 'SEMI', 500.00, 20000.00, 0, '2023-04-01', '2023-04-20', 'asdasd', '2023-04-27 12:55:42', '2023-04-20 09:48:03', 2, 1),
(6, 3, 1, 2000.00, 'PARTIAL', 'SEMI', 100.00, 1500.00, 0, '2023-06-01', '2023-06-30', 'asdasd', '2023-06-13 15:41:31', '2023-06-13 15:38:08', 3, 2),
(7, 7, 1, 15000.00, 'PARTIAL', 'MONTHLY', 1500.00, 15000.00, 0, '2024-12-31', '2025-04-30', 'calamity loan', '2024-11-19 13:49:56', '2024-11-19 13:48:21', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ot_applied`
--

CREATE TABLE `tbl_ot_applied` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `ot_type` varchar(100) NOT NULL,
  `date_target` date NOT NULL,
  `time_from` datetime NOT NULL,
  `time_to` datetime NOT NULL,
  `status` varchar(100) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_approved` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_ot_applied`
--

INSERT INTO `tbl_ot_applied` (`id`, `emp_id`, `ot_type`, `date_target`, `time_from`, `time_to`, `status`, `reason`, `date_created`, `date_updated`, `user_approved`, `user_id`) VALUES
(1, 1, 'ROT', '2023-05-13', '2023-05-13 19:00:00', '2023-05-13 22:00:00', 'APPROVED', 'Sample', '2023-05-13 14:21:50', '2023-05-13 14:47:49', 1, 1),
(2, 1, 'ROT', '2023-05-09', '2023-05-09 19:00:00', '2023-05-09 21:15:00', 'APPROVED', 'This is just a test for the computation', '2023-05-15 11:56:22', '2023-05-15 12:17:06', 1, 1),
(4, 7, 'ROT', '2024-11-28', '2024-11-28 18:30:00', '2024-11-28 20:30:00', 'FILED', 'fix server', '2024-11-28 14:22:07', '2024-11-28 14:22:08', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payroll`
--

CREATE TABLE `tbl_payroll` (
  `id` int(11) NOT NULL,
  `code` varchar(155) NOT NULL,
  `name` varchar(255) NOT NULL,
  `target_month` varchar(100) NOT NULL,
  `target_year` varchar(100) NOT NULL,
  `cover_from` date NOT NULL,
  `cover_to` date NOT NULL,
  `process_type` varchar(10) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'monthly/daily',
  `type_info` varchar(155) NOT NULL,
  `employee` longtext DEFAULT NULL,
  `other_income` varchar(255) DEFAULT NULL,
  `lib_loan` varchar(255) DEFAULT NULL,
  `payroll_status` varchar(155) NOT NULL DEFAULT 'OPEN',
  `gsis` int(11) NOT NULL,
  `sss` int(11) NOT NULL,
  `ph` int(11) NOT NULL,
  `hdmf` int(11) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_payroll`
--

INSERT INTO `tbl_payroll` (`id`, `code`, `name`, `target_month`, `target_year`, `cover_from`, `cover_to`, `process_type`, `type`, `type_info`, `employee`, `other_income`, `lib_loan`, `payroll_status`, `gsis`, `sss`, `ph`, `hdmf`, `date_updated`, `date_created`, `user_id`) VALUES
(1, 'aprm-01', 'April 1st Half', 'APR', '2023', '2023-04-01', '2023-04-15', 'RP', 'SEMI', '1', '|1|;|2|', '2;1', '2;1', 'COMPUTED', 0, 1, 1, 1, '2023-05-09 15:45:28', '2023-04-21 00:00:00', 2),
(2, 'aprm-02', 'April Payroll 2nd Hald', 'APR', '2023', '2023-04-16', '2023-04-30', 'RP', 'SEMI', '2', '|2|;|3|', '2', '3;1', 'COMPUTED', 0, 1, 1, 1, '2023-09-05 15:08:19', '2023-04-24 00:00:00', 2),
(3, '13th-2023', '13th Month Pay 2023', 'DEC', '2023', '2022-12-01', '2023-11-30', '13', 'MONTHLY', '1', '|1|;|2|;|3|', '', '', 'COMPUTED', 0, 1, 0, 1, '2024-11-24 14:36:26', '2023-08-08 00:00:00', 1),
(4, 'rp_m1', 'May 1-15, 2023', 'MAY', '2023', '2023-05-01', '2023-05-15', 'RP', 'SEMI', '1', '|1|;|2|;|3|', '1', '1', 'COMPUTED', 0, 1, 0, 1, '2023-08-08 14:34:53', '2023-08-08 00:00:00', 1),
(7, 'cc', 'cc', 'AUG', '2023', '2023-08-01', '2023-08-08', 'RP', 'SEMI', '1', '|1|;|2|;|3|', '', '', 'CLOSE', 0, 0, 1, 0, '2023-08-08 14:52:07', '2023-08-08 00:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payroll_deduction`
--

CREATE TABLE `tbl_payroll_deduction` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'SSS;HDMF;PH;TAX;LATE;ABSENT;id of emp loan',
  `amount` double(12,2) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_payroll_deduction`
--

INSERT INTO `tbl_payroll_deduction` (`id`, `payroll_id`, `emp_id`, `type`, `amount`, `date_updated`, `date_created`, `user_id`) VALUES
(28, 1, 1, 'HDMF', 250.00, '2023-04-27 16:21:47', '2023-04-27', 2),
(36, 1, 1, 'HDMF', 250.00, '2023-04-28 23:15:41', '2023-04-28', 2),
(41, 1, 1, 'HDMF', 250.00, '2023-04-29 09:02:32', '2023-04-29', 2),
(46, 1, 1, 'HDMF', 250.00, '2023-04-29 09:31:42', '2023-04-29', 2),
(57, 1, 1, 'HDMF', 250.00, '2023-05-09 15:24:46', '2023-05-09', 1),
(62, 1, 1, 'HDMF', 250.00, '2023-05-09 15:24:55', '2023-05-09', 1),
(67, 1, 1, 'HDMF', 250.00, '2023-05-09 15:24:59', '2023-05-09', 1),
(70, 1, 1, 'SSS', 572.50, '2023-05-09 15:45:28', '2023-05-09', 1),
(71, 1, 1, 'PH', 281.25, '2023-05-09 15:45:28', '2023-05-09', 1),
(72, 1, 1, 'HDMF', 250.00, '2023-05-09 15:45:28', '2023-05-09', 1),
(73, 1, 1, 'TAX', 312.45, '2023-05-09 15:45:28', '2023-05-09', 1),
(74, 1, 2, 'R_1', 500.00, '2023-05-09 15:45:28', '2023-05-09', 1),
(161, 4, 1, 'LATE', 60.63, '2023-05-22 14:01:20', '2023-05-22', 1),
(162, 4, 1, 'ABSENT', 5769.23, '2023-05-22 14:01:20', '2023-05-22', 1),
(163, 4, 1, 'SSS', 572.50, '2023-05-22 14:01:20', '2023-05-22', 1),
(164, 4, 1, 'PH', 281.25, '2023-05-22 14:01:20', '2023-05-22', 1),
(165, 4, 1, 'HDMF', 250.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(166, 4, 1, 'TAX', 312.45, '2023-05-22 14:01:20', '2023-05-22', 1),
(173, 5, 1, 'ABSENT', 7692.31, '2023-08-08 14:44:26', '2023-08-08', 1),
(174, 5, 1, 'HDMF', 250.00, '2023-08-08 14:44:26', '2023-08-08', 1),
(175, 5, 1, 'TAX', 312.45, '2023-08-08 14:44:26', '2023-08-08', 1),
(176, 7, 1, 'ABSENT', 7692.31, '2023-08-08 14:48:39', '2023-08-08', 1),
(177, 7, 1, 'PH', 281.25, '2023-08-08 14:48:39', '2023-08-08', 1),
(178, 7, 1, 'TAX', 312.45, '2023-08-08 14:48:39', '2023-08-08', 1),
(184, 2, 2, 'R_1', 1000.00, '2023-09-05 15:08:20', '2023-09-05', 1),
(185, 2, 3, 'LATE', 1.04, '2023-09-05 15:08:20', '2023-09-05', 1),
(186, 2, 3, 'SSS', 190.00, '2023-09-05 15:08:20', '2023-09-05', 1),
(187, 2, 3, 'HDMF', 35.00, '2023-09-05 15:08:20', '2023-09-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payroll_income`
--

CREATE TABLE `tbl_payroll_income` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'BP->basic pay;id of Other income',
  `amount` double(12,2) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_payroll_income`
--

INSERT INTO `tbl_payroll_income` (`id`, `payroll_id`, `emp_id`, `type`, `amount`, `date_updated`, `date_created`, `user_id`) VALUES
(92, 1, 1, 'BP', 12500.00, '2023-05-09 15:45:28', '2023-05-09', 1),
(93, 1, 2, 'BP', 0.00, '2023-05-09 15:45:28', '2023-05-09', 1),
(94, 1, 2, 'R_2', 500.00, '2023-05-09 15:45:28', '2023-05-09', 1),
(95, 3, 1, '13TH', 0.00, '2023-05-09 15:46:43', '2023-05-09', 1),
(96, 3, 2, '13TH', 0.00, '2023-05-09 15:46:43', '2023-05-09', 1),
(97, 3, 3, '13TH', 0.00, '2023-05-09 15:46:43', '2023-05-09', 1),
(190, 4, 1, '3', 1000.00, '2023-05-19 16:29:34', '2023-05-19', 1),
(218, 4, 1, 'BP', 12500.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(219, 4, 1, 'ROT', 72.12, '2023-05-22 14:01:20', '2023-05-22', 1),
(220, 4, 1, 'RH', 1923.08, '2023-05-22 14:01:20', '2023-05-22', 1),
(221, 4, 2, 'BP', 0.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(222, 4, 2, 'RH', 1000.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(223, 4, 2, 'SH', 500.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(224, 4, 3, 'BP', 0.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(225, 4, 3, 'RH', 500.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(226, 4, 3, 'SH', 250.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(233, 5, 1, 'BP', 12500.00, '2023-08-08 14:44:26', '2023-08-08', 1),
(234, 5, 2, 'BP', 0.00, '2023-08-08 14:44:26', '2023-08-08', 1),
(236, 7, 1, 'BP', 12500.00, '2023-08-08 14:48:39', '2023-08-08', 1),
(237, 7, 2, 'BP', 0.00, '2023-08-08 14:48:39', '2023-08-08', 1),
(238, 7, 3, 'BP', 0.00, '2023-08-08 14:48:39', '2023-08-08', 1),
(243, 2, 2, 'BP', 0.00, '2023-09-05 15:08:20', '2023-09-05', 1),
(244, 2, 2, 'R_2', 600.00, '2023-09-05 15:08:20', '2023-09-05', 1),
(245, 2, 3, 'BP', 1750.00, '2023-09-05 15:08:20', '2023-09-05', 1),
(246, 2, 3, 'ROT', 18.75, '2023-09-05 15:08:20', '2023-09-05', 1),
(247, 2, 3, 'SOT', 15.62, '2023-09-05 15:08:20', '2023-09-05', 1),
(248, 2, 3, 'ND', 15.62, '2023-09-05 15:08:20', '2023-09-05', 1),
(249, 2, 3, 'RH', 62.50, '2023-09-05 15:08:20', '2023-09-05', 1),
(250, 2, 3, 'SH', 62.50, '2023-09-05 15:08:20', '2023-09-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_raw_logs`
--

CREATE TABLE `tbl_raw_logs` (
  `biometric_id` varchar(50) NOT NULL COMMENT 'tbl_employee.bio_id',
  `state` varchar(50) NOT NULL COMMENT 'AM_IN; AM_OUT; PM_IN; PM_OUT; OT_IN; OT_OUT;',
  `logs` datetime NOT NULL COMMENT 'DATE TIME REQUIRED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_raw_logs`
--

INSERT INTO `tbl_raw_logs` (`biometric_id`, `state`, `logs`) VALUES
('12', 'AM_IN', '2024-10-24 16:20:13'),
('12', 'AM_OUT', '2024-10-24 16:20:52'),
('12', 'AM_IN', '2024-10-24 16:25:34'),
('12', 'AM_IN', '2024-10-26 05:27:30'),
('12', 'AM_IN', '2024-10-26 05:50:18'),
('12', 'AM_IN', '2024-10-26 06:41:43'),
('44', 'AM_IN', '2024-10-26 06:44:04'),
('12', 'PM_OUT', '2024-10-26 06:46:07'),
('123', 'AM_IN', '2024-10-29 13:56:17'),
('123', 'AM_IN', '2024-10-30 15:59:18');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_role_access`
--

CREATE TABLE `tbl_role_access` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL COMMENT 'admin;employee',
  `permission` varchar(255) NOT NULL COMMENT 'semicolon delimited\r\n| 1->crud,2->u,3->r ',
  `is_active` int(11) NOT NULL DEFAULT 1,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_role_access`
--

INSERT INTO `tbl_role_access` (`id`, `name`, `type`, `permission`, `is_active`, `date_updated`, `date_created`) VALUES
(1, 'Timekeeper', 'hr', '1|1;2|1;3|1;4|1;5|1;6|1;7|1;8|1;9|1;10|1;11|1;12|1;13|1;14|1', 1, '2024-10-29 14:02:44', NULL),
(2, 'Staff', 'employee', '1|3;2|0;3|3;4|0;5|0;6|1;7|1;8|1;9|0;10|0;11|1;12|2;13|0;14|0', 1, '2023-06-06 14:59:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_site_config`
--

CREATE TABLE `tbl_site_config` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `logo_main` varchar(255) NOT NULL,
  `logo_sub` varchar(255) NOT NULL,
  `url` varchar(155) NOT NULL,
  `default_work_settings` int(11) NOT NULL COMMENT 'lib_week_schedule',
  `is_government` int(11) NOT NULL DEFAULT 0,
  `gsis_contribution` double(12,2) NOT NULL,
  `gsis_company` double NOT NULL,
  `address` varchar(255) NOT NULL,
  `divisor` double(12,4) NOT NULL,
  `required_lunch_in_out` int(11) NOT NULL DEFAULT 0,
  `daily_divisor` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_site_config`
--

INSERT INTO `tbl_site_config` (`id`, `company_name`, `logo_main`, `logo_sub`, `url`, `default_work_settings`, `is_government`, `gsis_contribution`, `gsis_company`, `address`, `divisor`, `required_lunch_in_out`, `daily_divisor`, `version`, `date_updated`) VALUES
(1, 'Intra Business Solutions', 'public/upload_images/logo/main_logo.jpg', 'public/upload_images/logo/1679493209sub.png', 'https://demo.payroll.com', 1, 0, 0.09, 0.12, 'Cabanatuan City', 26.0000, 1, 8, 1, '2024-10-24 14:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_statutory_company`
--

CREATE TABLE `tbl_statutory_company` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'GSIS;SSS;PH;HDMF',
  `amount` double(12,2) NOT NULL,
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_created` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_statutory_company`
--

INSERT INTO `tbl_statutory_company` (`id`, `payroll_id`, `emp_id`, `type`, `amount`, `date_updated`, `date_created`, `user_id`) VALUES
(32, 1, 1, 'SSS', 1187.50, '2023-05-09 15:45:28', '2023-05-09', 1),
(33, 1, 1, 'PH', 281.25, '2023-05-09 15:45:28', '2023-05-09', 1),
(34, 1, 1, 'HDMF', 250.00, '2023-05-09 15:45:28', '2023-05-09', 1),
(80, 4, 1, 'SSS', 1187.50, '2023-05-22 14:01:20', '2023-05-22', 1),
(81, 4, 1, 'PH', 281.25, '2023-05-22 14:01:20', '2023-05-22', 1),
(82, 4, 1, 'HDMF', 250.00, '2023-05-22 14:01:20', '2023-05-22', 1),
(85, 5, 1, 'HDMF', 250.00, '2023-08-08 14:44:26', '2023-08-08', 1),
(86, 7, 1, 'PH', 281.25, '2023-08-08 14:48:39', '2023-08-08', 1),
(90, 2, 3, 'SSS', 380.00, '2023-09-05 15:08:20', '2023-09-05', 1),
(91, 2, 3, 'HDMF', 35.00, '2023-09-05 15:08:20', '2023-09-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_timecard`
--

CREATE TABLE `tbl_timecard` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `target_date` date NOT NULL,
  `flexi_hours` double(12,2) NOT NULL DEFAULT 0.00,
  `AM_IN` datetime DEFAULT NULL,
  `AM_OUT` datetime DEFAULT NULL,
  `PM_IN` datetime DEFAULT NULL,
  `PM_OUT` datetime DEFAULT NULL,
  `OT_IN` datetime DEFAULT NULL,
  `OT_OUT` datetime DEFAULT NULL,
  `processed_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='this is the table for processed logs from tbl_raw_logs';

--
-- Dumping data for table `tbl_timecard`
--

INSERT INTO `tbl_timecard` (`id`, `emp_id`, `target_date`, `flexi_hours`, `AM_IN`, `AM_OUT`, `PM_IN`, `PM_OUT`, `OT_IN`, `OT_OUT`, `processed_date`, `user_id`) VALUES
(1, 1, '2023-05-09', 0.00, '2023-05-09 07:48:30', '2023-05-09 12:00:16', '2023-05-09 13:00:16', '2023-05-09 22:49:41', '2023-05-09 19:00:00', '2023-05-09 21:00:00', '2023-05-17 13:15:57', 1),
(2, 1, '2023-05-17', 0.00, '2023-05-17 12:52:49', '2023-05-17 12:53:40', '2023-05-17 12:54:08', '2023-05-17 12:54:34', NULL, NULL, '2023-05-17 13:15:57', 1),
(3, 1, '2023-05-16', 0.00, NULL, NULL, NULL, NULL, '2023-05-16 13:16:19', '2023-05-16 13:16:19', '2023-05-17 13:19:25', 1),
(4, 1, '2023-08-07', 0.00, '2023-08-07 15:13:53', '2023-08-07 23:16:12', '2023-08-07 23:48:10', '2023-08-07 23:48:13', '2023-08-07 23:48:14', '2023-08-07 23:48:16', '2023-08-24 15:32:51', 1),
(5, 1, '2023-08-08', 0.00, '2023-08-08 00:05:17', '2023-08-08 00:05:19', '2023-08-08 00:05:20', '2023-08-08 00:05:22', '2023-08-08 00:05:24', '2023-08-08 00:05:26', '2023-08-24 15:32:51', 1),
(6, 3, '2023-08-08', 0.00, '2023-08-08 00:05:40', '2023-08-08 00:10:10', '2023-08-08 00:10:45', NULL, NULL, NULL, '2023-08-24 15:32:51', 1),
(18, 1, '2023-08-23', 0.06, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-24 15:55:58', 1),
(19, 1, '2023-08-24', 0.80, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-24 15:55:58', 1),
(20, 1, '2024-10-24', 0.00, '2024-10-24 16:20:13', '2024-10-24 16:20:52', NULL, NULL, NULL, NULL, '2024-10-26 06:44:33', 1),
(21, 1, '2024-10-26', 0.00, '2024-10-26 05:27:30', NULL, NULL, '2024-10-26 06:46:07', NULL, NULL, '2024-10-26 06:46:24', 1),
(22, 2, '2024-10-26', 0.00, '2024-10-26 06:44:04', NULL, NULL, NULL, NULL, NULL, '2024-10-26 06:44:33', 1),
(23, 7, '2024-11-22', 0.00, '2024-11-22 09:23:09', '2024-11-22 12:01:09', '2024-11-22 12:50:09', '2024-11-22 18:33:09', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2024-11-24 11:26:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_timekeeping`
--

CREATE TABLE `tbl_timekeeping` (
  `id` int(255) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `emp_code` varchar(200) NOT NULL,
  `date_target` date NOT NULL,
  `regular_work` double NOT NULL,
  `lates` double NOT NULL COMMENT 'in minutes',
  `regular_ot` double NOT NULL COMMENT 'hours',
  `special_ot` double(12,2) NOT NULL COMMENT 'hours',
  `night_diff` double NOT NULL COMMENT 'hours',
  `regular_leave` double NOT NULL,
  `sick_leave` double NOT NULL,
  `special_leave` double NOT NULL,
  `regular_holiday` double NOT NULL COMMENT 'hours',
  `special_holiday` double NOT NULL COMMENT 'hours',
  `is_manual` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_timekeeping`
--

INSERT INTO `tbl_timekeeping` (`id`, `emp_id`, `emp_code`, `date_target`, `regular_work`, `lates`, `regular_ot`, `special_ot`, `night_diff`, `regular_leave`, `sick_leave`, `special_leave`, `regular_holiday`, `special_holiday`, `is_manual`) VALUES
(646, 1, '20-09-0933', '2023-05-01', 0, 0, 0, 0.00, 0, 0, 0, 0, 8, 0, 0),
(647, 1, '20-09-0933', '2023-05-02', 0, 0, 0, 0.00, 0, 8, 0, 0, 0, 0, 0),
(648, 1, '20-09-0933', '2023-05-03', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(649, 1, '20-09-0933', '2023-05-04', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(650, 1, '20-09-0933', '2023-05-05', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(651, 1, '20-09-0933', '2023-05-06', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(652, 1, '20-09-0933', '2023-05-07', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(653, 1, '20-09-0933', '2023-05-08', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(654, 1, '20-09-0933', '2023-05-09', 7.8538888888889, 30.266666666667, 2, 0.00, 0, 0, 0, 0, 0, 0, 0),
(655, 1, '20-09-0933', '2023-05-10', 0, 0, 0, 0.00, 0, 0, 0, 0, 8, 0, 0),
(656, 1, '20-09-0933', '2023-05-11', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(657, 1, '20-09-0933', '2023-05-12', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(658, 1, '20-09-0933', '2023-05-13', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(659, 1, '20-09-0933', '2023-05-14', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(660, 1, '20-09-0933', '2023-05-15', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(661, 2, '20-124514', '2023-05-01', 0, 0, 0, 0.00, 0, 0, 0, 0, 8, 0, 0),
(662, 2, '20-124514', '2023-05-02', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(663, 2, '20-124514', '2023-05-03', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(664, 2, '20-124514', '2023-05-04', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(665, 2, '20-124514', '2023-05-05', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 8, 0),
(666, 2, '20-124514', '2023-05-06', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(667, 2, '20-124514', '2023-05-07', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(668, 2, '20-124514', '2023-05-08', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(669, 2, '20-124514', '2023-05-09', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(670, 2, '20-124514', '2023-05-10', 0, 0, 0, 0.00, 0, 0, 0, 0, 8, 0, 0),
(671, 2, '20-124514', '2023-05-11', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(672, 2, '20-124514', '2023-05-12', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(673, 2, '20-124514', '2023-05-13', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(674, 2, '20-124514', '2023-05-14', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(675, 2, '20-124514', '2023-05-15', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(676, 3, '20-142451', '2023-05-01', 0, 0, 0, 0.00, 0, 0, 0, 0, 8, 0, 0),
(677, 3, '20-142451', '2023-05-02', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(678, 3, '20-142451', '2023-05-03', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(679, 3, '20-142451', '2023-05-04', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(680, 3, '20-142451', '2023-05-05', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 8, 0),
(681, 3, '20-142451', '2023-05-06', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(682, 3, '20-142451', '2023-05-07', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(683, 3, '20-142451', '2023-05-08', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(684, 3, '20-142451', '2023-05-09', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(685, 3, '20-142451', '2023-05-10', 0, 0, 0, 0.00, 0, 0, 0, 0, 8, 0, 0),
(686, 3, '20-142451', '2023-05-11', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(687, 3, '20-142451', '2023-05-12', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(688, 3, '20-142451', '2023-05-13', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(689, 3, '20-142451', '2023-05-14', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(690, 3, '20-142451', '2023-05-15', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(715, 1, '20-09-0933', '2023-08-01', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(716, 1, '20-09-0933', '2023-08-02', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(717, 1, '20-09-0933', '2023-08-03', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(718, 1, '20-09-0933', '2023-08-04', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(719, 1, '20-09-0933', '2023-08-05', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(720, 1, '20-09-0933', '2023-08-06', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(721, 1, '20-09-0933', '2023-08-07', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(722, 1, '20-09-0933', '2023-08-08', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(723, 2, '20-124514', '2023-08-01', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(724, 2, '20-124514', '2023-08-02', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(725, 2, '20-124514', '2023-08-03', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(726, 2, '20-124514', '2023-08-04', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(727, 2, '20-124514', '2023-08-05', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(728, 2, '20-124514', '2023-08-06', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(729, 2, '20-124514', '2023-08-07', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(730, 2, '20-124514', '2023-08-08', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(731, 3, '20-142451', '2023-08-01', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(732, 3, '20-142451', '2023-08-02', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(733, 3, '20-142451', '2023-08-03', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(734, 3, '20-142451', '2023-08-04', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(735, 3, '20-142451', '2023-08-05', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(736, 3, '20-142451', '2023-08-06', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(737, 3, '20-142451', '2023-08-07', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(738, 3, '20-142451', '2023-08-08', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(784, 1, '20-09-0933', '2023-04-16', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(785, 1, '20-09-0933', '2023-04-17', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(786, 1, '20-09-0933', '2023-04-18', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(787, 1, '20-09-0933', '2023-04-19', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(788, 1, '20-09-0933', '2023-04-20', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(789, 1, '20-09-0933', '2023-04-21', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(790, 1, '20-09-0933', '2023-04-22', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(791, 1, '20-09-0933', '2023-04-23', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(792, 1, '20-09-0933', '2023-04-24', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(793, 1, '20-09-0933', '2023-04-25', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(794, 1, '20-09-0933', '2023-04-26', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(795, 1, '20-09-0933', '2023-04-27', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(796, 1, '20-09-0933', '2023-04-28', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(797, 1, '20-09-0933', '2023-04-29', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(798, 1, '20-09-0933', '2023-04-30', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(830, 3, '20-142451', '2023-04-16', 2, 2, 2, 2.00, 2, 2, 2, 2, 2, 2, 1),
(831, 2, '20-124514', '2023-04-16', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(832, 2, '20-124514', '2023-04-17', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(833, 2, '20-124514', '2023-04-18', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(834, 2, '20-124514', '2023-04-19', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(835, 2, '20-124514', '2023-04-20', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(836, 2, '20-124514', '2023-04-21', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(837, 2, '20-124514', '2023-04-22', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(838, 2, '20-124514', '2023-04-23', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(839, 2, '20-124514', '2023-04-24', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(840, 2, '20-124514', '2023-04-25', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(841, 2, '20-124514', '2023-04-26', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(842, 2, '20-124514', '2023-04-27', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(843, 2, '20-124514', '2023-04-28', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(844, 2, '20-124514', '2023-04-29', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(845, 2, '20-124514', '2023-04-30', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(846, 3, '20-142451', '2023-04-17', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(847, 3, '20-142451', '2023-04-18', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(848, 3, '20-142451', '2023-04-19', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(849, 3, '20-142451', '2023-04-20', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(850, 3, '20-142451', '2023-04-21', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(851, 3, '20-142451', '2023-04-22', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(852, 3, '20-142451', '2023-04-23', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(853, 3, '20-142451', '2023-04-24', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(854, 3, '20-142451', '2023-04-25', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(855, 3, '20-142451', '2023-04-26', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(856, 3, '20-142451', '2023-04-27', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(857, 3, '20-142451', '2023-04-28', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(858, 3, '20-142451', '2023-04-29', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0),
(859, 3, '20-142451', '2023-04-30', 0, 0, 0, 0.00, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middleName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int(11) NOT NULL COMMENT 'tbl_role_id',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `firstName`, `middleName`, `lastName`, `extName`, `position`, `role_id`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'RONELL JOHN BENEDICTO', 'RONELL JOHN', 'MANALILI', 'BENEDICTO', NULL, 'Programmers', 1, 'ahrjhace@gmail.com', NULL, '$2y$10$roQOSnH23TIwX.UbGELM0OzoeMeUxQm5a1GuAO1hURr6OyabWLhum', NULL, '2023-03-17 06:02:36', '2023-03-17 06:02:36'),
(2, 'Joe Najera', 'Joe', 'Ma', 'Najera', 'Corazon', 'Programmers', 1, 'rjbenedicto.dev@gmail.com', NULL, '$2y$10$917IP3KoHQvMeK4tLswSiOaL5vMWAd0k9aX6ySKLF2gF9L8NuTP/K', NULL, NULL, NULL),
(3, 'Irwin Pamintuan', 'Irwin', 'B', 'Pamintuan', NULL, 'Programmers', 2, 'joe@gmail.com', NULL, '$2y$10$s091mOZmMBKZgJthYz//ieoSQe634edzd.fD.I3mhVWQ8VRJifRfi', NULL, NULL, NULL),
(5, 'Rommel Lacap', 'Rommel', 'Lopez', 'Lacap', NULL, 'Programmers', 2, 'lacaprommel11@gmail.com', NULL, '$2y$10$D6CSBddVu07DpPw1ZQfRO.0lZCJApoo7kp4Cj1hIFls6VbHJziE3G', NULL, NULL, NULL),
(8, 'miguel buo', 'miguel', 'martin', 'buo', 'jr', 'Programmers', 2, 'miguelbuojr@gmail.com', NULL, '$2y$10$6H8KDtIS/Lxx1h8jp7/xfORF/7S4/FN6TKmoJFMzfmVFrrvEMJSJG', NULL, NULL, '2024-11-25 05:58:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `lib_bir_non_taxable`
--
ALTER TABLE `lib_bir_non_taxable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_bir_taxable`
--
ALTER TABLE `lib_bir_taxable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_designation`
--
ALTER TABLE `lib_designation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_file_type`
--
ALTER TABLE `lib_file_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_hdmf`
--
ALTER TABLE `lib_hdmf`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_income`
--
ALTER TABLE `lib_income`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_loans`
--
ALTER TABLE `lib_loans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_loan_type`
--
ALTER TABLE `lib_loan_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_ot_table`
--
ALTER TABLE `lib_ot_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_permission`
--
ALTER TABLE `lib_permission`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_philhealth`
--
ALTER TABLE `lib_philhealth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_position`
--
ALTER TABLE `lib_position`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_schedule`
--
ALTER TABLE `lib_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_sss`
--
ALTER TABLE `lib_sss`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_tax_table`
--
ALTER TABLE `lib_tax_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lib_week_schedule`
--
ALTER TABLE `lib_week_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `tbl_branch`
--
ALTER TABLE `tbl_branch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_daily_schedule`
--
ALTER TABLE `tbl_daily_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_department`
--
ALTER TABLE `tbl_department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_division`
--
ALTER TABLE `tbl_division`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_file`
--
ALTER TABLE `tbl_file`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_holiday`
--
ALTER TABLE `tbl_holiday`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_income_file`
--
ALTER TABLE `tbl_income_file`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_leave_credits`
--
ALTER TABLE `tbl_leave_credits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_leave_types`
--
ALTER TABLE `tbl_leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_leave_used`
--
ALTER TABLE `tbl_leave_used`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_loan_file`
--
ALTER TABLE `tbl_loan_file`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_ot_applied`
--
ALTER TABLE `tbl_ot_applied`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_payroll`
--
ALTER TABLE `tbl_payroll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_payroll_deduction`
--
ALTER TABLE `tbl_payroll_deduction`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_payroll_income`
--
ALTER TABLE `tbl_payroll_income`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_role_access`
--
ALTER TABLE `tbl_role_access`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_site_config`
--
ALTER TABLE `tbl_site_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_statutory_company`
--
ALTER TABLE `tbl_statutory_company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_timecard`
--
ALTER TABLE `tbl_timecard`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_timekeeping`
--
ALTER TABLE `tbl_timekeeping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lib_bir_non_taxable`
--
ALTER TABLE `lib_bir_non_taxable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lib_bir_taxable`
--
ALTER TABLE `lib_bir_taxable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lib_designation`
--
ALTER TABLE `lib_designation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lib_file_type`
--
ALTER TABLE `lib_file_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `lib_hdmf`
--
ALTER TABLE `lib_hdmf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lib_income`
--
ALTER TABLE `lib_income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lib_loans`
--
ALTER TABLE `lib_loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lib_loan_type`
--
ALTER TABLE `lib_loan_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lib_ot_table`
--
ALTER TABLE `lib_ot_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lib_permission`
--
ALTER TABLE `lib_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `lib_philhealth`
--
ALTER TABLE `lib_philhealth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lib_position`
--
ALTER TABLE `lib_position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lib_schedule`
--
ALTER TABLE `lib_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lib_tax_table`
--
ALTER TABLE `lib_tax_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `lib_week_schedule`
--
ALTER TABLE `lib_week_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_branch`
--
ALTER TABLE `tbl_branch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_daily_schedule`
--
ALTER TABLE `tbl_daily_schedule`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_department`
--
ALTER TABLE `tbl_department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_division`
--
ALTER TABLE `tbl_division`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_file`
--
ALTER TABLE `tbl_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_holiday`
--
ALTER TABLE `tbl_holiday`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_income_file`
--
ALTER TABLE `tbl_income_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_leave_credits`
--
ALTER TABLE `tbl_leave_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_leave_types`
--
ALTER TABLE `tbl_leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_leave_used`
--
ALTER TABLE `tbl_leave_used`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_loan_file`
--
ALTER TABLE `tbl_loan_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_ot_applied`
--
ALTER TABLE `tbl_ot_applied`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_payroll`
--
ALTER TABLE `tbl_payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_payroll_deduction`
--
ALTER TABLE `tbl_payroll_deduction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `tbl_payroll_income`
--
ALTER TABLE `tbl_payroll_income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT for table `tbl_role_access`
--
ALTER TABLE `tbl_role_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_statutory_company`
--
ALTER TABLE `tbl_statutory_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `tbl_timecard`
--
ALTER TABLE `tbl_timecard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tbl_timekeeping`
--
ALTER TABLE `tbl_timekeeping`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=860;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
