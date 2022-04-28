SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `envios` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `envios`;

CREATE TABLE `parcel` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `tracking` varchar(13) DEFAULT NULL,
  `origin` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`origin`)),
  `destination` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`destination`)),
  `volumen` decimal(10,0) DEFAULT NULL,
  `weight` decimal(10,0) DEFAULT NULL,
  `amount` decimal(10,0) DEFAULT NULL,
  `value` decimal(10,0) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `parel_status` (
  `id` int(11) NOT NULL,
  `parcel` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `person` (
  `id` int(11) NOT NULL,
  `firstname` varchar(16) DEFAULT NULL,
  `secondname` varchar(16) DEFAULT NULL,
  `lastname` varchar(16) DEFAULT NULL,
  `secondlastname` varchar(16) DEFAULT NULL,
  `dni` varchar(32) DEFAULT NULL,
  `sex` int(11) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `country` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `municipality` int(11) DEFAULT NULL,
  `parish` int(11) DEFAULT NULL,
  `zipcode` varchar(8) DEFAULT NULL,
  `numberhouse` varchar(12) DEFAULT NULL,
  `street` varchar(120) DEFAULT NULL,
  `reference` varchar(126) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `description` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `token` (
  `id` int(11) NOT NULL,
  `user` int(11) DEFAULT NULL,
  `token` varchar(256) DEFAULT NULL,
  `generation_timestamp` timestamp NULL DEFAULT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `browser` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `user` varchar(32) NOT NULL,
  `password` varchar(128) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `email` varchar(64) NOT NULL,
  `person_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `questionOne` varchar(128) NOT NULL,
  `answerOne` varchar(128) NOT NULL,
  `questionTwo` varchar(128) NOT NULL,
  `answerTwo` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `parcel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`);

ALTER TABLE `parel_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parcel` (`parcel`),
  ADD KEY `fk_status` (`status`);

ALTER TABLE `person`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_token` (`user`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `person_id` (`person_id`),
  ADD UNIQUE KEY `user` (`user`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `person`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `parcel`
  ADD CONSTRAINT `parcel_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`);

ALTER TABLE `parel_status`
  ADD CONSTRAINT `fk_parcel` FOREIGN KEY (`parcel`) REFERENCES `parcel` (`id`),
  ADD CONSTRAINT `fk_status` FOREIGN KEY (`status`) REFERENCES `status` (`id`);

ALTER TABLE `token`
  ADD CONSTRAINT `fk_user_token` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user`
  ADD CONSTRAINT `fk_person` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`);