-- --------------------------------------------------------
-- Hôte :                        127.0.0.1
-- Version du serveur:           5.7.21 - MySQL Community Server (GPL)
-- SE du serveur:                Linux
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Export de la structure de la base pour sesileprod_test
CREATE DATABASE IF NOT EXISTS `%%database_name%%` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `%%database_name%%`;


-- Export de la structure de table sesileprod_test. Action
CREATE TABLE IF NOT EXISTS `Action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classeur_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `action` longtext NOT NULL,
  `observation` longtext,
  `commentaire` longtext,
  `user_action` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_406089A4229E97AF` (`user_action`),
  KEY `IDX_406089A4EC10E96A` (`classeur_id`),
  CONSTRAINT `Action_ibfk_1` FOREIGN KEY (`classeur_id`) REFERENCES `Classeur` (`id`),
  CONSTRAINT `Action_ibfk_2` FOREIGN KEY (`user_action`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Aide
CREATE TABLE IF NOT EXISTS `Aide` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Circuit
CREATE TABLE IF NOT EXISTS `Circuit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ordre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Classeur
CREATE TABLE IF NOT EXISTS `Classeur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` longtext,
  `creation` datetime NOT NULL,
  `validation` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `ordreEtape` int(11) NOT NULL,
  `visibilite` int(11) NOT NULL,
  `circuit` varchar(255) DEFAULT NULL,
  `ordreCircuit` int(11) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `EtapeDeposante` int(11) NOT NULL,
  `ordreValidant` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2829E10C8CDE5729` (`type`),
  CONSTRAINT `Classeur_ibfk_1` FOREIGN KEY (`type`) REFERENCES `TypeClasseur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. ClasseursUsers
CREATE TABLE IF NOT EXISTS `ClasseursUsers` (
  `classeur_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  PRIMARY KEY (`classeur_id`,`user_id`,`ordre`),
  KEY `IDX_A6067AC4A76ED395` (`user_id`),
  KEY `IDX_A6067AC4EC10E96A` (`classeur_id`),
  CONSTRAINT `ClasseursUsers_ibfk_1` FOREIGN KEY (`classeur_id`) REFERENCES `Classeur` (`id`),
  CONSTRAINT `ClasseursUsers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Classeur_copy
CREATE TABLE IF NOT EXISTS `Classeur_copy` (
  `classeur_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`classeur_id`,`user_id`),
  KEY `IDX_D16DD300A76ED395` (`user_id`),
  KEY `IDX_D16DD300EC10E96A` (`classeur_id`),
  CONSTRAINT `Classeur_copy_ibfk_1` FOREIGN KEY (`classeur_id`) REFERENCES `Classeur` (`id`) ON DELETE CASCADE,
  CONSTRAINT `Classeur_copy_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. classeur_groupe
CREATE TABLE IF NOT EXISTS `classeur_groupe` (
  `typeclasseur_id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  PRIMARY KEY (`groupe_id`,`typeclasseur_id`),
  KEY `IDX_C6A03DCB7A45358C` (`groupe_id`),
  KEY `IDX_C6A03DCB9258CF37` (`typeclasseur_id`),
  CONSTRAINT `classeur_groupe_ibfk_1` FOREIGN KEY (`typeclasseur_id`) REFERENCES `TypeClasseur` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classeur_groupe_ibfk_2` FOREIGN KEY (`groupe_id`) REFERENCES `Groupe` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Classeur_visible
CREATE TABLE IF NOT EXISTS `Classeur_visible` (
  `classeur_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`classeur_id`,`user_id`),
  KEY `IDX_2D9281F3A76ED395` (`user_id`),
  KEY `IDX_2D9281F3EC10E96A` (`classeur_id`),
  CONSTRAINT `Classeur_visible_ibfk_1` FOREIGN KEY (`classeur_id`) REFERENCES `Classeur` (`id`) ON DELETE CASCADE,
  CONSTRAINT `Classeur_visible_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Collectivite
CREATE TABLE IF NOT EXISTS `Collectivite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `message` longtext,
  `active` tinyint(1) NOT NULL,
  `textmailnew` varchar(3000) DEFAULT NULL,
  `textmailrefuse` varchar(3000) DEFAULT NULL,
  `textmailwalid` varchar(3000) DEFAULT NULL,
  `abscissesVisa` int(11) DEFAULT NULL,
  `ordonneesVisa` int(11) DEFAULT NULL,
  `abscissesSignature` int(11) DEFAULT NULL,
  `ordonneesSignature` int(11) DEFAULT NULL,
  `couleurVisa` varchar(10) DEFAULT NULL,
  `titreVisa` varchar(250) DEFAULT 'VISE PAR',
  `pageSignature` int(11) DEFAULT NULL,
  `deleteClasseurAfter` int(11) NOT NULL DEFAULT '180',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Delegations
