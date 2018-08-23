CREATE TABLE `gwaccounts` (
  `accid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'this key will be bound by charid in table gwchars',
  `userid` int(11) DEFAULT NULL,
  `accemail` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`accid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `gwchars` */

CREATE TABLE `gwchars` (
  `charid` int(11) NOT NULL AUTO_INCREMENT,
  `accid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `charname` varchar(19) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `profid` int(2) DEFAULT NULL,
  `profcolor` char(7) NOT NULL DEFAULT '#45b39d',
  PRIMARY KEY (`charid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `gwprofessions` */

CREATE TABLE `gwprofessions` (
  `profid` int(2) NOT NULL AUTO_INCREMENT,
  `profession` varchar(12) DEFAULT NULL,
  `profcolor` char(4) DEFAULT NULL,
  PRIMARY KEY (`profid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `gwstats` */

CREATE TABLE `gwstats` (
  `titlenameid` int(11) DEFAULT NULL,
  `stnameid` int(2) DEFAULT NULL,
  `titlepoints` int(11) DEFAULT NULL,
  `currentstrankname` varchar(37) DEFAULT NULL,
  `currentstrank` int(11) DEFAULT NULL,
  `percent` int(3) DEFAULT NULL,
  `gwamm` int(1) NOT NULL DEFAULT '0',
  `charid` int(11) NOT NULL DEFAULT '0',
  `accid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `gwsubtitles` */

CREATE TABLE `gwsubtitles` (
  `stnameid` int(11) NOT NULL AUTO_INCREMENT,
  `titlenameid` int(11) DEFAULT NULL COMMENT 'should be grabbed from the gwtitles table',
  `stname` varchar(50) DEFAULT NULL,
  `stpoints` int(11) DEFAULT NULL,
  `strank` int(11) DEFAULT NULL,
  PRIMARY KEY (`stnameid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `gwtitles` */

CREATE TABLE `gwtitles` (
  `titlenameid` int(2) NOT NULL AUTO_INCREMENT,
  `titlename` varchar(40) DEFAULT NULL,
  `titletype` int(1) DEFAULT NULL COMMENT '0 = account, 1 = character',
  `titlemaxrank` int(2) DEFAULT NULL,
  `autofilled` int(1) NOT NULL DEFAULT '0' COMMENT '0 = no, 1 = yes',
  `gwamm` int(1) NOT NULL DEFAULT '0' COMMENT '0 = no, 1 = yes',
  PRIMARY KEY (`titlenameid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `userinfo` */

CREATE TABLE `userinfo` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `userpass` varchar(255) DEFAULT NULL,
  `usermail` varchar(50) DEFAULT NULL,
  `admin` int(1) NOT NULL DEFAULT '0' COMMENT 'it''s either a 0 or 1',
  `prefaccid` int(11) NOT NULL DEFAULT '0' COMMENT 'sets which GW account to default to upon login',
  `prefaccname` varchar(50) DEFAULT 'No default selected' COMMENT 'name or alias of account',
  `prefcharid` int(11) NOT NULL DEFAULT '0' COMMENT 'sets which GW character you want to default to',
  `prefcharname` char(19) DEFAULT 'No default selected',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;