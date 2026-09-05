-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 05, 2026 at 09:13 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `criminal_intelligence_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
CREATE TABLE IF NOT EXISTS `persons` (
  `person_id` int NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `nickname` varchar(80) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `occupation` varchar(80) DEFAULT NULL,
  `photo_ref` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`person_id`, `full_name`, `nickname`, `date_of_birth`, `gender`, `occupation`, `photo_ref`) VALUES
(1, 'Ravi Parmar', NULL, '1983-02-08', 'M', 'Trader', 'PHOTO-0001'),
(2, 'Amit Trivedi', NULL, '1986-03-15', 'M', 'Technician', 'PHOTO-0002'),
(3, 'Karan Solanki', NULL, '1989-04-22', 'F', 'Contractor', 'PHOTO-0003'),
(4, 'Suresh Mehta', NULL, '1992-05-02', 'M', 'Student', 'PHOTO-0004'),
(5, 'Vivek Joshi', 'R5', '1995-06-09', 'M', 'Accountant', 'PHOTO-0005'),
(6, 'Nikhil Rana', NULL, '1998-07-16', 'F', 'Business Owner', 'PHOTO-0006'),
(7, 'Rahul Shah', NULL, '1981-08-23', 'M', 'Mechanic', 'PHOTO-0007'),
(8, 'Arjun Desai', NULL, '1984-09-03', 'M', 'Driver', 'PHOTO-0008'),
(9, 'Manish Pandya', NULL, '1987-10-10', 'F', 'Trader', 'PHOTO-0009'),
(10, 'Deepak Patel', 'R10', '1990-11-17', 'M', 'Technician', 'PHOTO-0010'),
(11, 'Vikas Parmar', NULL, '1993-12-24', 'M', 'Contractor', 'PHOTO-0011'),
(12, 'Rohit Trivedi', NULL, '1996-01-04', 'F', 'Student', 'PHOTO-0012'),
(13, 'Sameer Solanki', NULL, '1999-02-11', 'M', 'Accountant', 'PHOTO-0013'),
(14, 'Akash Mehta', NULL, '1982-03-18', 'M', 'Business Owner', 'PHOTO-0014'),
(15, 'Harsh Joshi', 'R15', '1985-04-25', 'F', 'Mechanic', 'PHOTO-0015'),
(16, 'Mihir Rana', NULL, '1988-05-05', 'M', 'Driver', 'PHOTO-0016'),
(17, 'Jay Shah', NULL, '1991-06-12', 'M', 'Trader', 'PHOTO-0017'),
(18, 'Dhruv Desai', NULL, '1994-07-19', 'F', 'Technician', 'PHOTO-0018'),
(19, 'Yash Pandya', NULL, '1997-08-26', 'M', 'Contractor', 'PHOTO-0019'),
(20, 'Parth Patel', NULL, '1980-09-06', 'M', 'Student', 'PHOTO-0020'),
(21, 'Nitin Parmar', NULL, '1983-10-13', 'F', 'Accountant', 'PHOTO-0021'),
(22, 'Ankit Trivedi', NULL, '1986-11-20', 'M', 'Business Owner', 'PHOTO-0022'),
(23, 'Raj Solanki', NULL, '1989-12-27', 'M', 'Mechanic', 'PHOTO-0023'),
(24, 'Sahil Mehta', NULL, '1992-01-07', 'F', 'Driver', 'PHOTO-0024'),
(25, 'Varun Joshi', 'R25', '1995-02-14', 'M', 'Trader', 'PHOTO-0025'),
(26, 'Neel Rana', NULL, '1998-03-21', 'M', 'Technician', 'PHOTO-0026'),
(27, 'Rakesh Shah', NULL, '1981-04-01', 'F', 'Contractor', 'PHOTO-0027'),
(28, 'Mohit Desai', NULL, '1984-05-08', 'M', 'Student', 'PHOTO-0028'),
(29, 'Pranav Pandya', NULL, '1987-06-15', 'M', 'Accountant', 'PHOTO-0029'),
(30, 'Tushar Patel', 'R30', '1990-07-22', 'F', 'Business Owner', 'PHOTO-0030'),
(31, 'Ishaan Parmar', NULL, '1993-08-02', 'M', 'Mechanic', 'PHOTO-0031'),
(32, 'Dev Trivedi', NULL, '1996-09-09', 'M', 'Driver', 'PHOTO-0032'),
(33, 'Aditya Solanki', NULL, '1999-10-16', 'F', 'Trader', 'PHOTO-0033'),
(34, 'Mayank Mehta', NULL, '1982-11-23', 'M', 'Technician', 'PHOTO-0034'),
(35, 'Piyush Joshi', 'R35', '1985-12-03', 'M', 'Contractor', 'PHOTO-0035'),
(36, 'Darshan Rana', NULL, '1988-01-10', 'F', 'Student', 'PHOTO-0036'),
(37, 'Meet Shah', NULL, '1991-02-17', 'M', 'Accountant', 'PHOTO-0037'),
(38, 'Himanshu Desai', NULL, '1994-03-24', 'M', 'Business Owner', 'PHOTO-0038'),
(39, 'Abhishek Pandya', NULL, '1997-04-04', 'F', 'Mechanic', 'PHOTO-0039'),
(40, 'Aarav Patel', NULL, '1980-05-11', 'M', 'Driver', 'PHOTO-0040'),
(41, 'Bhavin Parmar', NULL, '1983-06-18', 'M', 'Trader', 'PHOTO-0041'),
(42, 'Chirag Trivedi', NULL, '1986-07-25', 'F', 'Technician', 'PHOTO-0042'),
(43, 'Jatin Solanki', NULL, '1989-08-05', 'M', 'Contractor', 'PHOTO-0043'),
(44, 'Krish Mehta', NULL, '1992-09-12', 'M', 'Student', 'PHOTO-0044'),
(45, 'Lalit Joshi', 'R45', '1995-10-19', 'F', 'Accountant', 'PHOTO-0045'),
(46, 'Milan Rana', NULL, '1998-11-26', 'M', 'Business Owner', 'PHOTO-0046'),
(47, 'Naveen Shah', NULL, '1981-12-06', 'M', 'Mechanic', 'PHOTO-0047'),
(48, 'Om Desai', NULL, '1984-01-13', 'F', 'Driver', 'PHOTO-0048'),
(49, 'Pratik Pandya', NULL, '1987-02-20', 'M', 'Trader', 'PHOTO-0049'),
(50, 'Sachin Patel', 'R50', '1990-03-27', 'M', 'Technician', 'PHOTO-0050'),
(51, 'Rudra Desai', 'rudiboy', '2008-01-30', 'M', 'CE/ICT student', 'PHOTO-0051');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
