/*
Création de la table 


*/


SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


--
-- Base de données: `cpro_dolibarr`
--

-- --------------------------------------------------------

--
-- Structure de la table `llx_fin_grille_penalite`
--

CREATE TABLE IF NOT EXISTS `llx_ressource_field` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(32) NOT NULL,
  `libelle` varchar(32) NOT NULL,
  `valeur` varchar(32) NOT NULL,
  `obligatoire` int(1) NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Contenu de la table `llx_fin_grille_penalite`
--

INSERT INTO `llx_fin_grille_penalite` (`rowid`, `nom`, `libelle`, `valeur`,'obligatoire') VALUES
(1, 'opt_periodicite', 'opt_trimestriel', 0),
(2, 'opt_periodicite', 'opt_mensuel', 5),
(3, 'opt_mode_reglement', 'opt_prelevement', 0),
(4, 'opt_mode_reglement', 'opt_virement', 3),
(5, 'opt_mode_reglement', 'opt_cheque', 8),
(6, 'opt_terme', 'opt_a_echoir', 0),
(7, 'opt_terme', 'opt_echu', 4),
(8, 'opt_administration', 'opt_administration', 4.5),
(9, 'opt_creditbail', 'opt_creditbail', 6);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
