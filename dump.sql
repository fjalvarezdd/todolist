--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) NOT NULL DEFAULT '',
  `task_due` date NOT NULL,
  `complete` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;