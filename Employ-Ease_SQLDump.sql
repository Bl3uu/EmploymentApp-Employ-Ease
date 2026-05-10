-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 10, 2026 at 11:56 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recruitment_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `user_id` int NOT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `ai_score` int DEFAULT '0',
  `ai_summary` text,
  `status` enum('Applied','Screened','Exam Assigned','Exam Completed','Passed','Failed','Interviewing','Offered','Rejected','Archived') DEFAULT 'Applied',
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `recruiter_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `user_id`, `resume_path`, `ai_score`, `ai_summary`, `status`, `applied_at`, `recruiter_notes`) VALUES
(20, 21, 7, 'RESUME_78afabeba5353f82.pdf', 45, 'The candidate\'s profile is more hardware and general-purpose software oriented rather than focused on backend API development. While the candidate possesses foundational knowledge in PHP and MySQL, they lack explicit experience with RESTful architecture, API design patterns, or modern backend frameworks (e.g., Express, Django, FastAPI) required for the job. The resume focuses heavily on embedded systems and robotics, making it a weak fit for a dedicated backend/API optimization role.', 'Rejected', '2026-05-08 12:15:45', NULL),
(21, 20, 9, 'RESUME_c556769e98a472fc.pdf', 4, 'The candidate is a Computer Engineering student with relevant academic exposure to MySQL, PHP, and web development. However, the resume lacks the professional experience necessary for managing large-scale, high-traffic production databases. The skill set is foundational rather than specialized in database administration, performance tuning, or database security at an enterprise scale.', 'Exam Completed', '2026-05-08 12:28:08', NULL),
(22, 20, 12, 'RESUME_05aa932f1e57f636.pdf', 4, 'The candidate shows potential in basic database management (SQL, SSMS, MySQL) and web development, which aligns with the technical requirements. However, the resume lacks evidence of experience with large-scale databases, performance tuning, query optimization, or enterprise-level security protocols. As a student, the candidate currently lacks the professional infrastructure experience necessary to manage high-traffic applications. The candidate is a strong fit for a junior-level role but requires significant mentorship to meet the \'large-scale\' and \'high-traffic\' demands of this specific position.', 'Exam Assigned', '2026-05-08 13:43:06', NULL),
(23, 16, 12, 'RESUME_37d31d520a89c896.pdf', 7, 'The candidate has a solid foundation in the required stack (PHP, MySQL, JavaScript) through academic projects, specifically web development with database integration. However, the resume lacks professional industry experience, and the profile is heavily weighted toward hardware/embedded systems rather than pure software/web engineering. To be a stronger candidate, the applicant should highlight more complex web projects, mention experience with modern JavaScript frameworks (like React, Vue, or Angular), and demonstrate familiarity with backend frameworks (like Laravel), which are currently absent from the skill set.', 'Rejected', '2026-05-08 14:28:43', NULL),
(24, 11, 12, 'RESUME_bf850840618964aa.pdf', 60, 'The candidate is a strong internship or junior-level applicant, but currently lacks the \'experience\' required for a professional Software Engineer role. While the resume demonstrates a solid academic foundation in programming, database management, and embedded systems, it does not show evidence of industry work experience or participation in large-scale professional software development life cycles. To better qualify for this position, the candidate should highlight more complex software architecture, testing methodologies, and collaborative version control workflows in their project descriptions.', 'Exam Assigned', '2026-05-08 15:31:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `application_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `application_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(101, 15, 21, 'Status Update', 'Changed status to Exam Assigned', NULL, '2026-05-08 12:29:30'),
(102, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 12:30:15'),
(103, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 12:30:32'),
(104, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 12:30:48'),
(110, 15, 22, 'Status Update', 'Changed application status to Exam Assigned', '112.201.195.47', '2026-05-08 13:48:34'),
(111, 12, 22, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 13:53:46'),
(112, 12, 22, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 13:53:54'),
(113, 12, 22, 'EXAM_VIOLATION', 'Type: Tab Switch | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 13:55:42'),
(114, 15, 22, 'Status Update', 'Changed status to Passed', NULL, '2026-05-08 13:57:16'),
(115, 15, 22, 'Status Update', 'Changed status to Interviewing', NULL, '2026-05-08 14:00:19'),
(116, 15, 22, 'Status Update', 'Changed status to Offered', NULL, '2026-05-08 14:00:20'),
(117, 15, 22, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 14:27:44'),
(118, 15, 23, 'Status Update', 'Changed application status to Exam Assigned', '112.201.195.47', '2026-05-08 14:29:35'),
(119, 12, 23, 'EXAM_VIOLATION', 'Type: Tab Switch | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 14:30:37'),
(120, 12, 23, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 14:30:37'),
(121, 12, 23, 'EXAM_VIOLATION', 'Type: Tab Switch | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 14:31:05'),
(122, 15, 23, 'Status Update', 'Changed status to Failed', NULL, '2026-05-08 14:35:20'),
(123, 15, 23, 'Status Update', 'Changed application status to Exam Assigned', '112.201.195.47', '2026-05-08 14:35:32'),
(124, 15, 23, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 14:35:44'),
(125, 15, 20, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 15:10:54'),
(126, 15, 20, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 15:12:19'),
(127, 15, 20, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 15:12:36'),
(128, 15, 20, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 15:13:04'),
(129, 15, 24, 'Status Update', 'Changed application status to Exam Assigned', '112.201.195.47', '2026-05-08 15:31:25'),
(130, 12, 24, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '103.60.170.45', '2026-05-08 15:32:24'),
(131, 15, 24, 'Status Update', 'Changed status to Failed', NULL, '2026-05-08 15:34:21'),
(132, 15, 22, 'Status Update', 'Changed status to Applied', NULL, '2026-05-08 15:46:59'),
(133, 15, 22, 'Status Update', 'Changed application status to Exam Assigned', '49.144.6.2', '2026-05-08 15:49:36'),
(134, 15, 22, 'Status Update', 'Changed status to Failed', NULL, '2026-05-08 16:05:42'),
(135, 15, 22, 'Status Update', 'Changed application status to Exam Assigned', '49.144.6.2', '2026-05-08 16:05:45'),
(136, 15, 21, 'Status Update', 'Changed status to Failed', NULL, '2026-05-08 16:05:48'),
(137, 15, 21, 'Status Update', 'Changed status to Applied', NULL, '2026-05-08 16:05:49'),
(138, 15, 21, 'Status Update', 'Changed status to Exam Assigned', NULL, '2026-05-08 16:05:55'),
(139, 15, 21, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 16:05:58'),
(140, 15, 24, 'Status Update', 'Changed application status to Exam Assigned', '49.144.6.2', '2026-05-08 18:33:51'),
(141, 15, 21, 'Status Update', 'Changed status to Applied', NULL, '2026-05-08 18:35:24'),
(142, 15, 21, 'Status Update', 'Changed status to Exam Assigned', NULL, '2026-05-08 18:35:25'),
(143, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 18:38:12'),
(144, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 18:38:19'),
(145, 9, 21, 'EXAM_VIOLATION', 'Type: Tab Switch | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 18:39:16'),
(146, 15, 21, 'Status Update', 'Changed status to Passed', NULL, '2026-05-08 18:39:50'),
(147, 15, 21, 'Status Update', 'Changed status to Interviewing', NULL, '2026-05-08 18:40:21'),
(148, 15, 21, 'Status Update', 'Changed status to Offered', NULL, '2026-05-08 18:40:22'),
(149, 15, 21, 'Status Update', 'Changed status to Rejected', NULL, '2026-05-08 18:41:02'),
(150, 15, 21, 'Status Update', 'Changed status to Applied', NULL, '2026-05-08 18:41:03'),
(151, 15, 21, 'Status Update', 'Changed status to Exam Assigned', NULL, '2026-05-08 18:41:04'),
(152, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 18:41:46'),
(153, 9, 21, 'EXAM_VIOLATION', 'Type: Window Focus Lost | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 18:42:20'),
(154, 9, 21, 'EXAM_VIOLATION', 'Type: Tab Switch | Browser event detection (Tab/Window focus API).', '112.201.195.47', '2026-05-08 18:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration_min` int NOT NULL,
  `passing_mark` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `job_id`, `title`, `duration_min`, `passing_mark`) VALUES
