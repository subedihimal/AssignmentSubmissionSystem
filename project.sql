-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2024 at 04:00 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignment_id` varchar(30) NOT NULL,
  `subject_id` varchar(30) DEFAULT NULL,
  `assignment_name` varchar(30) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `pdf_filepath` varchar(335) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignment_id`, `subject_id`, `assignment_name`, `due_date`, `pdf_filepath`) VALUES
('CACS101-1', 'CACS101', 'Assignment - 1', '2023-09-30', 'D:/Programs/Xampp/htdocs/project_as/dashboard/storage/givenassignment/CACS101-1_ASSIGNMENT QUESTION.pdf'),
('CACS101-2', 'CACS101', 'Assignment - 2', '2023-10-10', 'D:/Programs/Xampp/htdocs/project_as/dashboard/storage/givenassignment/CACS101-2_ASSIGNMENT QUESTION.pdf'),
('CACS102-1', 'CACS102', 'Assignment - 1', '2023-09-28', 'D:/Programs/Xampp/htdocs/project_as/dashboard/storage/givenassignment/CACS102-1_ASSIGNMENT QUESTION.pdf'),
('CACS201-1', 'CACS201', 'Assignment - 1', '2023-09-27', 'D:/Programs/Xampp/htdocs/project_as/dashboard/storage/givenassignment/CACS201-1_ASSIGNMENT QUESTION.pdf'),
('CACS201-2', 'CACS201', 'Assignment - 2', '2023-10-12', 'D:/Programs/Xampp/htdocs/project_as/dashboard/storage/givenassignment/CACS201-2_ASSIGNMENT QUESTION.pdf'),
('CACS202-1', 'CACS202', 'Assignment - 1', '2023-10-11', 'D:/Programs/Xampp/htdocs/project_as/dashboard/storage/givenassignment/CACS202-1_ASSIGNMENT QUESTION.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `usertype` enum('admin','student') DEFAULT NULL,
  `semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`, `usertype`, `semester`) VALUES
('admin@dav', '21232f297a57a5a743894a0e4a801fc3', 'admin', 0),
('gita2@2019', '202cb962ac59075b964b07152d234b70', 'student', 2),
('niraj1@2020', '202cb962ac59075b964b07152d234b70', 'student', 1),
('nripesh2@2020', '202cb962ac59075b964b07152d234b70', 'student', 1),
('ram1@2019', '202cb962ac59075b964b07152d234b70', 'student', 2),
('sagar3@2020', '202cb962ac59075b964b07152d234b70', 'student', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` varchar(30) NOT NULL,
  `subject_name` varchar(30) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `semester`) VALUES
('CACS101', 'Numerical Method', 1),
('CACS102', 'English', 1),
('CACS201', 'Mathematics', 2),
('CACS202', 'C Programming', 2);

-- --------------------------------------------------------

--
-- Table structure for table `s_assignment`
--

CREATE TABLE `s_assignment` (
  `username` varchar(30) DEFAULT NULL,
  `s_file` varchar(225) DEFAULT NULL,
  `assignment_id` varchar(30) DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `s_assignment`
--

INSERT INTO `s_assignment` (`username`, `s_file`, `assignment_id`, `submission_date`, `status`) VALUES
('niraj1@2020', '../../storage/submittedassignment/niraj1@2020_CACS101-1.pdf', 'CACS101-1', '2023-08-15', 'Complete'),
('nripesh2@2020', '../../storage/submittedassignment/nripesh2@2020_CACS101-1.pdf', 'CACS101-1', '2023-08-15', 'Partial');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `s_assignment`
--
ALTER TABLE `s_assignment`
  ADD KEY `username` (`username`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `assignment_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`);

--
-- Constraints for table `s_assignment`
--
ALTER TABLE `s_assignment`
  ADD CONSTRAINT `s_assignment_ibfk_1` FOREIGN KEY (`username`) REFERENCES `login` (`username`),
  ADD CONSTRAINT `s_assignment_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
