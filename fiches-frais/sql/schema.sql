-- Script de création de la base de données et des tables pour le service Fiches de frais

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- Suppression des tables si elles existent
DROP TABLE IF EXISTS `ligne_frais_horsforfait`;
DROP TABLE IF EXISTS `ligne_frais_forfait`;
DROP TABLE IF EXISTS `frais_forfait`;
DROP TABLE IF EXISTS `fiche_frais`;
DROP TABLE IF EXISTS `USER`;
DROP TABLE IF EXISTS `visiteur`;
DROP TABLE IF EXISTS `etat`;

-- Table des états de traitement
CREATE TABLE `etat` (
  `ETA_ID` CHAR(2)   NOT NULL PRIMARY KEY,
  `ETA_LIB` VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des visiteurs
CREATE TABLE `visiteur` (
  `VIS_ID`           CHAR(4)    NOT NULL PRIMARY KEY,
  `VIS_NOM`          CHAR(60)   NOT NULL,
  `VIS_PRENOM`       CHAR(60)   NOT NULL,
  `VIS_ADRESSE`      CHAR(60),
  `VIS_CP`           CHAR(5),
  `VIS_VILLE`        CHAR(60),
  `VIS_DATE_EMBAUCHE` DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des utilisateurs/authentification
CREATE TABLE `USER` (
  `id`           CHAR(4)       NOT NULL PRIMARY KEY,
  `login`        VARCHAR(30)   NOT NULL,
  `password`     VARCHAR(255)  NOT NULL,
  `dteConnexion` DATE,
  `role`         ENUM('comptable','administrateur','visiteur') NOT NULL,
  CONSTRAINT `FK_usr` FOREIGN KEY (`id`) REFERENCES `visiteur` (`VIS_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des fiches de frais
CREATE TABLE `fiche_frais` (
  `FFR_ID`              INT           AUTO_INCREMENT PRIMARY KEY,
  `VIS_ID`              CHAR(4)       NOT NULL,
  `ETA_ID`              CHAR(2)       NOT NULL,
  `FRR_ANNEE`           CHAR(4)       NOT NULL,
  `FRR_MOIS`            ENUM('JANVIER','FEVRIER','MARS','AVRIL','MAI','JUIN','JUILLET','AOUT','SEPTEMBRE','OCTOBRE','NOVEMBRE','DECEMBRE') NOT NULL,
  `FRR_MONTANT_VALIDE`  DECIMAL(10,2) NOT NULL DEFAULT 0,
  `FRR_NB_JUSTIFICATIFS` INT          NOT NULL DEFAULT 0,
  `FRR_DATE_MODIF`      DATE          NOT NULL,
  KEY (`VIS_ID`),
  KEY (`ETA_ID`),
  CONSTRAINT `fk_ff_visiteur` FOREIGN KEY (`VIS_ID`) REFERENCES `visiteur` (`VIS_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_ff_etat`      FOREIGN KEY (`ETA_ID`) REFERENCES `etat`    (`ETA_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des types de forfait
CREATE TABLE `frais_forfait` (
  `FOR_ID`      CHAR(3)     NOT NULL PRIMARY KEY,
  `FOR_LIB`     VARCHAR(20) NOT NULL,
  `FOR_MONTANT` DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des lignes de frais au forfait
CREATE TABLE `ligne_frais_forfait` (
  `FFR_ID` INT    NOT NULL,
  `FOR_ID` CHAR(3) NOT NULL,
  `LIG_QTE` INT    NOT NULL,
  PRIMARY KEY (`FFR_ID`,`FOR_ID`),
  CONSTRAINT `fk_lff_ffr`     FOREIGN KEY (`FFR_ID`) REFERENCES `fiche_frais`   (`FFR_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_lff_forfait` FOREIGN KEY (`FOR_ID`) REFERENCES `frais_forfait` (`FOR_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des lignes de frais hors forfait
CREATE TABLE `ligne_frais_horsforfait` (
  `ID`      INT           AUTO_INCREMENT PRIMARY KEY,
  `FFR_ID`  INT           NOT NULL,
  `DTE`     DATE          NOT NULL,
  `LIBELLE` VARCHAR(250)  NOT NULL,
  `MONTANT` DECIMAL(10,2) NOT NULL,
  `ETA_ID`  CHAR(2)       NOT NULL,
  CONSTRAINT `fk_lhf_ffr`  FOREIGN KEY (`FFR_ID`) REFERENCES `fiche_frais` (`FFR_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_lhf_etat` FOREIGN KEY (`ETA_ID`) REFERENCES `etat`        (`ETA_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;