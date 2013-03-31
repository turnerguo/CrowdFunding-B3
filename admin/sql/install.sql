CREATE TABLE IF NOT EXISTS `#__crowdf_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment` varchar(1024) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_currencies` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `abbr` char(3) NOT NULL,
  `symbol` char(3) NOT NULL DEFAULT '',
  `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_crowdf_ccode` (`abbr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `latitude` varchar(64) NOT NULL,
  `longitude` varchar(64) NOT NULL,
  `country_code` char(2) NOT NULL,
  `timezone` varchar(40) NOT NULL,
  `published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_projects` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `alias` varchar(32) NOT NULL DEFAULT '',
  `short_desc` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(64) NOT NULL DEFAULT '',
  `image_square` varchar(64) NOT NULL DEFAULT '',
  `image_small` varchar(64) NOT NULL DEFAULT '',
  `location` int(10) unsigned NOT NULL DEFAULT '0',
  `goal` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `funded` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `funding_type` enum('FIXED','FLEXIBLE') NOT NULL DEFAULT 'FIXED',
  `funding_start` date NOT NULL DEFAULT '0000-00-00',
  `funding_end` date NOT NULL DEFAULT '0000-00-00',
  `funding_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pitch_video` varchar(255) NOT NULL DEFAULT '',
  `pitch_image` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `user_id` (`user_id`),
  KEY `alias` (`alias`),
  KEY `location` (`location`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_rewards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(500) NOT NULL,
  `amount` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `distributed` smallint(5) unsigned NOT NULL DEFAULT '0',
  `delivery` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Estimated delivery',
  `shipping` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `project_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `txn_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `txn_amount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `txn_currency` varchar(64) NOT NULL DEFAULT '',
  `txn_status` varchar(64) NOT NULL DEFAULT '',
  `txn_id` varchar(128) DEFAULT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `reward_id` int(10) unsigned NOT NULL DEFAULT '0',
  `investor_id` int(10) unsigned NOT NULL COMMENT 'The backer of the project.',
  `receiver_id` int(10) unsigned NOT NULL COMMENT 'The owner of the project.',
  `service_provider` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_updates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