(6, 11, 'Interview Test', 30, 60),
(7, 20, 'Database Administrator Technical Interview Exam', 60, 60),
(8, 16, 'Full-Stack Web Developer Interview Exam', 30, 60),
(9, 22, 'Intermediate Test', 20, 60),
(10, 21, 'CYBER SECURITY 101', 30, 70);

-- --------------------------------------------------------

--
-- Table structure for table `exam_answers`
--

CREATE TABLE `exam_answers` (
  `id` int NOT NULL,
  `application_id` int NOT NULL,
  `question_id` int NOT NULL,
  `selected_option` varchar(1) NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exam_answers`
--

INSERT INTO `exam_answers` (`id`, `application_id`, `question_id`, `selected_option`, `is_correct`, `created_at`) VALUES
(98, 21, 17, 'B', 1, '2026-05-08 18:43:33'),
(99, 21, 33, 'C', 0, '2026-05-08 18:43:33'),
(100, 21, 32, 'C', 1, '2026-05-08 18:43:33'),
(101, 21, 35, 'B', 0, '2026-05-08 18:43:33'),
(102, 21, 30, 'B', 1, '2026-05-08 18:43:33'),
(103, 21, 18, 'B', 1, '2026-05-08 18:43:33'),
(104, 21, 20, 'B', 0, '2026-05-08 18:43:33'),
(105, 21, 25, 'C', 1, '2026-05-08 18:43:33'),
(106, 21, 24, 'C', 1, '2026-05-08 18:43:33'),
(107, 21, 19, 'C', 1, '2026-05-08 18:43:33'),
(108, 21, 36, 'B', 1, '2026-05-08 18:43:33'),
(109, 21, 21, 'A', 0, '2026-05-08 18:43:33'),
(110, 21, 31, 'B', 1, '2026-05-08 18:43:33'),
(111, 21, 27, 'C', 1, '2026-05-08 18:43:33'),
(112, 21, 22, 'B', 1, '2026-05-08 18:43:33'),
(113, 21, 28, 'B', 1, '2026-05-08 18:43:33'),
(114, 21, 26, 'B', 0, '2026-05-08 18:43:33'),
(115, 21, 34, 'B', 1, '2026-05-08 18:43:33'),
(116, 21, 29, 'B', 1, '2026-05-08 18:43:33'),
(117, 21, 23, 'C', 0, '2026-05-08 18:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `exam_id` int NOT NULL,
  `score` int NOT NULL,
  `status` varchar(20) NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `user_id`, `exam_id`, `score`, `status`, `attempted_at`) VALUES
(19, 9, 7, 70, 'Passed', '2026-05-08 18:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int NOT NULL,
  `recruiter_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `type` enum('Full-time','Part-time','Contract','Remote','On-site','Hybrid') NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('Active','Closed','Draft') DEFAULT 'Active',
  `max_applicants` int DEFAULT '50',
  `is_archived` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `recruiter_id`, `title`, `company`, `location`, `type`, `description`, `category`, `status`, `max_applicants`, `is_archived`, `created_at`) VALUES
