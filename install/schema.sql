CREATE TABLE `__TABLE_PREFIX__dsrating` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ds_id` int(11) unsigned NOT NULL default '0',
  `doc_id` int(11) unsigned NOT NULL default '0',
  `raters` int(6) unsigned NOT NULL default '0',
  `rating` int(3) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id` (`ds_id`,`doc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `__TABLE_PREFIX__dsrating_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `rating_id` int(11) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `is_active` int(1) unsigned NOT NULL default '0',
  `is_fake` int(1) unsigned NOT NULL default '0',
  `rating` int(10) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY (`id`),
  KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;