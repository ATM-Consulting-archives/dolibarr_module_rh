<?php
/*
 * init ressource par défaut
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/ressource.class.php');
	require('../class/contrat.class.php');
	require('../class/evenement.class.php');
	require('../class/regle.class.php');

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

//VOITURE
	$tempType = new TRH_Ressource_type;
	$tempType->chargement('Voiture', 'voiture', 1);
	$tempType->save($ATMdb);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb, 'Immatriculation', 'immatriculation','chaine', 0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Marque', 'marqueVoit', 'chaine',0, 1, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Modèle', 'modleVoit', 'chaine',0, 2, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Bail', 'bailVoit', 'chaine',0, 3, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Puissance Fiscale', 'pf', 'chaine',0, 4, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Localisation', 'localisationvehicule', 'chaine',0, 5, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Type de véhicule', 'typeVehicule', 'liste',0, 6, 'VU;VP', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Clé', 'cle', 'checkbox',1, 7, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Kit de Sécurité', 'kit', 'checkbox',1, 8, '', 1, $tempType->rowid);
	
//CARTE TOTAL
	$tempType = new TRH_Ressource_type;
	$tempType->chargement('Carte Total', 'cartetotal', 1);
	$tempType->save($ATMdb);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Numéro carte', 'numcarte', 'chaine',0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Immatriculation carte', 'immCarte', 'chaine',0, 1, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Code carte', 'codecarte', 'chaine',1, 2, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Libellé estampé', 'libcarte', 'chaine',1, 3, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Compte support', 'comptesupport', 'chaine',1, 4, '', 1, $tempType->rowid);

//CARTE AREA
	$tempType = new TRH_Ressource_type;
	$tempType->chargement('Badge Area', 'badgearea', 1);
	$tempType->save($ATMdb);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Numéro carte', 'numcarte', 'chaine',0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Immatriculation carte', 'immCarte', 'chaine',0, 1, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Compte support', 'comptesupport', 'chaine',1, 4, '', 1, $tempType->rowid);
	
	
//TELEPHONE
	$tempType = new TRH_Ressource_type;
	$tempType->chargement('Téléphone', 'telephone', 1);
	$tempType->save($ATMdb);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Marque', 'marquetel', 'chaine',0, 0, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Modèle', 'modletel', 'chaine',0, 1, '', 1, $tempType->rowid);
	
	
//CARTE SIM
	$tempType = new TRH_Ressource_type;
	$tempType->chargement('Carte SIM', 'carteSim', 1);
	$tempType->save($ATMdb);
	
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Numéro', 'numeroTel', 'chaine',0, 0, '', 1, $tempType->rowid);
	/*$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Coût minutaire interne', 'coutMinuteInterne', 'chaine',0, 1, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Coût minutaire externe', 'coutMinuteExterne', 'chaine',0, 2, '', 1, $tempType->rowid);
	/*
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications vers fixe métropolitain en Euros ht', 'commFixeMetrop', 'chaine',1, 3, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications vers mobiles Orange en Euros ht', 'commMobileOrange', 'chaine',1, 4, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications vers mobiles SFR en Euros ht', 'commMobileSFR', 'chaine',1, 5, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications vers mobiles Bouygues en Euros ht', 'commMobileBouygues', 'chaine',1, 6, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications vers l\'international en Euros ht', 'commToInternational', 'chaine',1, 7, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications depuis l\'international en Euros ht', 'commFromInternational', 'chaine',1, 8, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications internes en Euros ht', 'commInterne', 'chaine',1, 9, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications VPNonsite en Euros ht', 'commVPN', 'chaine',1, 10, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Connexions GPRS en Euros ht', 'connGPRS', 'chaine',1, 11, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Connexions GPRS depuis l\'international en Euros ht', 'connGPRSFromInternational', 'chaine',1, 12, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Connexions 3G en Euros ht', 'conn3G', 'chaine',1, 13, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Connexions 3G depuis l’international en Euros ht', 'conn3GFromInternational', 'chaine',1, 14, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'SMS en Euros ht', 'sms', 'chaine',1, 15, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'SMS sans frontière en Euros ht', 'smsSansFrontiere', 'chaine',1, 16, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Service SMS en Euros ht', 'serviceSMS', 'chaine',1, 17, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'MMS en Euros ht', 'mms', 'chaine',1, 18, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'MMS sans frontière en Euros ht', 'mmsSansFrontiere', 'chaine',1, 19, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Connexions Wifi en Euros ht', 'ConnexionsWifi', 'chaine',1, 20, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Connexions Wifi surtaxes en Euros ht', 'ConnexionsWifiSurtaxes', 'chaine',1, 21, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Wifi depuis l\'international en Euros ht', 'WifiFromInternational', 'chaine',1, 22, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Autres communications en Euros ht', 'autresCommunications', 'chaine',1, 23, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications au-delà Optima en Euros ht', 'commOptima', 'chaine',1, 24, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Dépassement facturation utilisateur en Euros ht', 'depassementFacturation', 'chaine',1, 25, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Déduction forfait unique en Euros ht', 'deductionForfait', 'chaine',1, 26, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Total communications en Euros ht', 'totalComm', 'chaine',1, 27, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications semaine en Euros ht', 'commSemaine', 'chaine',1, 28, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Communications week-end en Euros ht', 'commWeekEnd', 'chaine',1, 29, '', 1, $tempType->rowid);
	$tempField = new TRH_Ressource_field;
	$tempField->chargement($ATMdb,'Libellé de la flotte', 'libFlotte', 'chaine',1, 30, '', 1, $tempType->rowid);
	 */
		
		
	