CREATE TABLE IF NOT EXISTS `Delegations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delegant` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AEB8727A851BB8D7` (`delegant`),
  KEY `IDX_AEB8727A8D93D649` (`user`),
  CONSTRAINT `Delegations_ibfk_1` FOREIGN KEY (`delegant`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  CONSTRAINT `Delegations_ibfk_2` FOREIGN KEY (`user`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Document
CREATE TABLE IF NOT EXISTS `Document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classeur_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `repourl` varchar(1000) NOT NULL,
  `type` varchar(255) NOT NULL,
  `signed` tinyint(1) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `display` tinyint(1) DEFAULT NULL,
  `downloaded` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_211FE820EC10E96A` (`classeur_id`),
  CONSTRAINT `Document_ibfk_1` FOREIGN KEY (`classeur_id`) REFERENCES `Classeur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. DocumentDetachedSign
CREATE TABLE IF NOT EXISTS `DocumentDetachedSign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `repourl` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D7DF1E62C33F7837` (`document_id`),
  CONSTRAINT `DocumentDetachedSign_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `Document` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. DocumentHistory
CREATE TABLE IF NOT EXISTS `DocumentHistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) DEFAULT NULL,
  `sha` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL,
  `comment` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_279321ACC33F7837` (`document_id`),
  CONSTRAINT `DocumentHistory_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `Document` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. EtapeClasseur
CREATE TABLE IF NOT EXISTS `EtapeClasseur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classeur` int(11) DEFAULT NULL,
  `ordre` int(11) DEFAULT NULL,
  `EtapeValidante` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B476E85FD15F835A` (`classeur`),
  CONSTRAINT `EtapeClasseur_ibfk_1` FOREIGN KEY (`classeur`) REFERENCES `Classeur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. etapeclasseur_user
