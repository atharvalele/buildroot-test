CREATE TABLE `results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL DEFAULT '0',
  `builddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `submitter` varchar(255) NOT NULL DEFAULT '',
  `commitid` char(40) NOT NULL DEFAULT '',
  `identifier` char(40) NOT NULL DEFAULT '',
  `arch` varchar(64) NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `results_config` (
       `id`         int(11) NOT NULL auto_increment,
       `resultid`   int(11) default NULL,
       `isset`	    tinyint(1) NOT NULL default '0',
       `name`       varchar(255) NOT NULL default '',
       `value`	    varchar(255) NOT NULL default '',
       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
