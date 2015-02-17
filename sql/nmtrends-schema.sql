/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar` (
  `year` int(11) DEFAULT NULL,
  `month` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cases` (
  `case_id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL,
  `cms_case_id` int(11) NOT NULL,
  `cms_case_created` timestamp NULL DEFAULT NULL,
  `open_date` date DEFAULT NULL,
  `close_date` date DEFAULT NULL,
  `gender` char(3) DEFAULT NULL,
  `race` char(3) DEFAULT NULL,
  `hispanic` tinyint(1) DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT NULL,
  `age_over_60` tinyint(1) DEFAULT NULL,
  `zip` varchar(15) DEFAULT NULL,
  `county` varchar(25) DEFAULT NULL,
  `opposing_party` tinytext,
  `opp_first_name` varchar(50) DEFAULT NULL,
  `opp_last_name` varchar(50) DEFAULT NULL,
  `court_name` varchar(50) DEFAULT NULL,
  `judge_name` varchar(50) DEFAULT NULL,
  `problem` char(3) DEFAULT NULL,
  `outcome` char(4) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`case_id`),
  KEY `problem` (`problem`),
  KEY `open_date` (`open_date`),
  KEY `organization_id` (`organization_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `current_month` (
  `problem` char(3) DEFAULT NULL,
  `case_month` int(1) NOT NULL DEFAULT '0',
  `case_count` decimal(21,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `case_record_count` int(11) DEFAULT NULL,
  `success_code` char(3) NOT NULL DEFAULT 'No',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2166 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_problem_2008` (
  `value` char(3) NOT NULL DEFAULT '0',
  `label` char(80) NOT NULL DEFAULT '',
  `menu_order` tinyint(4) NOT NULL DEFAULT '0',
  KEY `label` (`label`),
  KEY `menu_order` (`menu_order`),
  KEY `val` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `organization_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `last_upload` datetime DEFAULT NULL,
  `site_url` char(255) NOT NULL,
  PRIMARY KEY (`organization_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problem_by_month` (
  `problem` char(3) DEFAULT NULL,
  `case_count` decimal(21,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problem_recent` (
  `problem` char(3) DEFAULT NULL,
  `case_count` decimal(21,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats` (
  `stat_id` int(11) NOT NULL AUTO_INCREMENT,
  `case_trend` int(11) DEFAULT NULL,
  `problem` char(2) DEFAULT NULL,
  `stat_year` char(4) DEFAULT NULL,
  `stat_month` char(2) DEFAULT NULL,
  `current` int(11) DEFAULT NULL,
  `historical_month_average` int(11) DEFAULT NULL,
  `recent_average` int(11) DEFAULT NULL,
  `stat_date` date DEFAULT NULL,
  PRIMARY KEY (`stat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2705 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