(11, 15, 'Software Engineer', 'MHI Power', 'Mandaluyong City', 'Full-time', 'We are looking for an experienced software engineer to assist us in our development team', 'General', 'Active', 50, 0, '2026-05-07 12:37:27'),
(12, 15, 'Assistant Software Engineer ', 'MHI Power', 'Mandaluyong City', 'Full-time', 'We are looking for an experienced software engineer to assist our senior developers in our team', 'General', 'Active', 50, 0, '2026-05-07 12:37:27'),
(13, 15, 'AI Integration Engineer', 'Nexus AI Solutions', 'Engineer	Remote (PH Based)', 'Full-time', 'Develop and maintain integrations for the Gemini API to handle real-time NLP tasks and automated candidate proctoring.', 'General', 'Active', 50, 0, '2026-05-08 11:59:37'),
(14, 15, 'Junior Game Developer', 'Pixel Forge Studios', 'Makati City, Philippines', 'Full-time', 'Assist in the development of 3D environments and gameplay mechanics using Unity. Experience with C# and 3D asset rigging is a plus.', 'General', 'Active', 50, 0, '2026-05-08 12:00:12'),
(15, 15, 'Computer Vision Specialist', 'Visionary Systems', 'Quezon City, PH', 'Full-time', 'Build real-time tracking systems using OpenCV and MediaPipe for a smart mirror hardware project. Focus on pose estimation and clothing overlays.', 'General', 'Active', 50, 0, '2026-05-08 12:00:40'),
(16, 15, 'Full-Stack Web Developer', 'Digital Bridge Co.', 'BGC, Taguig', 'Full-time', 'Maintain and scale web applications using PHP, MySQL, and modern JavaScript frameworks. Responsible for both frontend UI and backend logic.', 'General', 'Active', 50, 0, '2026-05-08 12:01:03'),
(17, 15, 'Gameplay Programmer', 'Horizon Interactive', 'Remote', 'Remote', 'Implement character movement and physics-based interactions in a 3D space. Must be comfortable with complex rigging and animation state machines.', 'General', 'Active', 50, 0, '2026-05-08 12:01:33'),
(18, 15, 'Software Quality Assurance', 'SecureTest Tech', 'Dasmariñas, Cavite', 'Full-time', 'Perform manual and automated testing for a recruitment framework. Ensure the AI proctoring system is bug-free across different browsers.', 'General', 'Active', 50, 0, '2026-05-08 12:01:47'),
(19, 15, 'Machine Learning Engineer', 'Neural Path Inc.', 'Pasig City, Philippines', 'Contract', 'Fine-tune models for automated resume screening and sentiment analysis to improve recruitment efficiency.', 'General', 'Active', 50, 0, '2026-05-08 12:02:10'),
(20, 15, 'Database Administrator', 'DataNexus Corp', 'Remote', 'Remote', 'Manage large-scale MySQL databases, ensuring data integrity, security, and optimized query performance for high-traffic web apps.', 'General', 'Active', 50, 0, '2026-05-08 12:02:27'),
(21, 15, 'Backend API Developer', 'Flow State Systems', 'Alabang, Muntinlupa', 'Full-time', 'Design and optimize RESTful APIs to connect mobile interfaces with central database systems for seamless data flow.', 'General', 'Active', 50, 0, '2026-05-08 12:02:45'),
(22, 15, 'Junior Devops', 'Cloudstream solutions', 'Cavite', 'Remote', 'We are looking for a Junior DevOps Engineer to help automate our deployment pipelines. The ideal candidate must have experience with Docker and GitHub Actions. Familiarity with AWS (EC2/S3) is required. You will be responsible for maintaining our CI/CD workflows and monitoring server health. Bonus points if you have experience with Terraform or Kubernetes. We value candidates who understand the balance between rapid deployment and system stability.', 'General', 'Draft', 10, 0, '2026-05-08 15:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(4, 12, 'Application status updated', 'Your application for Software Engineer is now \'Exam Assigned\'.', 1, '2026-05-07 14:51:47'),
(5, 9, 'Application status updated', 'Your application for Software Engineer is now \'Rejected\'.', 1, '2026-05-07 14:55:11'),
(6, 12, 'Exam Completed', 'Your exam for Software Engineer was flagged for review due to security events.', 1, '2026-05-08 02:08:16'),
(7, 9, 'Application status updated', 'Your application for Database Administrator is now \'Exam Assigned\'.', 1, '2026-05-08 12:29:30'),
(8, 9, 'Exam Completed', 'Your exam for Database Administrator was flagged for review due to security events.', 1, '2026-05-08 12:31:18'),
(9, 12, 'Application status updated', 'Your application for Software Engineer is now \'Failed\'.', 1, '2026-05-08 13:23:27'),
(10, 12, 'Exam Assigned', 'Your technical exam for Software Engineer is ready. Please visit your portal to begin.', 1, '2026-05-08 13:23:37'),
(11, 12, 'Application status updated', 'Your application for Software Engineer is now \'Rejected\'.', 1, '2026-05-08 13:24:38'),
(12, 12, 'Application status updated', 'Your application for Software Engineer is now \'Screened\'.', 1, '2026-05-08 13:25:08'),
(13, 12, 'Exam Assigned', 'Your technical exam for Database Administrator is ready. Please visit your portal to begin.', 1, '2026-05-08 13:48:34'),
(14, 12, 'Exam Completed', 'Your exam for Database Administrator has been completed with status Failed and score 40%.', 1, '2026-05-08 13:55:42'),
(15, 12, 'Application status updated', 'Your application for Database Administrator is now \'Passed\'.', 1, '2026-05-08 13:57:16'),
(16, 12, 'Application status updated', 'Your application for Database Administrator is now \'Interviewing\'.', 1, '2026-05-08 14:00:19'),
(17, 12, 'Application status updated', 'Your application for Database Administrator is now \'Offered\'.', 1, '2026-05-08 14:00:20'),
(18, 12, 'Application status updated', 'Your application for Database Administrator is now \'Rejected\'.', 1, '2026-05-08 14:27:44'),
(19, 12, 'Exam Assigned', 'Your technical exam for Full-Stack Web Developer is ready. Please visit your portal to begin.', 1, '2026-05-08 14:29:35'),
(20, 12, 'Exam Completed', 'Your exam for Full-Stack Web Developer has been completed with status Passed and score 70%.', 1, '2026-05-08 14:31:05'),
(21, 12, 'Application status updated', 'Your application for Full-Stack Web Developer is now \'Failed\'.', 1, '2026-05-08 14:35:20'),
(22, 12, 'Exam Assigned', 'Your technical exam for Full-Stack Web Developer is ready. Please visit your portal to begin.', 1, '2026-05-08 14:35:32'),
(23, 12, 'Application status updated', 'Your application for Full-Stack Web Developer is now \'Rejected\'.', 1, '2026-05-08 14:35:44'),
(24, 7, 'Application status updated', 'Your application for Backend API Developer is now \'Rejected\'.', 0, '2026-05-08 15:10:55'),
(25, 7, 'Application status updated', 'Your application for Backend API Developer is now \'Rejected\'.', 0, '2026-05-08 15:12:19'),
(26, 7, 'Application status updated', 'Your application for Backend API Developer is now \'Rejected\'.', 0, '2026-05-08 15:12:36'),
(27, 7, 'Application status updated', 'Your application for Backend API Developer is now \'Rejected\'.', 0, '2026-05-08 15:13:04'),
(28, 12, 'Exam Assigned', 'Your technical exam for Software Engineer is ready. Please visit your portal to begin.', 1, '2026-05-08 15:31:25'),
(29, 12, 'Exam Completed', 'Your exam for Software Engineer has been completed with status Failed and score 50%.', 1, '2026-05-08 15:33:05'),
(30, 12, 'Application status updated', 'Your application for Software Engineer is now \'Failed\'.', 1, '2026-05-08 15:34:21'),
(31, 12, 'Exam Assigned', 'Your technical exam for Database Administrator is ready. Please visit your portal to begin.', 1, '2026-05-08 15:49:36'),
(32, 12, 'Exam Completed', 'Your exam for Database Administrator has been completed with status Failed and score 30%.', 1, '2026-05-08 15:50:14'),
(33, 12, 'Application status updated', 'Your application for Database Administrator is now \'Failed\'.', 1, '2026-05-08 16:05:42'),
(34, 12, 'Exam Assigned', 'Your technical exam for Database Administrator is ready. Please visit your portal to begin.', 1, '2026-05-08 16:05:45'),
(35, 9, 'Application status updated', 'Your application for Database Administrator is now \'Failed\'.', 1, '2026-05-08 16:05:48'),
(36, 9, 'Application status updated', 'Your application for Database Administrator is now \'Exam Assigned\'.', 1, '2026-05-08 16:05:55'),
(37, 9, 'Application status updated', 'Your application for Database Administrator is now \'Rejected\'.', 1, '2026-05-08 16:05:58'),
(38, 12, 'Exam Assigned', 'Your technical exam for Software Engineer is ready. Please visit your portal to begin.', 1, '2026-05-08 18:33:51'),
(39, 9, 'Application status updated', 'Your application for Database Administrator is now \'Exam Assigned\'.', 1, '2026-05-08 18:35:25'),
(40, 9, 'Exam Completed', 'Your exam for Database Administrator has been completed with status Failed and score 45%.', 1, '2026-05-08 18:39:16'),
(41, 9, 'Application status updated', 'Your application for Database Administrator is now \'Passed\'.', 1, '2026-05-08 18:39:50'),
(42, 9, 'Application status updated', 'Your application for Database Administrator is now \'Interviewing\'.', 1, '2026-05-08 18:40:21'),
(43, 9, 'Application status updated', 'Your application for Database Administrator is now \'Offered\'.', 1, '2026-05-08 18:40:22'),
(44, 9, 'Application status updated', 'Your application for Database Administrator is now \'Rejected\'.', 1, '2026-05-08 18:41:02'),
(45, 9, 'Application status updated', 'Your application for Database Administrator is now \'Exam Assigned\'.', 1, '2026-05-08 18:41:04'),
(46, 9, 'Exam Completed', 'Your exam for Database Administrator has been completed with status Passed and score 70%.', 1, '2026-05-08 18:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int NOT NULL,
  `exam_id` int NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`) VALUES
(7, 6, 'Which architectural pattern is specifically designed to decompose a large, monolithic application into a set of small, loosely coupled, and independently deployable services?', 'Model-View-Controller (MVC)', 'Microservices', 'Layered (N-tier) Architecture', 'Client-Server', 'B'),
(8, 6, 'In the context of the Agile Manifesto, what is valued more than \"following a plan\"?', 'Comprehensive documentation', 'Contract negotiation', 'Responding to change', 'Responding to change', 'C'),
(9, 6, 'Which of the following best describes \"Refactoring\" in software development?', 'Adding new functionality to an existing codebase.', 'Restructuring existing code without changing its external behavior.', 'Translating code from one programming language to another.', 'The process of finding and fixing bugs in a production environment.', 'B'),
(10, 6, 'What is the primary purpose of a \"Load Balancer\" in a distributed system?', 'To encrypt all incoming data from the client.', 'To store session data so the server remains stateless.', 'To distribute incoming network traffic across multiple servers.', 'To act as a primary database for high-speed read operations.', 'C'),
(11, 6, 'In Object-Oriented Programming, which principle allows a subclass to provide a specific implementation of a method that is already defined in its superclass?', 'Encapsulation', 'Abstraction', 'Polymorphism (Method Overriding)', 'Composition', 'C'),
(12, 6, 'What does the \"S\" in SOLID principles stand for?', 'System Integration Principle', 'Single Responsibility Principle', 'Software Scalability Principle', 'Sequential Execution Principle', 'B'),
(13, 6, 'Which version control command is used to combine changes from one branch into another?', 'git commit', 'git checkout', 'git merge', 'git push', 'C'),
(14, 6, 'Which data structure operates on a \"Last-In, First-Out\" (LIFO) basis?', 'Queue', 'Linked List', 'Stack', 'Heap', 'C'),
(15, 6, 'What is the main goal of Continuous Integration (CI)?', 'To manually deploy code to production once a month.', 'To automate the merging of code changes into a shared repository frequently.', 'To replace the need for unit testing.', 'To manage the hardware specifications of the server.', 'B'),
(16, 6, 'Which of the following is a \"Black Box\" testing technique?', 'Unit Testing (testing individual functions)', 'Boundary Value Analysis', 'Code Coverage Analysis', 'Data Flow Testing', 'B'),
(17, 7, 'Which SQL command is used to remove all records from a table without logging the individual row deletions?', 'DELETE', 'TRUNCATE', 'DROP', 'REMOVE', 'B'),
(18, 7, 'What is the primary purpose of a \"Checkpoint\" in a database management system?', 'To lock the database for maintenance', 'To reduce the time required for recovery after a crash by flushing dirty pages to disk', 'To verify the user\'s login credentials', 'To create a temporary backup of the schema', 'B'),
(19, 7, 'In a relational database, what does the \"A\" in ACID stand for?', 'Accuracy', 'Availability', 'Atomicity', 'Authentication', 'C'),
(20, 7, 'Which of the following is a benefit of using an \"Index\" on a table?', 'It decreases the storage space required', 'It makes INSERT operations faster', 'It improves the speed of data retrieval operations', 'It automatically encrypts the data', 'C'),
(21, 7, 'Which RAID level provides \"Mirroring\" but no parity or striping?', 'RAID 0', 'RAID 1', 'RAID 5', 'RAID 10', 'B'),
(22, 7, 'What is \"Database Normalization\" primarily used for?', 'Increasing data redundancy', 'Minimizing redundancy and dependency by organizing fields and table relationships', 'Converting a database to a flat-file system', 'Increasing the size of the database for better performance', 'B'),
(23, 7, 'Which type of backup includes only the data that has changed since the last Full backup?', 'Incremental Backup', 'Differential Backup', 'Transaction Log Backup', 'Mirror Backup', 'B'),
(24, 7, 'What is a \"Deadlock\" in a database context?', 'A table that has no primary key', 'A server that has lost its network connection', 'A situation where two tasks wait for each other to release locks, stopping both', 'The process of deleting old records', 'C'),
(25, 7, 'Which of the following constraints ensures that a column cannot have a NULL value?', 'UNIQUE', 'PRIMARY KEY', 'NOT NULL', 'CHECK', 'C'),
(26, 7, 'In SQL, which join returns all records when there is a match in either the left or right table?', 'INNER JOIN', 'LEFT JOIN', 'FULL OUTER JOIN', 'CROSS JOIN', 'C'),
(27, 7, 'What is the default port number for a standard MySQL installation?', '1433', '5432', '3306', '1521', 'C'),
(28, 7, 'Which of the following is used to undo changes made by a transaction that has not yet been committed?', 'COMMIT', 'ROLLBACK', 'SAVEPOINT', 'GRANT', 'B'),
(29, 7, 'What is the purpose of the \"EXPLAIN\" statement in SQL?', 'To provide a text-to-speech description of the table', 'To show the execution plan used by the database engine for a query', 'To rename a table', 'To list all users with access to the database', 'B'),
(30, 7, 'Which command is used to give a user specific permissions to a database?', 'ALLOW', 'GRANT', 'REVOKE', 'ASSIGN', 'B'),
(31, 7, 'What is \"Vertical Scaling\" (Scaling Up) in database administration?', 'Adding more servers to a cluster', 'Adding more power (CPU, RAM) to an existing server', 'Sharding a database into smaller pieces', 'Moving data from a hard drive to a cloud storage', 'B'),
(32, 7, 'Which of the following is a \"NoSQL\" database?', 'PostgreSQL', 'Oracle DB', 'MongoDB', 'Microsoft SQL Server', 'C'),
(33, 7, 'A \"Primary Key\" must be:', 'Unique and Not Null', 'Unique but can be Null', 'Any column in the table', 'Shared between two different tables', 'A'),
(34, 7, 'What does \"DBCC\" stand for in SQL Server?', 'Database Central Command', 'Database Console Commands', 'Data Backup Consistency Check', 'Data Binary Control Code', 'B'),
(35, 7, 'Data Binary Control Code', 'Read Uncommitted', 'Read Committed', 'Repeatable Read', 'Serializable', 'D'),
(36, 7, 'What is a \"Stored Procedure\"?', 'A document explaining how to use the database', 'A prepared SQL code that you can save and reuse over and over again', 'A backup file stored on a remote server', 'A method for physical hardware cooling', 'B'),
(37, 8, 'In the context of the MERN/MEAN stack, what is the primary role of \"Middleware\" in an Express.js application?', 'To serve as the primary database for the application', 'To execute code, make changes to the request/response objects, and end the request-response cycle', 'To compile CSS into browser-compatible JavaScript', 'To manage the hardware resources of the server', 'B'),
(38, 8, 'Which CSS property is used to change the stack order of elements that overlap?', 'float', 'position', 'z-index', 'overflow', 'C'),
(39, 8, 'What is the difference between let and var in JavaScript?', 'var is block-scoped, while let is function-scoped', 'let is block-scoped, while var is function-scoped (or globally scoped)', 'let can be redeclared in the same scope, while var cannot', 'There is no difference; they are interchangeable in modern JS', 'B'),
(40, 8, 'What does the \"box-sizing: border-box;\" property do in CSS?', 'It removes the border from the element', 'It includes padding and border in the element\'s total width and height', 'It forces the element to be a perfect square', 'It hides the content that overflows the border', 'B'),
(41, 8, 'In a RESTful API, which HTTP method is typically used to update an existing resource?', 'GET', 'POST', 'PUT / PATCH', 'DELETE', 'C'),
(42, 8, 'What is the purpose of the \"alt\" attribute in an HTML <img> tag?', 'To provide a link to an alternative image', 'To provide text descriptions for accessibility and if the image fails to load', 'To set the alignment of the image on the page', 'To apply an alternative CSS filter to the image', 'B'),
(43, 8, 'Which of the following is a way to prevent Cross-Site Scripting (XSS) attacks?', 'Using innerHTML for all user-generated content', 'Sanitizing and escaping all user input before rendering it on the page', 'Storing passwords in plain text for faster verification', 'Disabling HTTPS on the server', 'B'),
(44, 8, 'What is \"Hoisting\" in JavaScript?', 'Moving an element to the top of the DOM tree', 'The behavior of moving declarations to the top of their scope during the compilation phase', 'A method for increasing the memory limit of a script', 'The process of uploading a script to a server', 'B'),
(45, 8, 'In React, what is the primary purpose of \"Hooks\" (like useState)?', 'To link the application to an external database', 'To allow functional components to use state and other React features', 'To style components using inline CSS', 'To optimize the performance of the hardware GPU', 'B'),
(46, 8, 'What does a \"403 Forbidden\" HTTP status code indicate?', 'The server cannot find the requested resource', 'The server understands the request but refuses to authorize it', 'The server is currently down for maintenance', 'The request was successful, but there is no content to return', 'B'),
(47, 9, 'HTML Stands For?', 'A', 'B', 'C', 'D', 'C'),
(48, 10, 'DHCP STANDS FOR?', 'A', 'B', 'C', 'D', 'C');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'Recruiter'),
(2, 'Candidate'),
(3, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`) VALUES
(49, '3D Modelling'),
(45, 'Adobe Illustrator'),
(44, 'Adobe Photoshop'),
(40, 'Agile'),
(15, 'Angular'),
(37, 'Arduino'),
(20, 'ASP.NET Core'),
(31, 'AWS'),
(32, 'Azure'),
(73, 'Bilingual Communication'),
(66, 'Business Development'),
(2, 'C#'),
(3, 'C++'),
(33, 'CI/CD'),
(43, 'Clean Architecture'),
(74, 'Conflict Resolution'),
(53, 'Content Strategy'),
(63, 'Copywriting'),
(54, 'CRM'),
(71, 'Customer Success'),
(69, 'Data Analysis'),
(22, 'Django'),
(29, 'Docker'),
(36, 'Embedded Systems'),
(75, 'Emotional Intelligence'),
(19, 'Express.js'),
(46, 'Figma'),
(56, 'Financial Accounting'),
(27, 'Firebase'),
(39, 'Git'),
(10, 'Go'),
(52, 'Google Analytics'),
(35, 'GraphQL'),
(67, 'Human Resources Management'),
(4, 'Java'),
(6, 'JavaScript'),
(12, 'Kotlin'),
(30, 'Kubernetes'),
(21, 'Laravel'),
(41, 'Linux'),
(38, 'Machine Learning'),
(65, 'Market Research'),
(76, 'Marketing'),
(59, 'Microsoft Excel'),
(25, 'MongoDB'),
(17, 'Next.js'),
(18, 'Node.js'),
(8, 'PHP'),
(24, 'PostgreSQL'),
(57, 'Power BI'),
(28, 'Prisma ORM'),
(55, 'Project Management'),
(64, 'Public Relations'),
(72, 'Public Speaking'),
(5, 'Python'),
(60, 'QuickBooks'),
(14, 'React.js'),
(26, 'Redis'),
(34, 'RESTful API'),
(13, 'Ruby'),
(9, 'Rust'),
(61, 'SAP'),
(50, 'SEO'),
(51, 'Social Media Marketing'),
(23, 'Spring Boot'),
(1, 'SQL'),
(68, 'Strategic Planning'),
(11, 'Swift'),
(58, 'Tableau'),
(62, 'Technical Writing'),
(77, 'Trading'),
(7, 'TypeScript'),
(47, 'UI/UX Design'),
(42, 'Unit Testing'),
(70, 'User Research'),
(48, 'Video Editing'),
(16, 'Vue.js');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int DEFAULT NULL,
  `anchor_photo_path` varchar(255) DEFAULT NULL,
  `bio` text,
  `two_fa_code` varchar(6) DEFAULT NULL,
  `two_fa_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `two_fa_secret` varchar(255) DEFAULT NULL,
  `two_fa_enabled` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone_number`, `password`, `role_id`, `anchor_photo_path`, `bio`, `two_fa_code`, `two_fa_expires_at`, `created_at`, `two_fa_secret`, `two_fa_enabled`) VALUES
