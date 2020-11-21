DROP DATABASE IF EXISTS `CloudGuides`;
CREATE DATABASE  IF NOT EXISTS `CloudGuides`;
USE `CloudGuides`;

--
-- Table structure for table `posts`
--
SET SQL_SAFE_UPDATES = 0;
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `providerId` tinyint NOT NULL,
  `serviceId` tinyint NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `submissionDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userId` int NOT NULL,
  `imageUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `subheading` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SET SQL_SAFE_UPDATES = 1;
--
-- Seed data for table `posts`
--

LOCK TABLES `posts` WRITE;
INSERT INTO `posts` VALUES 
    (1,1,1,'TitleTest','1321321','2020-11-03 22:51:31',1,'https://i.imgur.com/4ILisqH.jpg','SubheadingTest');
UNLOCK TABLES;

--
-- Table structure for table `providers`
--
SET SQL_SAFE_UPDATES = 0;
DROP TABLE IF EXISTS `providers`;
CREATE TABLE `providers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SET SQL_SAFE_UPDATES = 1;
--
-- Dumping data for table `providers`
--

LOCK TABLES `providers` WRITE;
INSERT INTO `providers` VALUES 
	(1,'Azure'),
    (2,'Amazon Web Services'),
    (3,'Google Cloud');
UNLOCK TABLES;

--
-- Table structure for table `services`
--
SET SQL_SAFE_UPDATES = 0;
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `providerId` int NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SET SQL_SAFE_UPDATES = 1;

--
-- Seed data for table `services`
--

LOCK TABLES `services` WRITE;
INSERT INTO `services` VALUES 
	(1,0,'App Service'),
    (2,1,'Lightsail');
UNLOCK TABLES;

--
-- Table structure for table `users`
--
SET SQL_SAFE_UPDATES = 0;
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin` tinyint NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SET SQL_SAFE_UPDATES = 1;

--
-- Seed data for table `users`
--

LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES 
	(1,1,'admin','email@domain.com','$2y$10$SjUA3LhGgOsuT4oCr01QZeCbEhD85ck8fXMxkeuNqeCH/YYSOrQKm','2020-09-06 15:12:33'),
    (2,0,'melvin','melvin@email.com','$2y$10$SjUA3LhGgOsuT4oCr01QZeCbEhD85ck8fXMxkeuNqeCH/YYSOrQKm','2020-09-08 16:37:11');
UNLOCK TABLES;