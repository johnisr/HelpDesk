-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2017 at 07:27 AM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `help desk`
--

-- --------------------------------------------------------

--
-- Table structure for table `hdaccounts`
--

CREATE TABLE `hdaccounts` (
  `hda_id` int(11) NOT NULL,
  `Username` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hdaccounts`
--

INSERT INTO `hdaccounts` (`hda_id`, `Username`) VALUES
(5, 'HDUserGriffin'),
(4, 'HDUserKaitlyn'),
(3, 'HDUserMorty'),
(2, 'HDUserWilliam'),
(1, 'HDUserZachary');

-- --------------------------------------------------------

--
-- Table structure for table `hdaonline`
--

CREATE TABLE `hdaonline` (
  `hda_id` int(11) NOT NULL,
  `online` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hda_message`
--

CREATE TABLE `hda_message` (
  `mId` int(11) NOT NULL,
  `ps_id` int(11) NOT NULL,
  `Sender` varchar(30) NOT NULL,
  `Content` varchar(150) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hda_session`
--

CREATE TABLE `hda_session` (
  `ps_id` int(11) NOT NULL,
  `hdAccount1` varchar(16) NOT NULL,
  `hdAccount2` varchar(16) NOT NULL,
  `hda1_need_update` tinyint(1) NOT NULL,
  `hda2_need_update` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hdpassword`
--

CREATE TABLE `hdpassword` (
  `hda_id` int(11) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hdpassword`
--

INSERT INTO `hdpassword` (`hda_id`, `Password`) VALUES
(1, '$2y$10$xQOLG9Tvn8K42qdIvSkYJOVstFuRFTYVDzKCY2I3jE0V.Z0hlPxVO'),
(2, '$2y$10$5vt5Hu.rrQeyHbftO8oE7u/wQVdWMVER04rXIX2cSPAVvaK1t4kz6'),
(3, '$2y$10$PJ7rr.H3L/teN8RMm/JuS.3J92qYODC.mSTUhHJqNjUpqpcnILS9q'),
(4, '$2y$10$vVnmdVPWO58DThNJ1mQ3ueOkmg7RFkeAxXfmvPcYgKVT3LJvc53Oy'),
(5, '$2y$10$z9NHFrhpIxXxk2aqdP8OeePA5BRLzLnSNc/vUBT1oQ4n7HPRtDzQ2');

-- --------------------------------------------------------

--
-- Table structure for table `loginattempts`
--

CREATE TABLE `loginattempts` (
  `IP` varchar(32) NOT NULL,
  `FailedAttempts` int(11) NOT NULL,
  `LastLogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `mID` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `Sender` varchar(30) NOT NULL,
  `Content` varchar(150) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `ticket_id` int(11) NOT NULL,
  `session_id` varchar(40) NOT NULL,
  `anon_name` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `ticket_id` int(11) NOT NULL,
  `HDuser` varchar(16) NOT NULL,
  `anon_session_id` varchar(40) NOT NULL,
  `anon_name` varchar(16) NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hdaccounts`
--
ALTER TABLE `hdaccounts`
  ADD PRIMARY KEY (`hda_id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `hdaonline`
--
ALTER TABLE `hdaonline`
  ADD PRIMARY KEY (`hda_id`);

--
-- Indexes for table `hda_message`
--
ALTER TABLE `hda_message`
  ADD PRIMARY KEY (`mId`),
  ADD KEY `ps_id FK` (`ps_id`);

--
-- Indexes for table `hda_session`
--
ALTER TABLE `hda_session`
  ADD PRIMARY KEY (`ps_id`),
  ADD UNIQUE KEY `hda_session` (`hdAccount1`,`hdAccount2`),
  ADD KEY `hdAccount2 FK` (`hdAccount2`);

--
-- Indexes for table `hdpassword`
--
ALTER TABLE `hdpassword`
  ADD PRIMARY KEY (`hda_id`);

--
-- Indexes for table `loginattempts`
--
ALTER TABLE `loginattempts`
  ADD PRIMARY KEY (`IP`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`mID`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`ticket_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `HDuser` (`HDuser`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hdaccounts`
--
ALTER TABLE `hdaccounts`
  MODIFY `hda_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `hda_message`
--
ALTER TABLE `hda_message`
  MODIFY `mId` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hda_session`
--
ALTER TABLE `hda_session`
  MODIFY `ps_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `mID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `hdaonline`
--
ALTER TABLE `hdaonline`
  ADD CONSTRAINT `hda_id match` FOREIGN KEY (`hda_id`) REFERENCES `hdaccounts` (`hda_id`);

--
-- Constraints for table `hda_message`
--
ALTER TABLE `hda_message`
  ADD CONSTRAINT `ps_id FK` FOREIGN KEY (`ps_id`) REFERENCES `hda_session` (`ps_id`);

--
-- Constraints for table `hda_session`
--
ALTER TABLE `hda_session`
  ADD CONSTRAINT `hdAccount1 FK` FOREIGN KEY (`hdAccount1`) REFERENCES `hdaccounts` (`Username`),
  ADD CONSTRAINT `hdAccount2 FK` FOREIGN KEY (`hdAccount2`) REFERENCES `hdaccounts` (`Username`);

--
-- Constraints for table `hdpassword`
--
ALTER TABLE `hdpassword`
  ADD CONSTRAINT `hda_id_in_hdaccount` FOREIGN KEY (`hda_id`) REFERENCES `hdaccounts` (`hda_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `session_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `session` (`ticket_id`);

--
-- Constraints for table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `session_given_hdaccount` FOREIGN KEY (`HDuser`) REFERENCES `hdaccounts` (`Username`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