(7, 'Leeam', 'Ramos', 'leeamramos@gmail.com', '0945 584 5126', '$2y$10$d118/FwbtkpIPfHW0ZcXA.5rhPJcaMuV5FhiNvuxFyn6Wb/U8WVIK', 2, NULL, NULL, '892140', '2026-05-06 14:18:35', '2026-05-06 13:50:06', 'IUXF3FZ7M5PXC5TU', 1),
(8, 'Dustin', 'Cabrera', 'dustincabrera369@gmail.com', '+639457459378', '$2y$10$H0/ly49nta1VBVxctqlc3ucuyQqRDzSOttB5uf4kR0A1s6qoNSxFu', 3, NULL, 'Hello and Hi', '107351', '2026-05-06 14:26:14', '2026-03-03 23:14:50', 'EBJXK2MWICD67GON', 1),
(9, 'Rafael Luis', 'Relorcaa', 'rrf0792@dlsud.edu.ph', '+63 926 053 4964', '$2y$10$3DEK7VTeQfV1Dv/N9voaXuPxhxKW5d.lY5TGlYHnnro0saQeX4NCi', 2, NULL, 'I love coding', NULL, NULL, '2026-05-07 08:32:15', 'E6YTOJSNXYZCIEZE', 1),
(11, 'Rylle', 'Dumangon', 'dumangonrylle123@gmail.com', '0995 582 9757', '$2y$10$ArUR/HbwJlEdqLyj0bjjW.QaLWlSwIXnBBjPa465Ynv63L6PSMnca', 1, NULL, NULL, NULL, NULL, '2026-05-07 10:15:21', 'TXWXIHKRXQE4B7YL', 1),
(12, 'Irys', 'Dumangon', 'dumangonrylle120803@gmail.com', '09276503838', '$2y$10$juJKWTxQkEi3fGvCexSdo.CvbJw3rGkhrMpViQNOsd9jAjFwOkZf6', 2, NULL, 'Computer Engineering Graduate', NULL, NULL, '2026-05-07 11:14:51', '2UMFHVZHTXPOFFOS', 1),
(13, 'Koko', 'Dumangon', 'dumangonrylle@gmail.com', '09271856871', '$2y$10$JqnEBtmbPQIwQzvpQlKh3ueok8FurEVtRRyeqOsgHAe94NFIPV8aO', 2, NULL, NULL, NULL, NULL, '2026-05-07 11:42:39', 'WHHMMDGAQLBRUYOP', 0),
(14, 'Dustin', 'Cabrera', 'dustincabrera36@gmail.com', '0935 303 5693', '$2y$10$Z/BnHGBWiVrweGXBrQZcbunbF1NVeHgoOgf0eXJEV27ByHdfC2iEq', 1, NULL, 'Hi and Hello', NULL, NULL, '2026-05-07 11:45:12', '4XUXNFYVWXATE753', 1),
(15, 'Rafael Luis ', 'Relorcasa', 'rrfemployer@dlsud.edu.ph', '+63 0922 095 1882', '$2y$10$prLyp8s7kpKa1jFRe9I/XOsXd9gEPEqHuUq8FXnObe23ngDYX6VDS', 1, NULL, NULL, NULL, NULL, '2026-05-07 12:35:42', 'HRTZNTAL3U5JLZRC', 1),
(16, 'Test', 'Admin', 'rrfadmin@dlsud.edu.ph', '+63 0941 877 9532', '$2y$10$PwMvG.CktgBDp5ZhGGL8.uDHTAO0qf8cJqUQ9Lji9A4uM/tTuitB2', 3, NULL, NULL, NULL, NULL, '2026-05-08 12:36:50', '6YHZGJTJJSQHXCSF', 1),
(17, 'Rafael Luis ', 'Relorcasa', 'relorapplicant@gmail.com', '+63 0924 976 6751', '$2y$10$57lx03A.DhEXZhg6ghg70u5qfMN4wjO.uFtYNCBg4rFwjsjreaI0W', 2, NULL, NULL, NULL, NULL, '2026-05-08 14:48:51', 'YXKZFQKVPR2STZHI', 1),
(18, 'Rafael Luis F', 'Relorcasa', 'relor@gmail.com', '+63 0982 765 5092', '$2y$10$HW0uEg3bC2L9RKTkewo89eL.sppM8QI2Pe/hO11eHXbbiipEoGN3e', 2, NULL, NULL, NULL, NULL, '2026-05-08 14:57:46', NULL, 0),
(19, 'Rafael ', 'Relorcasa', 'relor@dlsud.edu.ph', '+63 0965 897 0952', '$2y$10$.UMlG.b2UeRibe.6o/30ve6/wNC7FZCNjhNFwNV9NYsSSEEriBsIu', 2, NULL, NULL, NULL, NULL, '2026-05-08 15:02:59', 'GUWTQXP254QDFKI5', 1),
(20, 'Irelia', 'Akali', 'patrickmjose22@gmail.com', '911', '$2y$10$NKm3CPK5Xr02slfygSF//uOP3i9NtSMQ2Oylfj.RJX5kbB4H31J6W', 2, NULL, NULL, NULL, NULL, '2026-05-08 17:33:37', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `user_id` int NOT NULL,
  `skill_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_skills`
--

INSERT INTO `user_skills` (`user_id`, `skill_id`) VALUES
(9, 2),
(14, 2),
(9, 6),
(14, 32),
(12, 57),
(12, 58),
(12, 69);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_application` (`application_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created_at` (`created_at`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id` (`job_id`);

--
-- Indexes for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recruiter` (`recruiter_id`),
  ADD KEY `idx_jobs_search` (`title`,`company`),
  ADD KEY `idx_jobs_filter` (`type`,`category`,`location`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`user_id`,`skill_id`),
  ADD KEY `idx_user_skill_lookup` (`skill_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `exam_answers`
--
ALTER TABLE `exam_answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD CONSTRAINT `fk_ans_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ans_q` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_recruiter` FOREIGN KEY (`recruiter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `fk_user_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_skills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
