CREATE DATABASE `espn` /*!40100 DEFAULT CHARACTER SET utf8 */;
CREATE TABLE  `espn`.`cronjob` (
  `cronjob_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` varchar(255) COLLATE utf8_bin NOT NULL,
  `name_class` varchar(255) COLLATE utf8_bin NOT NULL,
  `file` varchar(255) COLLATE utf8_bin NOT NULL,
  `schedule` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0 */2 * * *',
  `datetime_start` datetime DEFAULT NULL,
  `datetime_end` datetime DEFAULT NULL,
  `status_activity` enum('RUNNING','IDLE','ONHOLD') COLLATE utf8_bin DEFAULT 'IDLE',
  `status_run` enum('SUCCESS','FAIL') COLLATE utf8_bin DEFAULT NULL,
  `status` enum('A','I') COLLATE utf8_bin NOT NULL DEFAULT 'A',
  PRIMARY KEY (`cronjob_id`),
  KEY `idx_status` (`status`),
  KEY `idx_status_activity` (`status_activity`),
  KEY `idx_status_run` (`status_run`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE  `espn`.`event` (
  `eventId` int(10) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of the Event',
  `home_team_id` int(10) unsigned NOT NULL DEFAULT '0',
  `away_team_id` int(10) unsigned NOT NULL DEFAULT '0',
  `home_score` int(10) unsigned DEFAULT NULL,
  `away_score` int(10) unsigned DEFAULT NULL,
  `location` varchar(50) NOT NULL DEFAULT '',
  `week` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT 'Football match Status',
  PRIMARY KEY (`eventId`),
  KEY `eventDate` (`date`),
  KEY `eventLocation` (`location`),
  KEY `eventWeek` (`week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `espn`.`team` (
  `teamId` int(10) unsigned NOT NULL DEFAULT '0',
  `abbreviation` varchar(5) NOT NULL,
  `name` varchar(45) NOT NULL DEFAULT '',
  `school` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`teamId`),
  KEY `teamName` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `espn`.`cronjob` (`cronjob_id`, `name`, `description`, `name_class`, `file`, `schedule`, `status`) VALUES ('1', 'Schedule Job', 'Run schedule everyday', 'Schedule', 'schedule.php', '0 7 * * *', 'A');
INSERT INTO `espn`.`cronjob` (`cronjob_id`, `name`, `description`, `name_class`, `file`, `schedule`, `status`) VALUES ('2', 'Team Job', 'Load Teams ', 'Team', 'team.php', '0 4 * * *', 'A');