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
  `race` char(22) DEFAULT NULL,
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
  `outcome` char(64) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  veteran_household char(3),
  language char(32),
  children tinyint,
  persons_helped int,
  poverty float(6,2),
  client_age tinyint,
client_id int,
close_code varchar(64),
city_problem varchar(64),
city_poverty varchar(64)
  PRIMARY KEY (`case_id`),
  KEY `problem` (`problem`),
  KEY `open_date` (`open_date`),
  KEY `organization_id` (`organization_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counties` (
  `county` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `problem` char(3) DEFAULT NULL,
  `stat_year` char(4) DEFAULT NULL,
  `stat_month` char(2) DEFAULT NULL,
  `current` int(11) DEFAULT NULL,
  `historical_month_average` int(11) DEFAULT NULL,
  `recent_average` int(11) DEFAULT NULL,
  `stat_date` date DEFAULT NULL,
  PRIMARY KEY (`stat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2705 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `counties` VALUES ('Bernalillo'),('Catron'),('Chaves'),('Cibola'),('Colfax'),('Curry'),('De Baca'),('Dona Ana'),('Eddy'),('Grant'),('Guadalupe'),('Harding'),('Hidalgo'),('Lea'),('Lincoln'),('Los Alamos'),('Luna'),('McKinley'),('Mora'),('Otero'),('Quay'),('Rio Arriba'),('Roosevelt'),('San Juan'),('San Miguel'),('Sandoval'),('Santa Fe'),('Sierra'),('Socorro'),('Taos'),('Torrance'),('Union'),('Valencia');

TRUNCATE menu_problem_2008;
INSERT INTO menu_problem_2008 VALUES ('01','01 - Bankruptcy/Debtor Relief',0),('02','02 - Collection (Repo/Def/Garnish)',1),('03','03 - Contracts/Warranties',2),('04','04 - Collection Practices/Creditor Harassment',3),('05','05 - Predatory Lending Practices (Not Mortgages)',4),('06','06 - Loans/Installment Purch.',5),('07','07 - Public Utilities',6),('08','08 - Unfair and Deceptive Sales Practices',7),('09','09 - Other Consumer/Finance.',8),('11','11 - Reserved',9),('12','12 - Discipline (Including Expulsion and Suspension)',10),('12b','12b - Financial Exploitation',10),('13','13 - Special Education/Learning Disabilities',11),('14','14 - Access to Education (Including Bilingual, Residency, Testing)',12),('15','15 - Vocational Education',13),('16','11/16 - Student Financial Aid',14),('19','19 - Other Education',15),('21','21 - Employment Discrimination',16),('22','22 - Wage Claims and other FLSA (Fair Labor Standards Act) Issues',17),('23','23 - EITC (Earned Income Tax Credit)',18),('24','24 - Taxes (Not EITC)',19),('25','25 - Employee Rights',20),('26','26 - Agricultural Worker Issues (Not Wage Claims/FLSA Issues)',21),('27','27 - Worker\'s Compensation',21),('29','29 - Other Employment',22),('30','30 - Adoption',23),('31','31 - Custody/Visitation',24),('32','32 - Divorce/Separ./Annul.',25),('33','33 - Adult Guardianship/Conservatorship',26),('34','34 - Name Change',27),('35','35 - Parental Rights Termin.',28),('36','36 - Paternity',29),('37','37 - Domestic Abuse',30),('38','38 - Support',31),('39','39 - Other Family',32),('39z','39z - Unknown Family Matter Conflicted Out',32),('41','41 - Delinquent',33),('42','42 - Neglected/Abused/Depend.',34),('43','43 - Emancipation',35),('44','44 - Minor Guardianship/Conservatorship',36),('49','49 - Other Juvenile',37),('51','51 - Medicaid',38),('52','52 - Medicare',39),('53','53 - Government Children\'s Health Insurance Program',40),('54','54 - Home and Community Based Care',41),('55','55 - Private Health Insurance',42),('56','56 - Long Term Health Care Facilities',43),('57','57 - State and Local Health',44),('59','59 - Other Health',45),('61','61 - Fed. Subsidized Housing',46),('62','62 - Homeownership/Real Prop. (Not Foreclosure)',47),('63','63 - Private Landlord/Tenant',48),('64','64 - Public Housing',49),('65','65 - Mobile Homes',50),('66','66 - Housing Discrimination',51),('67','67 - Mortgage Foreclosure (Not Predatory Lending Practices)',52),('68','68 - Mortgage Predatory Lending/Practices',53),('69','69 - Other Housing',54),('71','71 - TANF',55),('72','72 - Social Security (Not SSDI)',56),('73','73 - Food Stamps / Commodities',57),('74','74 - SSDI',58),('75','75 - SSI',59),('76','76 - Unemployment Compensation',60),('77','77 - Veterans Benefits',61),('78','78 - State and Local Income Maintenance',62),('79','79 - Other Income Maintanence',63),('81','81 - Immigration / Natural.',64),('82','82 - Mental Health',65),('83','83 - Prisoner\'s Rights',65),('84','84 - Physically Disabled Rghts',66),('85','85 - Civil Rights',67),('86','86 - Human Trafficking',68),('89','89 - Other Individual Rights',69),('91','91 - Legal Assistance to Non-Profit Organization or Group (Including Inc./Dis.)',70),('92','92 - Indian / Tribal Law',71),('93','93 - Licenses (Drivers, Occupational, and Others)',72),('94','94 - Torts',73),('95','95 - Wills and Estates',74),('96','96 - Advance Directives/Powers of Attorney',75),('97','97 - Municipal Legal Needs',76),('97a','97a - Other Probs. (Non-legal)',76),('98','98 - Criminal Referrals',76),('99','99 - Other Miscellaneous',77),('99O','99O - Outreach Activities',78),('99C','99C - Community Education',79),('99U','99U - Underserved Services',80),('99a','99a - Client Services',81);

