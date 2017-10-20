CREATE TABLE `results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL DEFAULT '0',
  `builddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `submitter` varchar(255) NOT NULL DEFAULT '',
  `commitid` char(40) NOT NULL DEFAULT '',
  `identifier` char(40) NOT NULL DEFAULT '',
  `arch` varchar(64) NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `libc` varchar(32) NOT NULL DEFAULT '',
  `static` tinyint(1) NOT NULL default '0',
  `subarch` varchar(64) NOT NULL DEFAULT '',
  `duration` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `config_symbol` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `symbol_per_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL DEFAULT '0',
  `symbol_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
