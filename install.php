<?php

include('variables/variables.php');

$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `language` int(9) NOT NULL,
  `count` int(9) NOT NULL DEFAULT '0',
  `definition` varchar(500) NOT NULL,
  `pos` int(2) NOT NULL,
  `postwo` int(2) NOT NULL,
  `sample_sentence` varchar(500) NOT NULL,
  `english_equivalent` varchar(50) NOT NULL,
  `domain` int(2) NOT NULL,
  `blacklist` int(2) NOT NULL,
  `englishword` int(2) NOT NULL,
  `standard_spelling` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`),
  KEY `id_3` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

$sql[] = " CREATE TABLE IF NOT EXISTS `article` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  `url` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date` datetime NOT NULL,
  `uid` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1; ";

$sql[] = "CREATE TABLE IF NOT EXISTS `blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blacklist` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2; ";


$sql[] = " CREATE TABLE IF NOT EXISTS `domain` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";

$sql[] = "CREATE TABLE IF NOT EXISTS `genre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";

$sql[] = "CREATE TABLE IF NOT EXISTS `language` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `frequent_word_value` int(11) NOT NULL DEFAULT '1000',
  `frequent_words` longblob NOT NULL,
  `frequent_manual` varchar(65000) NOT NULL DEFAULT 'a:0:{}',
  `total_words` int(11) NOT NULL DEFAULT '0',
  `distinct_words` int(1) NOT NULL DEFAULT '0',
  `words_constant` decimal(10,4) NOT NULL DEFAULT '0.0860',
  `sentences_constant` decimal(10,4) NOT NULL DEFAULT '0.1410',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `meta` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `permissions` (
  `pid` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `pos` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `progress` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `text_updater` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `semantic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `subdomain` int(1) NOT NULL DEFAULT '0',
  `waray` varchar(255) NOT NULL,
  `english` varchar(255) NOT NULL,
  `filipino` varchar(255) NOT NULL,
  `kana` varchar(255) NOT NULL,
  `inabaknon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `spelling` (
  `original` tinytext NOT NULL,
  `revised` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `text` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `content` longtext CHARACTER SET utf8 NOT NULL,
  `language` int(9) NOT NULL,
  `genre` int(9) NOT NULL,
  `author` varchar(255) CHARACTER SET utf8 NOT NULL,
  `year` year(4) NOT NULL,
  `sentence_count` int(11) NOT NULL,
  `word_list` longtext CHARACTER SET utf8 NOT NULL,
  `word_count` int(9) NOT NULL,
  `words_per_sentence` decimal(10,0) NOT NULL,
  `readability` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `last` varchar(50) CHARACTER SET latin1 NOT NULL,
  `email` varchar(255) CHARACTER SET latin1 NOT NULL,
  `photo` varchar(50) CHARACTER SET latin1 NOT NULL,
  `access` text CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  `password` varchar(500) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

foreach ($sql as $table) {
  try {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $stmt = $db->prepare($table);
    $stmt->execute();
  }
  catch(PDOException $e) {
    echo $e->getMessage();
  }
}