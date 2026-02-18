-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 02:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teacherdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','late','excused','absent') NOT NULL,
  `remarks` text DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `date`, `status`, `remarks`, `grade_level`, `section`, `created_at`) VALUES
(1, 1, '2026-02-15', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-15 03:25:08'),
(4, 2, '2026-02-15', 'absent', '', 'GRADE 10', 'RIZAL', '2026-02-15 03:30:57'),
(6, 53, '2026-02-16', 'absent', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(7, 63, '2026-02-16', 'absent', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(8, 28, '2026-02-16', 'absent', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(9, 48, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(10, 2, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(11, 73, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(12, 43, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(13, 58, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(14, 3, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(15, 23, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(16, 18, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(17, 1, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(18, 33, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(19, 8, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(20, 38, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(21, 68, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(22, 13, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 06:21:41'),
(26, 78, '2026-02-16', 'present', '', 'GRADE 10', 'RIZAL', '2026-02-16 13:25:56'),
(41, 42, '2026-02-18', 'absent', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(42, 12, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(43, 52, '2026-02-18', 'late', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(44, 32, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(45, 62, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(46, 27, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(47, 22, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(48, 47, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(49, 57, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(50, 77, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:27'),
(51, 67, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:28'),
(52, 17, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:28'),
(53, 7, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:28'),
(54, 37, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:28'),
(55, 72, '2026-02-18', 'present', '', 'GRADE 7', 'PASCAL', '2026-02-18 00:44:28'),
(56, 26, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(57, 56, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(58, 46, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(59, 31, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(60, 79, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(61, 36, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(62, 21, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(63, 41, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(64, 71, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(65, 6, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(66, 61, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(67, 76, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(68, 51, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(69, 16, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(70, 66, '2026-02-18', 'present', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52'),
(71, 11, '2026-02-18', 'absent', '', 'GRADE 8', 'MARS', '2026-02-18 00:56:52');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `grade_level`, `section`, `subject`, `subject_code`, `created_at`) VALUES
(1, 'GRADE 10', 'RIZAL', 'FILIPINO', 'FIL-005', '2026-02-15 03:19:29'),
(2, 'GRADE 9', 'HONESTY', 'SCIENCE', 'SCI-101', '2026-02-15 03:42:50'),
(3, 'GRADE 8', 'SATURN', 'MATHEMATICS', 'MATH-102', '2026-02-15 03:44:01'),
(4, 'GRADE 8', 'MARS', 'MATHEMATICS', 'MATH-102', '2026-02-15 03:44:38'),
(5, 'GRADE 7', 'PASCAL', 'ENGLISH', 'ENG-103', '2026-02-15 03:45:16');

-- --------------------------------------------------------

--
-- Table structure for table `class_activities`
--

CREATE TABLE `class_activities` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `activity_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_activities`
--

INSERT INTO `class_activities` (`id`, `class_id`, `activity_date`, `activity_text`, `created_at`) VALUES
(1, 1, '2026-02-16', 'FUNRUN', '2026-02-15 03:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `class_students`
--

CREATE TABLE `class_students` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_students`
--

INSERT INTO `class_students` (`id`, `class_id`, `first_name`, `last_name`, `middle_name`, `gender`, `student_id`, `grade_level`, `section`, `created_at`) VALUES
(1, 1, 'VICTOR', 'ODARVE', 'E', 'Male', '02-2324-13335', 'GRADE 10', 'RIZAL', '2026-02-15 03:21:25'),
(2, 1, 'GABRIEL', 'MADRIDANO', 'M', 'Male', '02-2324-11111', 'GRADE 10', 'RIZAL', '2026-02-15 03:30:47'),
(3, 1, 'Mason', 'Morales', NULL, 'Male', 'FIL-005-01-01', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(4, 2, 'Olivia', 'Fernandez', NULL, 'Male', 'SCI-101-02-01', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(5, 3, 'Amelia', 'Cruz', NULL, 'Male', 'MATH-102-03-01', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(6, 4, 'James', 'Mendoza', NULL, 'Male', 'MATH-102-04-01', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(7, 5, 'Noah', 'Ramos', NULL, 'Male', 'ENG-103-05-01', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(8, 1, 'Sophia', 'Ramos', NULL, 'Female', 'FIL-005-01-02', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(9, 2, 'Lucas', 'Garcia', NULL, 'Female', 'SCI-101-02-02', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(10, 3, 'Elijah', 'Rivera', NULL, 'Female', 'MATH-102-03-02', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(11, 4, 'Benjamin', 'Torres', NULL, 'Female', 'MATH-102-04-02', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(12, 5, 'Elijah', 'Bautista', NULL, 'Female', 'ENG-103-05-02', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(13, 1, 'Isabella', 'Villanueva', NULL, 'Male', 'FIL-005-01-03', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(14, 2, 'Sophia', 'Flores', NULL, 'Male', 'SCI-101-02-03', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(15, 3, 'Noah', 'Santos', NULL, 'Male', 'MATH-102-03-03', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(16, 4, 'Isabella', 'Salazar', NULL, 'Male', 'MATH-102-04-03', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(17, 5, 'Charlotte', 'Mendoza', NULL, 'Male', 'ENG-103-05-03', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(18, 1, 'Ethan', 'Navarro', NULL, 'Female', 'FIL-005-01-04', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(19, 2, 'Emma', 'Villanueva', NULL, 'Female', 'SCI-101-02-04', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(20, 3, 'Henry', 'Garcia', NULL, 'Female', 'MATH-102-03-04', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(21, 4, 'Ava', 'Flores', NULL, 'Female', 'MATH-102-04-04', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(22, 5, 'Isabella', 'Cruz', NULL, 'Female', 'ENG-103-05-04', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(23, 1, 'Ava', 'Navarro', NULL, 'Male', 'FIL-005-01-05', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(24, 2, 'James', 'Diaz', NULL, 'Male', 'SCI-101-02-05', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(25, 3, 'Ava', 'Aquino', NULL, 'Male', 'MATH-102-03-05', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(26, 4, 'Lucas', 'Bautista', NULL, 'Male', 'MATH-102-04-05', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(27, 5, 'Sophia', 'Castro', NULL, 'Male', 'ENG-103-05-05', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(28, 1, 'Ava', 'Diaz', NULL, 'Female', 'FIL-005-01-06', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(29, 2, 'Daniel', 'Castro', NULL, 'Female', 'SCI-101-02-06', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(30, 3, 'Mason', 'Gonzales', NULL, 'Female', 'MATH-102-03-06', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(31, 4, 'Ethan', 'Domingo', NULL, 'Female', 'MATH-102-04-06', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(32, 5, 'Alexander', 'Castro', NULL, 'Female', 'ENG-103-05-06', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(33, 1, 'Sophia', 'Ramos', NULL, 'Male', 'FIL-005-01-07', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(34, 2, 'Noah', 'Mendoza', NULL, 'Male', 'SCI-101-02-07', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(35, 3, 'Alexander', 'Aquino', NULL, 'Male', 'MATH-102-03-07', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(36, 4, 'Noah', 'Fernandez', NULL, 'Male', 'MATH-102-04-07', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(37, 5, 'Olivia', 'Reyes', NULL, 'Male', 'ENG-103-05-07', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(38, 1, 'Benjamin', 'Rivera', NULL, 'Female', 'FIL-005-01-08', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(39, 2, 'Henry', 'Rivera', NULL, 'Female', 'SCI-101-02-08', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(40, 3, 'James', 'Mendoza', NULL, 'Female', 'MATH-102-03-08', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(41, 4, 'Alexander', 'Gonzales', NULL, 'Female', 'MATH-102-04-08', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(42, 5, 'James', 'Aquino', NULL, 'Female', 'ENG-103-05-08', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(43, 1, 'Ethan', 'Mendoza', NULL, 'Male', 'FIL-005-01-09', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(44, 2, 'Ethan', 'Mendoza', NULL, 'Male', 'SCI-101-02-09', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(45, 3, 'James', 'Santos', NULL, 'Male', 'MATH-102-03-09', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(46, 4, 'Daniel', 'Diaz', NULL, 'Male', 'MATH-102-04-09', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(47, 5, 'Charlotte', 'Fernandez', NULL, 'Male', 'ENG-103-05-09', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(48, 1, 'Henry', 'Gonzales', NULL, 'Female', 'FIL-005-01-10', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(49, 2, 'Henry', 'Rivera', NULL, 'Female', 'SCI-101-02-10', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(50, 3, 'Elijah', 'Domingo', NULL, 'Female', 'MATH-102-03-10', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(51, 4, 'Henry', 'Rivera', NULL, 'Female', 'MATH-102-04-10', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(52, 5, 'Isabella', 'Bautista', NULL, 'Female', 'ENG-103-05-10', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(53, 1, 'Alexander', 'Aquino', NULL, 'Male', 'FIL-005-01-11', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(54, 2, 'Ethan', 'Ramos', NULL, 'Male', 'SCI-101-02-11', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(55, 3, 'Benjamin', 'Torres', NULL, 'Male', 'MATH-102-03-11', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(56, 4, 'Ethan', 'Castro', NULL, 'Male', 'MATH-102-04-11', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(57, 5, 'Olivia', 'Fernandez', NULL, 'Male', 'ENG-103-05-11', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(58, 1, 'Amelia', 'Morales', NULL, 'Female', 'FIL-005-01-12', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(59, 2, 'Mia', 'Cruz', NULL, 'Female', 'SCI-101-02-12', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(60, 3, 'Charlotte', 'Reyes', NULL, 'Female', 'MATH-102-03-12', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(61, 4, 'Elijah', 'Morales', NULL, 'Female', 'MATH-102-04-12', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(62, 5, 'Mason', 'Castro', NULL, 'Female', 'ENG-103-05-12', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(63, 1, 'Ethan', 'Cruz', NULL, 'Male', 'FIL-005-01-13', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(64, 2, 'Sophia', 'Ramos', NULL, 'Male', 'SCI-101-02-13', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(65, 3, 'Henry', 'Mendoza', NULL, 'Male', 'MATH-102-03-13', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(66, 4, 'Olivia', 'Salazar', NULL, 'Male', 'MATH-102-04-13', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(67, 5, 'Olivia', 'Garcia', NULL, 'Male', 'ENG-103-05-13', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(68, 1, 'Sophia', 'Torres', NULL, 'Female', 'FIL-005-01-14', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(69, 2, 'Isabella', 'Morales', NULL, 'Female', 'SCI-101-02-14', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(70, 3, 'Benjamin', 'Reyes', NULL, 'Female', 'MATH-102-03-14', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(71, 4, 'Alexander', 'Gonzales', NULL, 'Female', 'MATH-102-04-14', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(72, 5, 'Noah', 'Rivera', NULL, 'Female', 'ENG-103-05-14', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(73, 1, 'Amelia', 'Mendoza', NULL, 'Male', 'FIL-005-01-15', 'GRADE 10', 'RIZAL', '2026-02-15 03:55:56'),
(74, 2, 'Isabella', 'Ramos', NULL, 'Male', 'SCI-101-02-15', 'GRADE 9', 'HONESTY', '2026-02-15 03:55:56'),
(75, 3, 'Amelia', 'Reyes', NULL, 'Male', 'MATH-102-03-15', 'GRADE 8', 'SATURN', '2026-02-15 03:55:56'),
(76, 4, 'Alexander', 'Ramos', NULL, 'Male', 'MATH-102-04-15', 'GRADE 8', 'MARS', '2026-02-15 03:55:56'),
(77, 5, 'Amelia', 'Garcia', NULL, 'Male', 'ENG-103-05-15', 'GRADE 7', 'PASCAL', '2026-02-15 03:55:56'),
(78, 1, 'JAPHET', 'DOROIN', 'C', 'Male', '02-2324-12345', 'GRADE 10', 'RIZAL', '2026-02-16 10:13:46'),
(79, 4, 'REN', 'DOSOL', 'O', 'Male', '02-2324-54321', 'GRADE 8', 'MARS', '2026-02-18 00:55:53');

-- --------------------------------------------------------

--
-- Table structure for table `final_grades`
--

CREATE TABLE `final_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `q1_grade` decimal(5,2) NOT NULL,
  `q2_grade` decimal(5,2) NOT NULL,
  `q3_grade` decimal(5,2) NOT NULL,
  `q4_grade` decimal(5,2) NOT NULL,
  `raw_final_grade` decimal(5,2) NOT NULL,
  `rounded_final_grade` int(11) NOT NULL,
  `remarks` varchar(50) NOT NULL,
  `finalized_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `final_grades`
--

INSERT INTO `final_grades` (`id`, `student_id`, `q1_grade`, `q2_grade`, `q3_grade`, `q4_grade`, `raw_final_grade`, `rounded_final_grade`, `remarks`, `finalized_at`) VALUES
(1, 53, 84.37, 92.00, 95.40, 97.70, 92.37, 92, 'PASSED', '2026-02-16 06:31:06'),
(2, 63, 49.56, 59.00, 55.50, 67.50, 57.89, 58, 'FAILED', '2026-02-16 06:47:52'),
(3, 79, 88.69, 85.20, 88.34, 96.10, 89.58, 90, 'PASSED', '2026-02-18 01:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `quarter` int(11) NOT NULL,
  `grade_type` enum('ww','pt','as') NOT NULL,
  `scores` text DEFAULT NULL,
  `total_score` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `quarter`, `grade_type`, `scores`, `total_score`, `created_at`) VALUES
(1, 53, 1, 'pt', '[\"45\",\"18\",\"25\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:23:15'),
(2, 53, 1, 'ww', '[\"12\",\"20\",\"10\",\"8\",\"5\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:23:15'),
(3, 53, 1, 'as', '[\"42\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:23:15'),
(4, 53, 2, 'ww', '[\"14\",\"22\",\"9\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:28:12'),
(5, 53, 2, 'as', '[\"45\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:28:12'),
(6, 53, 2, 'pt', '[\"48\",\"18\",\"27\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:28:12'),
(7, 53, 3, 'ww', '[\"15\",\"23\",\"10\",\"9\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:28:54'),
(8, 53, 3, 'pt', '[\"50\",\"19\",\"28\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:28:54'),
(9, 53, 3, 'as', '[\"46\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:28:54'),
(10, 53, 4, 'ww', '[\"14\",\"24\",\"9\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:29:45'),
(11, 53, 4, 'pt', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:29:45'),
(12, 53, 4, 'as', '[\"48\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:29:45'),
(19, 63, 1, 'pt', '[\"20\",\"10\",\"15\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:41:36'),
(20, 63, 1, 'ww', '[\"8\",\"15\",\"5\",\"6\",\"3\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:41:36'),
(21, 63, 1, 'as', '[\"28\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:41:36'),
(22, 63, 2, 'ww', '[\"10\",\"16\",\"7\",\"6\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:42:21'),
(23, 63, 2, 'pt', '[\"25\",\"12\",\"18\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:42:21'),
(24, 63, 2, 'as', '[\"30\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:42:21'),
(25, 63, 3, 'ww', '[\"9\",\"17\",\"6\",\"7\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:44:23'),
(26, 63, 3, 'as', '[\"30\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:44:23'),
(27, 63, 3, 'pt', '[\"22\",\"11\",\"15\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:44:23'),
(31, 63, 4, 'ww', '[\"12\",\"18\",\"7\",\"6\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:47:02'),
(32, 63, 4, 'as', '[\"35\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:47:02'),
(33, 63, 4, 'pt', '[\"30\",\"14\",\"20\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-16 06:47:02'),
(34, 28, 1, 'ww', '[\"01\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"10\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-16 08:26:14'),
(35, 79, 1, 'ww', '[\"13\",\"20\",\"8\",\"9\",\"8\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-18 00:58:44'),
(36, 79, 1, 'as', '[\"45\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-18 00:58:44'),
(37, 79, 1, 'pt', '[\"12\",\"24\",\"9\",\"10\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\"]', '2026-02-18 00:58:44'),
(38, 79, 2, 'ww', '[\"12\",\"24\",\"8\",\"8\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-18 00:59:39'),
(39, 79, 2, 'pt', '[\"13\",\"20\",\"8\",\"7\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\"]', '2026-02-18 00:59:39'),
(40, 79, 2, 'as', '[\"48\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-18 00:59:39'),
(44, 79, 3, 'ww', '[\"14\",\"20\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-18 01:02:12'),
(45, 79, 3, 'as', '[\"45\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-18 01:02:12'),
(46, 79, 3, 'pt', '[\"13\",\"23\",\"8\",\"8\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\"]', '2026-02-18 01:02:12'),
(47, 79, 4, 'ww', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '[\"15\",\"25\",\"10\",\"10\",\"\",\"\",\"\",\"\",\"\",\"\"]', '2026-02-18 01:03:01'),
(48, 79, 4, 'pt', '[\"45\",\"18\",\"30\",\"\",\"\",\"\",\"\"]', '[\"50\",\"20\",\"30\",\"\",\"\",\"\",\"\"]', '2026-02-18 01:03:01'),
(49, 79, 4, 'as', '[\"49\",\"\",\"\",\"\",\"\"]', '[\"50\",\"\",\"\",\"\",\"\"]', '2026-02-18 01:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `class_id`, `grade_level`, `subject`, `day_of_week`, `start_time`, `end_time`, `room`, `created_at`) VALUES
(1, 1, 'GRADE 10', 'FILIPINO', 'Monday', '07:30:00', '09:00:00', 'ROOM 105', '2026-02-15 03:20:28'),
(2, 5, 'GRADE 7', 'ENGLISH', 'Tuesday', '13:00:00', '15:00:00', 'ROOM 101', '2026-02-15 03:46:05'),
(3, 4, 'GRADE 8', 'MATHEMATICS', 'Wednesday', '09:00:00', '11:59:00', 'ROOM 102', '2026-02-15 03:47:00'),
(4, 3, 'GRADE 8', 'MATHEMATICS', 'Thursday', '14:30:00', '16:00:00', 'ROOM 103', '2026-02-15 03:47:42'),
(5, 2, 'GRADE 9', 'SCIENCE', 'Friday', '07:30:00', '10:30:00', 'ROOM 104', '2026-02-15 03:48:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'teacher',
  `student_profile_id` int(11) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `student_profile_id`, `last_login_at`, `created_at`) VALUES
(1, 'Administrator', 'admin', NULL, '$2y$10$DIhy.ppcYkX/NU14xC78/.dFRUer85PVwj4bc0s07AYyBlN4Wmwsy', 'admin', NULL, '2026-02-18 08:27:04', '2026-02-16 09:09:24'),
(21, 'Teacher One', 'teacher1', NULL, '$2y$10$DIhy.ppcYkX/NU14xC78/.dFRUer85PVwj4bc0s07AYyBlN4Wmwsy', 'teacher', NULL, NULL, '2026-02-16 09:59:20'),
(22, 'Teacher Two', 'teacher2', NULL, '$2y$10$DIhy.ppcYkX/NU14xC78/.dFRUer85PVwj4bc0s07AYyBlN4Wmwsy', 'teacher', NULL, NULL, '2026-02-16 09:59:20'),
(23, 'Student One', 'student1', NULL, '$2y$10$DIhy.ppcYkX/NU14xC78/.dFRUer85PVwj4bc0s07AYyBlN4Wmwsy', 'student', 1, NULL, '2026-02-16 09:59:20'),
(24, 'Student Two', 'student2', NULL, '$2y$10$DIhy.ppcYkX/NU14xC78/.dFRUer85PVwj4bc0s07AYyBlN4Wmwsy', 'student', 2, NULL, '2026-02-16 09:59:20'),
(32, 'VICTOR ODARVE', '02-2324-13335@student.local', '02-2324-13335@student.local', '$2y$10$4YErfST4rvuC/V3rWefnMuDTqcdhfW36gtqwTuCk.V8dphH6XGyeK', 'student', 1, NULL, '2026-02-16 10:07:30'),
(33, 'GABRIEL MADRIDANO', '02-2324-11111@student.local', '02-2324-11111@student.local', '$2y$10$4hrNswrLFPp3zO0RDrNdF.XkZTvKOox/3TlJ5JDZjy6gXSDbosyK6', 'student', 2, NULL, '2026-02-16 10:07:30'),
(34, 'Mason Morales', 'fil-005-01-01@student.local', 'fil-005-01-01@student.local', '$2y$10$Hk50v6VOEs6hTrPwTvnzl.KH53tEuleAeqDvotAe3XyNvbDOT.50W', 'student', 3, NULL, '2026-02-16 10:07:30'),
(35, 'Olivia Fernandez', 'sci-101-02-01@student.local', 'sci-101-02-01@student.local', '$2y$10$6g37nwxTcVdUnH3WO2ur5ug4wWcPQJwPNxu9vdZCcgYE12ltBea9C', 'student', 4, NULL, '2026-02-16 10:07:30'),
(36, 'Amelia Cruz', 'math-102-03-01@student.local', 'math-102-03-01@student.local', '$2y$10$zc6S4JcKea3zlUY1eRVNh.fU29trXGyAFNJxtA8StIOPTim2Jmfvm', 'student', 5, NULL, '2026-02-16 10:07:30'),
(37, 'James Mendoza', 'math-102-04-01@student.local', 'math-102-04-01@student.local', '$2y$10$sg3v8HYi.0nauqb0JvVxvOpFEICJgNv0jUw.ugiaZJ3xaOlKDmMOe', 'student', 6, NULL, '2026-02-16 10:07:30'),
(38, 'Noah Ramos', 'eng-103-05-01@student.local', 'eng-103-05-01@student.local', '$2y$10$zg1tRPoJWpCSYf5z9p2OS.tWo9NdCQdPHMKVducRr.DW4.Tna2Q22', 'student', 7, NULL, '2026-02-16 10:07:30'),
(39, 'Sophia Ramos', 'fil-005-01-02@student.local', 'fil-005-01-02@student.local', '$2y$10$Hzboagxk2jyzaxCkCS2/2.3S9P/1oYSLnGlEtLO9F4.CQ6G.u7Nj6', 'student', 8, NULL, '2026-02-16 10:07:30'),
(40, 'Lucas Garcia', 'sci-101-02-02@student.local', 'sci-101-02-02@student.local', '$2y$10$8YquRisYAdqupXErp5a39.b.HxvoTO1onEWdY6eoO3zKuLpKBljm6', 'student', 9, NULL, '2026-02-16 10:07:30'),
(41, 'Elijah Rivera', 'math-102-03-02@student.local', 'math-102-03-02@student.local', '$2y$10$cBFf/mCxYUqn5mc3sGPhaOZfIG5G73WcEBhcpJpWidZgmV7Ypfx0.', 'student', 10, NULL, '2026-02-16 10:07:30'),
(42, 'Benjamin Torres', 'math-102-04-02@student.local', 'math-102-04-02@student.local', '$2y$10$45JRiwwWs8fH7u4yZ9eJMerpXSYkFo40GdzGCCawTJciOr8SLeCQy', 'student', 11, NULL, '2026-02-16 10:07:30'),
(43, 'Elijah Bautista', 'eng-103-05-02@student.local', 'eng-103-05-02@student.local', '$2y$10$gm0BRkjgCRLo.dd5pRDgsOUj3kDHHSpxRPcwcumxz8KkTp.fa86.C', 'student', 12, NULL, '2026-02-16 10:07:30'),
(44, 'Isabella Villanueva', 'fil-005-01-03@student.local', 'fil-005-01-03@student.local', '$2y$10$2cvbH93Ay0FOxrpdWpaUrOntqe0VavGg3nP/mjIDePvfzSj4H8cu.', 'student', 13, NULL, '2026-02-16 10:07:30'),
(45, 'Sophia Flores', 'sci-101-02-03@student.local', 'sci-101-02-03@student.local', '$2y$10$HRgbpjXQWxXYGfV01JhhFuKTNbn6jnA2mOYNK6HfWVPKk3qYv5bSu', 'student', 14, NULL, '2026-02-16 10:07:30'),
(46, 'Noah Santos', 'math-102-03-03@student.local', 'math-102-03-03@student.local', '$2y$10$QwZuZ2njbtfjMv/eex2ZAOEq27vkxvsp7f/2pn3hsd5YSdh4/zbo.', 'student', 15, NULL, '2026-02-16 10:07:30'),
(47, 'Isabella Salazar', 'math-102-04-03@student.local', 'math-102-04-03@student.local', '$2y$10$pxfakqU3ZgI9A/a5.1XIYeDXkmhy8FL/oKbCCMXZiCCNTqM6SY5V6', 'student', 16, NULL, '2026-02-16 10:07:30'),
(48, 'Charlotte Mendoza', 'eng-103-05-03@student.local', 'eng-103-05-03@student.local', '$2y$10$FwMHB7JHvdoXmn2sxN100.rn7iCuEKcnokfn.mqXnnNHPZp0mxajC', 'student', 17, NULL, '2026-02-16 10:07:30'),
(49, 'Ethan Navarro', 'fil-005-01-04@student.local', 'fil-005-01-04@student.local', '$2y$10$ap0HAPdxx77hxs9e60ZqueT1nlStsTEVpQDL44CoeCPhi9Jn2k/BG', 'student', 18, NULL, '2026-02-16 10:07:30'),
(50, 'Emma Villanueva', 'sci-101-02-04@student.local', 'sci-101-02-04@student.local', '$2y$10$4wboMlIO.QDEP/KoEoHmTuwbOVk6GZ65.5RzNeuQEo2yrcDz5vMFa', 'student', 19, NULL, '2026-02-16 10:07:31'),
(51, 'Henry Garcia', 'math-102-03-04@student.local', 'math-102-03-04@student.local', '$2y$10$2ihvAiNGCqZP6o5IDbYdAeeICAa62qc99oo2eHRDOukCDPSiR0Op.', 'student', 20, NULL, '2026-02-16 10:07:31'),
(52, 'Ava Flores', 'math-102-04-04@student.local', 'math-102-04-04@student.local', '$2y$10$aOYl1lQ9/2tIUzin8jP1Suu.CaEpihpIqnQLISTF1zG4M1nqjclju', 'student', 21, NULL, '2026-02-16 10:07:31'),
(53, 'Isabella Cruz', 'eng-103-05-04@student.local', 'eng-103-05-04@student.local', '$2y$10$JfYL0x1qYgHKvay3jHjiMutGKW54xQEvQFJS..JD7hIaeAedJZHQW', 'student', 22, NULL, '2026-02-16 10:07:31'),
(54, 'Ava Navarro', 'fil-005-01-05@student.local', 'fil-005-01-05@student.local', '$2y$10$wHQ.FVsCvYXITAQg957rwOx3lJq0tWUC/UjtOabTxnei.WA/qZIOW', 'student', 23, NULL, '2026-02-16 10:07:31'),
(55, 'James Diaz', 'sci-101-02-05@student.local', 'sci-101-02-05@student.local', '$2y$10$Rjg05q5AjeS25aQSxecaJ.8fZP5VOUmEO81sd6Kk/VeU7aS78sCq2', 'student', 24, NULL, '2026-02-16 10:07:31'),
(56, 'Ava Aquino', 'math-102-03-05@student.local', 'math-102-03-05@student.local', '$2y$10$dVZXyK0ftnZk8uXjwkXHr.m7Bn3KKbeySpjvWSKbO0VHBCF.xUMBu', 'student', 25, NULL, '2026-02-16 10:07:31'),
(57, 'Lucas Bautista', 'math-102-04-05@student.local', 'math-102-04-05@student.local', '$2y$10$jvUUNfDK7zy06zGUOUJ5Pu3dq1bP8xNiW4JwyioLHnEgGT6HJL3SW', 'student', 26, NULL, '2026-02-16 10:07:31'),
(58, 'Sophia Castro', 'eng-103-05-05@student.local', 'eng-103-05-05@student.local', '$2y$10$fHEUm4LQ6nuNfVdV1MoAS.cKxEmdvaC2aZ1itzWE43RoCxE.euLTS', 'student', 27, NULL, '2026-02-16 10:07:31'),
(59, 'Ava Diaz', 'fil-005-01-06@student.local', 'fil-005-01-06@student.local', '$2y$10$GO3oXIWyyfFq4gjFveSIT.bpQZ1jZgQGoEOwu3K.Nx8Vnufj8s8o6', 'student', 28, NULL, '2026-02-16 10:07:31'),
(60, 'Daniel Castro', 'sci-101-02-06@student.local', 'sci-101-02-06@student.local', '$2y$10$bH26mt0WopBE8mkAe3NEdesr5HQGHa8VLiUtYB/91VNOsXXUI46ku', 'student', 29, NULL, '2026-02-16 10:07:31'),
(61, 'Mason Gonzales', 'math-102-03-06@student.local', 'math-102-03-06@student.local', '$2y$10$WCcTX2tCRHGNaLCvCq2zL.0dTY/69RmpTgNXWhbho.QKDrkMzRNG.', 'student', 30, NULL, '2026-02-16 10:07:31'),
(62, 'Ethan Domingo', 'math-102-04-06@student.local', 'math-102-04-06@student.local', '$2y$10$8RhiejKGfhgbOc6GdnC4W.FU2hwWdWeT.sTA6HKqnwKys/WXUXZp6', 'student', 31, NULL, '2026-02-16 10:07:31'),
(63, 'Alexander Castro', 'eng-103-05-06@student.local', 'eng-103-05-06@student.local', '$2y$10$TEzu3K7xHvlzOlZR1KP3DOKEZUSuZCyKxKT7unNx/14FugJdZwKJ6', 'student', 32, NULL, '2026-02-16 10:07:31'),
(64, 'Sophia Ramos', 'fil-005-01-07@student.local', 'fil-005-01-07@student.local', '$2y$10$JqCNCpzCIa9oueBhFPpEHexc9eeq.W5RxxWtBFgmywJQPzcjT9766', 'student', 33, NULL, '2026-02-16 10:07:31'),
(65, 'Noah Mendoza', 'sci-101-02-07@student.local', 'sci-101-02-07@student.local', '$2y$10$oWrXeguVPSKbf9kHSnbkOuXWtynDC3gzeVoQBR2z3l2qPJXn0X8mK', 'student', 34, NULL, '2026-02-16 10:07:31'),
(66, 'Alexander Aquino', 'math-102-03-07@student.local', 'math-102-03-07@student.local', '$2y$10$3WpFjlQELEQJgO/Lny3K/.MsOfZjDeRk0lwhrSWkiGJ3LLnwk5fw2', 'student', 35, NULL, '2026-02-16 10:07:31'),
(67, 'Noah Fernandez', 'math-102-04-07@student.local', 'math-102-04-07@student.local', '$2y$10$cr.EkwIQ.wDALmpfOsDyjOjomZG4vXdIP6wHX5N6C7guQwjxvzoxG', 'student', 36, NULL, '2026-02-16 10:07:31'),
(68, 'Olivia Reyes', 'eng-103-05-07@student.local', 'eng-103-05-07@student.local', '$2y$10$ybGS9lw98HvTW5WtO1Qxteo6H7sVdQBFk.3cLhytH6u3jbKO5rq52', 'student', 37, NULL, '2026-02-16 10:07:31'),
(69, 'Benjamin Rivera', 'fil-005-01-08@student.local', 'fil-005-01-08@student.local', '$2y$10$dCwD7aqHV57L2Tuk3L4K.O7eWN/oR1wQLt1Hj1ZSint4.uYrpZpb.', 'student', 38, NULL, '2026-02-16 10:07:31'),
(70, 'Henry Rivera', 'sci-101-02-08@student.local', 'sci-101-02-08@student.local', '$2y$10$PjtQk0ulhScCTOhKd34QmepGVuMJgCSVHA75uRMmOsrqkOPiwb.wO', 'student', 39, NULL, '2026-02-16 10:07:32'),
(71, 'James Mendoza', 'math-102-03-08@student.local', 'math-102-03-08@student.local', '$2y$10$t//WhJkq6uG8KYXxq13Ri.6bh0HJ.T2hhSpAeTnP34xLsJA6taB1i', 'student', 40, NULL, '2026-02-16 10:07:32'),
(72, 'Alexander Gonzales', 'math-102-04-08@student.local', 'math-102-04-08@student.local', '$2y$10$n1UQowX1sIu/zbbfGEqAsOcuJ8DtkPluNyG8X.E.UI48BL7YbPoQu', 'student', 41, NULL, '2026-02-16 10:07:32'),
(73, 'James Aquino', 'eng-103-05-08@student.local', 'eng-103-05-08@student.local', '$2y$10$/upnpnwva0PSPOOGoY6PnOzeYDzwyQR0cnPDiHIVKy54GMM8Gqjqi', 'student', 42, NULL, '2026-02-16 10:07:32'),
(74, 'Ethan Mendoza', 'fil-005-01-09@student.local', 'fil-005-01-09@student.local', '$2y$10$Y9PZwU8KE18ErZpSl/oBWOXYlJ31KTox15O7Z4UrlPZ6KNzGrgX62', 'student', 43, NULL, '2026-02-16 10:07:32'),
(75, 'Ethan Mendoza', 'sci-101-02-09@student.local', 'sci-101-02-09@student.local', '$2y$10$sHluff1waBK.K8J0Ix1wWO01GTKmyNvz.6jkHY2bmTVqSUMX./NOW', 'student', 44, NULL, '2026-02-16 10:07:32'),
(76, 'James Santos', 'math-102-03-09@student.local', 'math-102-03-09@student.local', '$2y$10$UsOXFmvtLBdDVkZ3wMkBUOcBpB7wTNqLisVCT.l27g7NRY6niNHmm', 'student', 45, NULL, '2026-02-16 10:07:32'),
(77, 'Daniel Diaz', 'math-102-04-09@student.local', 'math-102-04-09@student.local', '$2y$10$IZR8ZvDGWRfEPpnOzt6OwuJqOO.5B4wk7ugHz8G2JB/.c1lW5cfDO', 'student', 46, NULL, '2026-02-16 10:07:32'),
(78, 'Charlotte Fernandez', 'eng-103-05-09@student.local', 'eng-103-05-09@student.local', '$2y$10$GmZZ9tzP1IlYpvuTrsc4Qe3EweRl15WwlNfLX7dcmolftZPd09GFm', 'student', 47, NULL, '2026-02-16 10:07:32'),
(79, 'Henry Gonzales', 'fil-005-01-10@student.local', 'fil-005-01-10@student.local', '$2y$10$v1NyJlBa.S/qxVF8BHCS6.XoZmdj6i4n75XA.3MID/q9hEiIJ6eb6', 'student', 48, NULL, '2026-02-16 10:07:32'),
(80, 'Henry Rivera', 'sci-101-02-10@student.local', 'sci-101-02-10@student.local', '$2y$10$66WgB8yRwiXpdZgVnlP4dOdA8kYQ5n4VAJhJQcJRx3iRINkd7hwHu', 'student', 49, NULL, '2026-02-16 10:07:32'),
(81, 'Elijah Domingo', 'math-102-03-10@student.local', 'math-102-03-10@student.local', '$2y$10$Y168Vm7qDf.NmkifMmOxQuYkSHA6aghXLZpKrQJbpfzTTdQjAaWzS', 'student', 50, NULL, '2026-02-16 10:07:32'),
(82, 'Henry Rivera', 'math-102-04-10@student.local', 'math-102-04-10@student.local', '$2y$10$w0WhE2wGB1n1HPXfJ5Mjou9VNiQAgcJ2xECoXjXghdqUMRSd1lA8m', 'student', 51, NULL, '2026-02-16 10:07:32'),
(83, 'Isabella Bautista', 'eng-103-05-10@student.local', 'eng-103-05-10@student.local', '$2y$10$HlXi90owkjNqzXS5KOgTJ.npqAFPN/SAqHQC/v43egs0aNSmtWDGG', 'student', 52, NULL, '2026-02-16 10:07:32'),
(84, 'Alexander Aquino', 'fil-005-01-11@student.local', 'fil-005-01-11@student.local', '$2y$10$llAPCrUJ.scxnK0TveDBW.akN34vRoXT5xLHZVRd40iUcVxByL9P2', 'student', 53, '2026-02-16 18:31:22', '2026-02-16 10:07:32'),
(85, 'Ethan Ramos', 'sci-101-02-11@student.local', 'sci-101-02-11@student.local', '$2y$10$abVFfNLvS5OqMkytPiYzeucZqW7bWTIOv2IeYLcRep5hgi973P3ja', 'student', 54, NULL, '2026-02-16 10:07:32'),
(86, 'Benjamin Torres', 'math-102-03-11@student.local', 'math-102-03-11@student.local', '$2y$10$zsyEtlS1tdl7Hy/O7.3OP.60bQXu9VgFuOne9UoXI/cq.dPocL/gq', 'student', 55, NULL, '2026-02-16 10:07:32'),
(87, 'Ethan Castro', 'math-102-04-11@student.local', 'math-102-04-11@student.local', '$2y$10$B8MSlQ2Hf5w0EmxbBHnaP.Wla3eddd83shov/WbrNlbz.RckVTOQG', 'student', 56, NULL, '2026-02-16 10:07:32'),
(88, 'Olivia Fernandez', 'eng-103-05-11@student.local', 'eng-103-05-11@student.local', '$2y$10$IkQe9f86dYGYvX3aavUBB.e.SXzq2Z7SBvvalGo1KdteNEgWeT2Wa', 'student', 57, NULL, '2026-02-16 10:07:32'),
(89, 'Amelia Morales', 'fil-005-01-12@student.local', 'fil-005-01-12@student.local', '$2y$10$Qz6E0MmGujdJOZota/reJu/KlUxF4S5I0afipZ08UDBynHZfHOo0O', 'student', 58, NULL, '2026-02-16 10:07:32'),
(90, 'Mia Cruz', 'sci-101-02-12@student.local', 'sci-101-02-12@student.local', '$2y$10$D792ejIFuWNOgfEcMdEMgOv2Lu48kqIEIyogjVyja9HEiVYS9i8dq', 'student', 59, NULL, '2026-02-16 10:07:33'),
(91, 'Charlotte Reyes', 'math-102-03-12@student.local', 'math-102-03-12@student.local', '$2y$10$SeLO6HORr2vA3Og3KEq98uir40Gevp7tJafr.NXLUoti73xpx27rq', 'student', 60, NULL, '2026-02-16 10:07:33'),
(92, 'Elijah Morales', 'math-102-04-12@student.local', 'math-102-04-12@student.local', '$2y$10$Vb4EB6MiicJ9cuPoluZGu.FAc9LRAHykao4xO5sMytTdiDZ.oypnm', 'student', 61, NULL, '2026-02-16 10:07:33'),
(93, 'Mason Castro', 'eng-103-05-12@student.local', 'eng-103-05-12@student.local', '$2y$10$p8/7nUEx2/JsQytJFTwwiuxGG/lcZcuoSbQD4faT/BtO7Rst8f0hy', 'student', 62, NULL, '2026-02-16 10:07:33'),
(94, 'Ethan Cruz', 'fil-005-01-13@student.local', 'fil-005-01-13@student.local', '$2y$10$9Q0xI2QYWqOREIcVYUxF7eMTmv4EK5nT4wBF6.kDWLJihB8k8u6/K', 'student', 63, NULL, '2026-02-16 10:07:33'),
(95, 'Sophia Ramos', 'sci-101-02-13@student.local', 'sci-101-02-13@student.local', '$2y$10$2iGs495TnvlUQfFW9weL7Oe5De0bUniarjY2YHSooM1KjvBO0oPMS', 'student', 64, NULL, '2026-02-16 10:07:33'),
(96, 'Henry Mendoza', 'math-102-03-13@student.local', 'math-102-03-13@student.local', '$2y$10$mJE4y7M4mVNZDH8kYPilKu3vmXkhtSsvXZUTi.5Ufu80zbFsdMCEe', 'student', 65, NULL, '2026-02-16 10:07:33'),
(97, 'Olivia Salazar', 'math-102-04-13@student.local', 'math-102-04-13@student.local', '$2y$10$IZg2C0voBPruKy4ASUEuqulzEjuRjW09O7KNrWhbpDoY7TUyxUzDq', 'student', 66, NULL, '2026-02-16 10:07:33'),
(98, 'Olivia Garcia', 'eng-103-05-13@student.local', 'eng-103-05-13@student.local', '$2y$10$6AoaGA57VoBKmzavlnRy/uH5de9oq2lu.MDuAI6EGdkzndjb1GOiq', 'student', 67, NULL, '2026-02-16 10:07:33'),
(99, 'Sophia Torres', 'fil-005-01-14@student.local', 'fil-005-01-14@student.local', '$2y$10$m7pJ5JEMjZKUwgsQZg60ZuWQJkVkfxHTVdg4d15xV9nrCO9TZrDh6', 'student', 68, NULL, '2026-02-16 10:07:33'),
(100, 'Isabella Morales', 'sci-101-02-14@student.local', 'sci-101-02-14@student.local', '$2y$10$Auj/LbWTSJwQ.73xUc5ZS.Ofal5z0P8T48kvyPUIzLdk2F4K2g8Ca', 'student', 69, NULL, '2026-02-16 10:07:33'),
(101, 'Benjamin Reyes', 'math-102-03-14@student.local', 'math-102-03-14@student.local', '$2y$10$nmvfV31akerWwRIlXmdc7.efNekkoG4lVrfesoIeg/anhYLPoE7wG', 'student', 70, NULL, '2026-02-16 10:07:33'),
(102, 'Alexander Gonzales', 'math-102-04-14@student.local', 'math-102-04-14@student.local', '$2y$10$TgyyxhKoSS/QpD4VX8BLZuyCuSC9EheSeVn/.9wt/Z1mYulmMCRKO', 'student', 71, NULL, '2026-02-16 10:07:33'),
(103, 'Noah Rivera', 'eng-103-05-14@student.local', 'eng-103-05-14@student.local', '$2y$10$qHCHEToknU8rtsIsqlgcteNgWqskZKMY6BlXBd1.yDanvflG34xZa', 'student', 72, NULL, '2026-02-16 10:07:33'),
(104, 'Amelia Mendoza', 'fil-005-01-15@student.local', 'fil-005-01-15@student.local', '$2y$10$tdNnX6G/EhdQz4Bc7tBBduoavDfy1DUB.E//qyIWVt4YdUMA9fErm', 'student', 73, NULL, '2026-02-16 10:07:33'),
(105, 'Isabella Ramos', 'sci-101-02-15@student.local', 'sci-101-02-15@student.local', '$2y$10$dPdg9Pm3.xyMxqfgMuAlae487BScAb8htRymjFz35GM1VDCdvs4vO', 'student', 74, NULL, '2026-02-16 10:07:33'),
(106, 'Amelia Reyes', 'math-102-03-15@student.local', 'math-102-03-15@student.local', '$2y$10$rDUxd15bmGsZDb8w8WcdleNy2M2R/hhtGwrGDBqwbkU/fNDX.ZWcK', 'student', 75, NULL, '2026-02-16 10:07:33'),
(107, 'Alexander Ramos', 'math-102-04-15@student.local', 'math-102-04-15@student.local', '$2y$10$DUPTVahWHcdi5uxFp4IWfu/MLFHp6ZWm.eV23XGmertPW76Me1d.i', 'student', 76, NULL, '2026-02-16 10:07:33'),
(108, 'Amelia Garcia', 'eng-103-05-15@student.local', 'eng-103-05-15@student.local', '$2y$10$0ESQZGaCiQcCvhvHcGVKhufsiY4CseFUu7VlqpBgcJOn4eX0/DyH.', 'student', 77, NULL, '2026-02-16 10:07:33'),
(421, 'VICTOR ODARVE', '02-2324-13335', '02-2324-13335', '$2y$10$X99fAqMef9uUWQGk1x/YZ.9kU.Ba/iA/6PdSVU.vS9O3/O3ivl8dK', 'student', 1, NULL, '2026-02-16 10:11:00'),
(422, 'GABRIEL MADRIDANO', '02-2324-11111', '02-2324-11111', '$2y$10$7tP8LwL1.mFVPuX/mKswhurnXLMfLO2R5DTJ80wbmvVORpFiQHNCe', 'student', 2, NULL, '2026-02-16 10:11:00'),
(423, 'Mason Morales', 'fil-005-01-01', 'fil-005-01-01', '$2y$10$CCxSO8dax.SIeeESUXvCwurUJt6Jmvd.KYlc3UsS9qPc5RUeIq2tO', 'student', 3, NULL, '2026-02-16 10:11:00'),
(424, 'Olivia Fernandez', 'sci-101-02-01', 'sci-101-02-01', '$2y$10$uFDh79SEh68fLycmiFm.l.ZwIfeaSV/Gr3o0ZXg.QK7Wy8iPtEadS', 'student', 4, NULL, '2026-02-16 10:11:00'),
(425, 'Amelia Cruz', 'math-102-03-01', 'math-102-03-01', '$2y$10$xP/RzOi9CZVOAR69HunX/uCb/uUSo52h5wgIsgIaloLdDqpb6kh9K', 'student', 5, NULL, '2026-02-16 10:11:00'),
(426, 'James Mendoza', 'math-102-04-01', 'math-102-04-01', '$2y$10$.cZivmSBuewgBTUdlLxIQetwUa1XIS4KoZy5BSKw/zfCmykshphyW', 'student', 6, NULL, '2026-02-16 10:11:00'),
(427, 'Noah Ramos', 'eng-103-05-01', 'eng-103-05-01', '$2y$10$dzde5vrmAMQG08BZp4MxsOVmVF1VcX5Nyxa.PG.AP0G.ia48CiiBe', 'student', 7, NULL, '2026-02-16 10:11:00'),
(428, 'Sophia Ramos', 'fil-005-01-02', 'fil-005-01-02', '$2y$10$b0jvLf5.Zc5oRNqFVEI4purz9NGlyU7c/ERN87c6TloHEBynTP1uG', 'student', 8, NULL, '2026-02-16 10:11:00'),
(429, 'Lucas Garcia', 'sci-101-02-02', 'sci-101-02-02', '$2y$10$UaoUL1I0nTV3w3aUGysV5eTvKnqLr9VKDrOoBAuWZbcAjQgcB4XLe', 'student', 9, NULL, '2026-02-16 10:11:00'),
(430, 'Elijah Rivera', 'math-102-03-02', 'math-102-03-02', '$2y$10$jVgE5bXZTBm.mPuLhOdpt.yz59/NSVc87PKoqDDSrPQPZs/D3qQ.y', 'student', 10, NULL, '2026-02-16 10:11:00'),
(431, 'Benjamin Torres', 'math-102-04-02', 'math-102-04-02', '$2y$10$cpAlY9bLYVfa3TajTky07.O/XYaMo1biJjgl0qD4G3rTsPU2ilJR6', 'student', 11, NULL, '2026-02-16 10:11:00'),
(432, 'Elijah Bautista', 'eng-103-05-02', 'eng-103-05-02', '$2y$10$VVJJBLIrQNWMDCLtLwkvBeEvc8Y9444T8ycv7AkEI69iF3IhakhRm', 'student', 12, NULL, '2026-02-16 10:11:00'),
(433, 'Isabella Villanueva', 'fil-005-01-03', 'fil-005-01-03', '$2y$10$zjjGZ9/KiSR0xM.hMZqkA.AXXmKnI6yNWVlX1zxNyfYoGfKsxjed2', 'student', 13, NULL, '2026-02-16 10:11:01'),
(434, 'Sophia Flores', 'sci-101-02-03', 'sci-101-02-03', '$2y$10$Fq/xJ.y23AoGUx1HgcJPAeqxAmu4SIdnASRWyBDOvRBmUJRcYNWdq', 'student', 14, NULL, '2026-02-16 10:11:01'),
(435, 'Noah Santos', 'math-102-03-03', 'math-102-03-03', '$2y$10$t0sPit.D9ZvFMo.ECnNE9.MNB/8xg/UY4knz/Jcocm5ro7QY5cL6m', 'student', 15, NULL, '2026-02-16 10:11:01'),
(436, 'Isabella Salazar', 'math-102-04-03', 'math-102-04-03', '$2y$10$4dU/uweoaJhPTPAyggvJC.aoZi1dbTivVHnPbB/vAzbR0v.sOpPva', 'student', 16, NULL, '2026-02-16 10:11:01'),
(437, 'Charlotte Mendoza', 'eng-103-05-03', 'eng-103-05-03', '$2y$10$BkAJeHqJHRl.byHmWumafuNca9hWu.ZioeZTvk5IgPU4qXor0xmKC', 'student', 17, NULL, '2026-02-16 10:11:01'),
(438, 'Ethan Navarro', 'fil-005-01-04', 'fil-005-01-04', '$2y$10$A0xaDRzzEiqrGstYP919we/As.Dr.gFG0NVuEVObwBZ/cpRDjnefa', 'student', 18, NULL, '2026-02-16 10:11:01'),
(439, 'Emma Villanueva', 'sci-101-02-04', 'sci-101-02-04', '$2y$10$tfyVotI8ekvl7KzrOkgrw.eE1xw8JgmQzIvlpQYcsXm37t6MHYMEa', 'student', 19, NULL, '2026-02-16 10:11:01'),
(440, 'Henry Garcia', 'math-102-03-04', 'math-102-03-04', '$2y$10$U.UeYIP/tWj.15r.1KrX9eXkxSLaFmDStC6R3TGiMaGASGEjY3uX.', 'student', 20, NULL, '2026-02-16 10:11:01'),
(441, 'Ava Flores', 'math-102-04-04', 'math-102-04-04', '$2y$10$EVcnre3kMSLV0zHIn4GX4eKCeb22Fbuyl86fZKMRbjDg09j.zlCm6', 'student', 21, NULL, '2026-02-16 10:11:01'),
(442, 'Isabella Cruz', 'eng-103-05-04', 'eng-103-05-04', '$2y$10$Uzwq5OELUtUJU1fwjpHQmem3QXWckCluxA/tMNbHdu/x3qqz3Xvl6', 'student', 22, NULL, '2026-02-16 10:11:01'),
(443, 'Ava Navarro', 'fil-005-01-05', 'fil-005-01-05', '$2y$10$8uFwLsC9RN2S/ajcNCmnVOOEr4J3nxoqAof76IlVFIzezatlUARcC', 'student', 23, NULL, '2026-02-16 10:11:01'),
(444, 'James Diaz', 'sci-101-02-05', 'sci-101-02-05', '$2y$10$VDm2h8vxfelA63kNaT6XEen7CJLM719cHLY0Adfrvacod4y.G2E0C', 'student', 24, NULL, '2026-02-16 10:11:01'),
(445, 'Ava Aquino', 'math-102-03-05', 'math-102-03-05', '$2y$10$iQfW6Ehw87LfnrH/5DjdAu4fFtJ5zcgcZNrDzQq/fUoQsRZ.6W98e', 'student', 25, NULL, '2026-02-16 10:11:01'),
(446, 'Lucas Bautista', 'math-102-04-05', 'math-102-04-05', '$2y$10$x.aPx2Z8fYQOIwg4AWJNOeBxz1fDzedZS93sHTn1LKfTRtbQclLDe', 'student', 26, NULL, '2026-02-16 10:11:01'),
(447, 'Sophia Castro', 'eng-103-05-05', 'eng-103-05-05', '$2y$10$K3HxMcdDwPhHx8v2hOor4.JXAWWS71x6/Ltc8lv8EUxI3X6ZjAO3C', 'student', 27, NULL, '2026-02-16 10:11:01'),
(448, 'Ava Diaz', 'fil-005-01-06', 'fil-005-01-06', '$2y$10$28dJ0FySIY.opgO157o2QOZeacbQd754/Y13XO6kAivguGNZJwvuK', 'student', 28, NULL, '2026-02-16 10:11:01'),
(449, 'Daniel Castro', 'sci-101-02-06', 'sci-101-02-06', '$2y$10$jNVUZhKrDVefx.PylQP/RuSgMqgyLEDhKHTB173W7rx5YFHREn2w6', 'student', 29, NULL, '2026-02-16 10:11:01'),
(450, 'Mason Gonzales', 'math-102-03-06', 'math-102-03-06', '$2y$10$2cNujSkqeBjWMO.0l7wiru/40ExJ5wvL8G2itNX5r6upkZZC4GmeW', 'student', 30, NULL, '2026-02-16 10:11:01'),
(451, 'Ethan Domingo', 'math-102-04-06', 'math-102-04-06', '$2y$10$JcinRKzcjjxB1U9Qeo5yTu/j1yH8kTAIvnCJ4Y98BKgrLnQDPFKqm', 'student', 31, NULL, '2026-02-16 10:11:01'),
(452, 'Alexander Castro', 'eng-103-05-06', 'eng-103-05-06', '$2y$10$Th.3qZRc.12D5.7j/t2yHOz2Rt2DQEpeYRGwWh6jN2tmVsd7RO5mW', 'student', 32, NULL, '2026-02-16 10:11:01'),
(453, 'Sophia Ramos', 'fil-005-01-07', 'fil-005-01-07', '$2y$10$37mQB1A20BFYgdaY.Zg5DeyHC/8tB6oAO4AoVeuI0ddOXis7Um.M.', 'student', 33, NULL, '2026-02-16 10:11:02'),
(454, 'Noah Mendoza', 'sci-101-02-07', 'sci-101-02-07', '$2y$10$ISP5Nflr6vWMV.n0zFkVme6K51YsOoLTYsEKRnc54Htrgc7vLP4Ny', 'student', 34, NULL, '2026-02-16 10:11:02'),
(455, 'Alexander Aquino', 'math-102-03-07', 'math-102-03-07', '$2y$10$b6ovN5pEuqLN6OW87JTi7OnitgfP5hW/xnFBoT0k7Ag73c3WUKRN.', 'student', 35, NULL, '2026-02-16 10:11:02'),
(456, 'Noah Fernandez', 'math-102-04-07', 'math-102-04-07', '$2y$10$NEBPooceCarL3Q7hbFLpse8d0jjjJsGha4jtFBWoyyDeaYqsWCBrS', 'student', 36, NULL, '2026-02-16 10:11:02'),
(457, 'Olivia Reyes', 'eng-103-05-07', 'eng-103-05-07', '$2y$10$Q1Fbm3qKNrbqufUJz4.wfuhxoRjoElVDw6kHI4OAgLU.dmqFQQ8wq', 'student', 37, NULL, '2026-02-16 10:11:02'),
(458, 'Benjamin Rivera', 'fil-005-01-08', 'fil-005-01-08', '$2y$10$2eDBucw.OKHGhwtP0VGU1eyJw/XltA23PEdremsWjb0QHX/RUaQkW', 'student', 38, NULL, '2026-02-16 10:11:02'),
(459, 'Henry Rivera', 'sci-101-02-08', 'sci-101-02-08', '$2y$10$SM67FFEwB2zg9lrjQBlSiOl0GhIX3IigRmKINiT4b7inCtaW4SOgW', 'student', 39, NULL, '2026-02-16 10:11:02'),
(460, 'James Mendoza', 'math-102-03-08', 'math-102-03-08', '$2y$10$GKwN7FWOEGm.musjZ0O.xeTwtLakHPpFpv43BjpwvHdvowZe1Stl6', 'student', 40, NULL, '2026-02-16 10:11:02'),
(461, 'Alexander Gonzales', 'math-102-04-08', 'math-102-04-08', '$2y$10$3GoWX6w/7Wt1bm38iB3CSeBf6EXeSgbnzW8wTmlctm5.zh.nU1IQS', 'student', 41, NULL, '2026-02-16 10:11:02'),
(462, 'James Aquino', 'eng-103-05-08', 'eng-103-05-08', '$2y$10$E/I8QkKrzkBPY5WCNH1dEu9Wsf1FESDxlMUBDHLyxKQJ5p1wvO0.a', 'student', 42, NULL, '2026-02-16 10:11:02'),
(463, 'Ethan Mendoza', 'fil-005-01-09', 'fil-005-01-09', '$2y$10$2nh2gdsHUxS.C7Q0hoK0BOXeNdHLvqsLNgIC1ZidPBIyrzAzMmiGG', 'student', 43, NULL, '2026-02-16 10:11:02'),
(464, 'Ethan Mendoza', 'sci-101-02-09', 'sci-101-02-09', '$2y$10$JmV34/ppUIo2MjHpVsw2iuoCsbZLLxnVN/xsy8Ar8j4SMnsGvcHVC', 'student', 44, NULL, '2026-02-16 10:11:02'),
(465, 'James Santos', 'math-102-03-09', 'math-102-03-09', '$2y$10$kRWO3LL.tJwJpPMzFV1QtOeTF5K.R3bDifYo7PYRM/JIEq6sWvPyO', 'student', 45, NULL, '2026-02-16 10:11:02'),
(466, 'Daniel Diaz', 'math-102-04-09', 'math-102-04-09', '$2y$10$Pp/5SbFMkSk4FNGta8Tkq.jXZu17Qm4QgOrX8wNcfNzjUhjnuQMpu', 'student', 46, NULL, '2026-02-16 10:11:02'),
(467, 'Charlotte Fernandez', 'eng-103-05-09', 'eng-103-05-09', '$2y$10$BgxSM1dklVivvFAX78ov/eipGHqKG4ZzafN0R0CrlnczQHtELcXba', 'student', 47, NULL, '2026-02-16 10:11:02'),
(468, 'Henry Gonzales', 'fil-005-01-10', 'fil-005-01-10', '$2y$10$szo4Hm/3.SVSKjHgrYPgoulW.vHq4H0TqWI1Dg1bsBT7FQdgBmgve', 'student', 48, NULL, '2026-02-16 10:11:02'),
(469, 'Henry Rivera', 'sci-101-02-10', 'sci-101-02-10', '$2y$10$w9cyzyilyZBw13mq90IVDOgRuYQqo2drccNquqp5QInyrBxLiPSwe', 'student', 49, NULL, '2026-02-16 10:11:02'),
(470, 'Elijah Domingo', 'math-102-03-10', 'math-102-03-10', '$2y$10$VOOi.dlnOHuBp5zoLGPSs.QR9qpOAS2SQXW67PlsdVhSg3OabRzrC', 'student', 50, NULL, '2026-02-16 10:11:02'),
(471, 'Henry Rivera', 'math-102-04-10', 'math-102-04-10', '$2y$10$XvGHN1ynJ8SjL05NiOU8turoVs442eEGwSy/0nrSEvcaH9YWYShPq', 'student', 51, NULL, '2026-02-16 10:11:02'),
(472, 'Isabella Bautista', 'eng-103-05-10', 'eng-103-05-10', '$2y$10$eU/uZCIVGq6QxOym/1AUkeNSlMthtlCbzikb8ZXS5kGhpAlKZ5eeW', 'student', 52, NULL, '2026-02-16 10:11:02'),
(473, 'Alexander Aquino', 'fil-005-01-11', 'fil-005-01-11', '$2y$10$Z81OLL1zUtGZ822L2YgVauTLX82I1/UK4QoKNAtTkU7/GeMdIgiKK', 'student', 53, NULL, '2026-02-16 10:11:03'),
(474, 'Ethan Ramos', 'sci-101-02-11', 'sci-101-02-11', '$2y$10$PWylbY8I9iP.17AkjLcgvOsjpiF88hnekGRemORfonof9Q.FoLPk2', 'student', 54, NULL, '2026-02-16 10:11:03'),
(475, 'Benjamin Torres', 'math-102-03-11', 'math-102-03-11', '$2y$10$SNCBNY.KS90c7my74B1QqO0Z/J2Vdke6ephCfCIDz2ANVeTOPH9XO', 'student', 55, NULL, '2026-02-16 10:11:03'),
(476, 'Ethan Castro', 'math-102-04-11', 'math-102-04-11', '$2y$10$vajupvujBhYJM/7AfAte0uIeF1pCG8LbAwop0tjgZYBx8WWEeeJMS', 'student', 56, NULL, '2026-02-16 10:11:03'),
(477, 'Olivia Fernandez', 'eng-103-05-11', 'eng-103-05-11', '$2y$10$Ox3Aiex5afaD/EK6cW/WouU2a3/rTxY5Gay2Wqcy/6yDezKot1j9C', 'student', 57, NULL, '2026-02-16 10:11:03'),
(478, 'Amelia Morales', 'fil-005-01-12', 'fil-005-01-12', '$2y$10$O0Kux2HOrPRpo9UySuvsG.gTxkouQ9aTpATgS5rEmWh0LHchuNKi.', 'student', 58, NULL, '2026-02-16 10:11:03'),
(479, 'Mia Cruz', 'sci-101-02-12', 'sci-101-02-12', '$2y$10$jgOvtva3WWFYBL54LXCXiee6EN9h05ziQYtTzOerPrac6Fkkw2IO.', 'student', 59, NULL, '2026-02-16 10:11:03'),
(480, 'Charlotte Reyes', 'math-102-03-12', 'math-102-03-12', '$2y$10$wmff3xGQoGYppR8UhzvMTuh4TF9.eT7QtvlFEwQWWYlSXzIhfZ666', 'student', 60, NULL, '2026-02-16 10:11:03'),
(481, 'Elijah Morales', 'math-102-04-12', 'math-102-04-12', '$2y$10$YBjnUhchWnStLC318RVpReq8aJFgqRUqnByZUQet7SqiWkucnUbDS', 'student', 61, NULL, '2026-02-16 10:11:03'),
(482, 'Mason Castro', 'eng-103-05-12', 'eng-103-05-12', '$2y$10$ObF1ZgLLzwcirlcFZj0Bu.9KJRmeW/Nbr8n0TBq/zpsnxxJTmM36m', 'student', 62, NULL, '2026-02-16 10:11:03'),
(483, 'Ethan Cruz', 'fil-005-01-13', 'fil-005-01-13', '$2y$10$KoIi2Fp.b2Uptm8w1d4.leRY.upbC2L7/pPjHaoaltgDgbUsRoKzq', 'student', 63, NULL, '2026-02-16 10:11:03'),
(484, 'Sophia Ramos', 'sci-101-02-13', 'sci-101-02-13', '$2y$10$kzaD.iIqeEpFrKlcIkIrOe/VVbHUupAJLMFco.ABnx2aNNJA8m592', 'student', 64, NULL, '2026-02-16 10:11:03'),
(485, 'Henry Mendoza', 'math-102-03-13', 'math-102-03-13', '$2y$10$B5e8MqyKBljoiUoxbssQwOT8sFwj.eVHy9onJYjwFNHOQ3AkH/KF2', 'student', 65, NULL, '2026-02-16 10:11:03'),
(486, 'Olivia Salazar', 'math-102-04-13', 'math-102-04-13', '$2y$10$yW4Sd3ZSLMrMPeRpAXkKIextSdKSsXUcL3G895ow2D05rvfHcpOLC', 'student', 66, NULL, '2026-02-16 10:11:03'),
(487, 'Olivia Garcia', 'eng-103-05-13', 'eng-103-05-13', '$2y$10$azM/IxarBC41mfY0ZyyDmurSn9IFHradf3NL1bKa4NcuUrygr8zgW', 'student', 67, NULL, '2026-02-16 10:11:03'),
(488, 'Sophia Torres', 'fil-005-01-14', 'fil-005-01-14', '$2y$10$Y4sPrqMt8VDucspTBT8bXud0rsOntzGX8StaapdjjEoXwYD.fIN22', 'student', 68, NULL, '2026-02-16 10:11:03'),
(489, 'Isabella Morales', 'sci-101-02-14', 'sci-101-02-14', '$2y$10$Qa6v4Gg/OgJxq5NtIwI.PuJK0l7NJjXEzDhBkiKNjqsZm96b2BcqW', 'student', 69, NULL, '2026-02-16 10:11:03'),
(490, 'Benjamin Reyes', 'math-102-03-14', 'math-102-03-14', '$2y$10$dVwGLXn6V1bp9ZpD5FDaOOYeJDvskiFLUkAdA0HCXKfBMeA0ZSQn2', 'student', 70, NULL, '2026-02-16 10:11:03'),
(491, 'Alexander Gonzales', 'math-102-04-14', 'math-102-04-14', '$2y$10$/0Slb8UzFm0lkWQ4gdyUPutX9N3wF5PcyqFJxWlt7dPXZyHact91u', 'student', 71, NULL, '2026-02-16 10:11:03'),
(492, 'Noah Rivera', 'eng-103-05-14', 'eng-103-05-14', '$2y$10$WwgHxyg/zTi6dbHEN2skXO2UQhvo50ezzAOLNfyb.Lss.omQC6d.u', 'student', 72, NULL, '2026-02-16 10:11:03'),
(493, 'Amelia Mendoza', 'fil-005-01-15', 'fil-005-01-15', '$2y$10$ijztoYQPoVBPRJL/lqb2requHMjVCIRD1W4nrwbY2bSflKLYtQKUG', 'student', 73, NULL, '2026-02-16 10:11:04'),
(494, 'Isabella Ramos', 'sci-101-02-15', 'sci-101-02-15', '$2y$10$oLxjrMsZHnSuky9UP3BaOOcTtlL0MJsDq9ZgoMKdGvdyYn3b6Yg7S', 'student', 74, NULL, '2026-02-16 10:11:04'),
(495, 'Amelia Reyes', 'math-102-03-15', 'math-102-03-15', '$2y$10$uO7xXyoz0viXwuCkycbusuh4RFNJ17aCFucDZRvVDx1Xiec.BbuQC', 'student', 75, NULL, '2026-02-16 10:11:04'),
(496, 'Alexander Ramos', 'math-102-04-15', 'math-102-04-15', '$2y$10$EV1BCEH9lgT4A4PjpnmIke1fFy2.iG2sEsW38ICsJtVqISc1wgBNq', 'student', 76, NULL, '2026-02-16 10:11:04'),
(497, 'Amelia Garcia', 'eng-103-05-15', 'eng-103-05-15', '$2y$10$EjCGGHx9t.sV.kqqRQZ7z.3kT3S0172gHn9PeAs57RDFZm7W1eLny', 'student', 77, NULL, '2026-02-16 10:11:04'),
(888, 'JAPHET DOROIN', '02-2324-12345', '02-2324-12345', '$2y$10$y64ZKqo1Esws8V.f4LZpD.Q2lX3SDt2kHG6vEPB8983BMZp/yWqK.', 'student', 78, '2026-02-16 19:30:21', '2026-02-16 10:14:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`date`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_activities`
--
ALTER TABLE `class_activities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_activity` (`class_id`,`activity_date`);

--
-- Indexes for table `class_students`
--
ALTER TABLE `class_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `final_grades`
--
ALTER TABLE `final_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_final_grade` (`student_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`quarter`,`grade_type`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `class_activities`
--
ALTER TABLE `class_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `class_students`
--
ALTER TABLE `class_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `final_grades`
--
ALTER TABLE `final_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2862;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `class_students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_activities`
--
ALTER TABLE `class_activities`
  ADD CONSTRAINT `class_activities_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_students`
--
ALTER TABLE `class_students`
  ADD CONSTRAINT `class_students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `final_grades`
--
ALTER TABLE `final_grades`
  ADD CONSTRAINT `final_grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `class_students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `class_students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
