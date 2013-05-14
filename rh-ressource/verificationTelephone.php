<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
global $conf;
$ATMdb=new TPDOdb;
llxHeader('','Vérification des consommation téléphonique');




llxFooter();