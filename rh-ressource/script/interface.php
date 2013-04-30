<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

//Interface qui renvoie les emprunts de ressources d'un utilisateur
$get = isset($_REQUEST['get'])?$_REQUEST['get']:'emprunt';

_get($get);

function _get($case) {
	switch ($case) {
		case 'emprunt':
			__out(_emprunt($_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));
			break;
	}
}

function _emprunt($userId, $date_debut, $date_fin){
		global $user,$conf;
		$ATMdb=new Tdb;
		
		$TabEmprunt=array();
		
		$sql="SELECT libelle, numId	
		FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
		WHERE e.entity=".$conf->entity."
		AND e.fk_user=".$userId."
		AND (date_debut<='".$date_fin."' AND date_fin>='".$date_debut."')";
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TabEmprunt[]=array(
				'nom'=>$ATMdb->Get_field('libelle').' - '.$ATMdb->Get_field('numId')
				,'date_debut'=>$ATMdb->Get_field('date_debut')
				,'date_fin'=>$ATMdb->Get_field('date_fin')
			);
		}
		
		return $TabEmprunt;
}