CREATE TABLE IF NOT EXISTS `etapeclasseur_user` (
  `etapeclasseur_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`etapeclasseur_id`,`user_id`),
  KEY `IDX_47D31B04A76ED395` (`user_id`),
  KEY `IDX_47D31B04BFC3F54E` (`etapeclasseur_id`),
  CONSTRAINT `etapeclasseur_user_ibfk_1` FOREIGN KEY (`etapeclasseur_id`) REFERENCES `EtapeClasseur` (`id`) ON DELETE CASCADE,
  CONSTRAINT `etapeclasseur_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. etapeclasseur_userpack
CREATE TABLE IF NOT EXISTS `etapeclasseur_userpack` (
  `etapeclasseur_id` int(11) NOT NULL,
  `userpack_id` int(11) NOT NULL,
  PRIMARY KEY (`etapeclasseur_id`,`userpack_id`),
  KEY `IDX_4A62C70A85784EA9` (`userpack_id`),
  KEY `IDX_4A62C70ABFC3F54E` (`etapeclasseur_id`),
  CONSTRAINT `etapeclasseur_userpack_ibfk_1` FOREIGN KEY (`etapeclasseur_id`) REFERENCES `EtapeClasseur` (`id`) ON DELETE CASCADE,
  CONSTRAINT `etapeclasseur_userpack_ibfk_2` FOREIGN KEY (`userpack_id`) REFERENCES `UserPack` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. EtapeGroupe
CREATE TABLE IF NOT EXISTS `EtapeGroupe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe` int(11) DEFAULT NULL,
  `ordre` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BF2B6E224B98C21` (`groupe`),
  CONSTRAINT `EtapeGroupe_ibfk_1` FOREIGN KEY (`groupe`) REFERENCES `Groupe` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. etapegroupe_user
CREATE TABLE IF NOT EXISTS `etapegroupe_user` (
  `etapegroupe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`etapegroupe_id`,`user_id`),
  KEY `IDX_76A8B5DA45DF1213` (`etapegroupe_id`),
  KEY `IDX_76A8B5DAA76ED395` (`user_id`),
  CONSTRAINT `etapegroupe_user_ibfk_1` FOREIGN KEY (`etapegroupe_id`) REFERENCES `EtapeGroupe` (`id`) ON DELETE CASCADE,
  CONSTRAINT `etapegroupe_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. etapegroupe_userpack
CREATE TABLE IF NOT EXISTS `etapegroupe_userpack` (
  `etapegroupe_id` int(11) NOT NULL,
  `userpack_id` int(11) NOT NULL,
  PRIMARY KEY (`etapegroupe_id`,`userpack_id`),
  KEY `IDX_C21AD9CD45DF1213` (`etapegroupe_id`),
  KEY `IDX_C21AD9CD85784EA9` (`userpack_id`),
  CONSTRAINT `etapegroupe_userpack_ibfk_1` FOREIGN KEY (`etapegroupe_id`) REFERENCES `EtapeGroupe` (`id`) ON DELETE CASCADE,
  CONSTRAINT `etapegroupe_userpack_ibfk_2` FOREIGN KEY (`userpack_id`) REFERENCES `UserPack` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Groupe
CREATE TABLE IF NOT EXISTS `Groupe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Nom` varchar(255) NOT NULL,
  `collectivite` int(11) DEFAULT NULL,
  `couleur` varchar(255) DEFAULT NULL,
  `json` longtext,
  `ordreEtape` varchar(255) NOT NULL,
  `creation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3158917CFA408A1` (`collectivite`),
  CONSTRAINT `Groupe_ibfk_1` FOREIGN KEY (`collectivite`) REFERENCES `Collectivite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. migration_versions
CREATE TABLE IF NOT EXISTS `migration_versions` (
  `version` varchar(255) NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. Patch
CREATE TABLE IF NOT EXISTS `Patch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(255) NOT NULL,
  `Version` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. TypeClasseur
CREATE TABLE IF NOT EXISTS `TypeClasseur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `creation` datetime DEFAULT NULL,
  `supprimable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. User
CREATE TABLE IF NOT EXISTS `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(180) NOT NULL,
  `username_canonical` varchar(180) NOT NULL,
  `email` varchar(180) NOT NULL,
  `email_canonical` varchar(180) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `confirmation_token` varchar(180) DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:array)',
  `Nom` varchar(255) DEFAULT NULL,
  `Prenom` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `code_postal` varchar(6) DEFAULT NULL,
  `pays` varchar(255) DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `apitoken` varchar(40) DEFAULT NULL,
  `apisecret` varchar(40) DEFAULT NULL,
  `apiactivated` tinyint(1) DEFAULT '0',
  `collectivite` int(11) DEFAULT NULL,
  `pathSignature` varchar(255) DEFAULT NULL,
  `qualite` varchar(255) DEFAULT NULL,
  `sesile_version` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DA1797792FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_2DA17977A0D96FBF` (`email_canonical`),
  UNIQUE KEY `UNIQ_2DA17977C05FB297` (`confirmation_token`),
  KEY `IDX_2DA17977CFA408A1` (`collectivite`),
  CONSTRAINT `User_ibfk_1` FOREIGN KEY (`collectivite`) REFERENCES `Collectivite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. UserGroupe
CREATE TABLE IF NOT EXISTS `UserGroupe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `groupe` int(11) DEFAULT NULL,
  `parent` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_24B29D034B98C21` (`groupe`),
  KEY `IDX_24B29D038D93D649` (`user`),
  CONSTRAINT `UserGroupe_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User` (`id`),
  CONSTRAINT `UserGroupe_ibfk_2` FOREIGN KEY (`groupe`) REFERENCES `Groupe` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. UserPack
CREATE TABLE IF NOT EXISTS `UserPack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collectivite` int(11) DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `creation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_68E7EB3ACFA408A1` (`collectivite`),
  CONSTRAINT `UserPack_ibfk_1` FOREIGN KEY (`collectivite`) REFERENCES `Collectivite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. userpack_user
CREATE TABLE IF NOT EXISTS `userpack_user` (
  `userpack_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`userpack_id`,`user_id`),
  KEY `IDX_A5F2E9085784EA9` (`userpack_id`),
  KEY `IDX_A5F2E90A76ED395` (`user_id`),
  CONSTRAINT `userpack_user_ibfk_1` FOREIGN KEY (`userpack_id`) REFERENCES `UserPack` (`id`) ON DELETE CASCADE,
  CONSTRAINT `userpack_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.


-- Export de la structure de table sesileprod_test. UserRole
CREATE TABLE IF NOT EXISTS `UserRole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) DEFAULT NULL,
  `userRoles` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A8503F738D93D649` (`user`),
  CONSTRAINT `UserRole_ibfk_1` FOREIGN KEY (`user`) REFERENCES `User` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- L'exportation de données n'était pas sélectionnée.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
