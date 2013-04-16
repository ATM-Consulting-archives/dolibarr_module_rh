<?php

require('../config.php');

global $db;

$dates=explode("/", $_REQUEST['dates']);
$datee=explode("/", $_REQUEST['datee']);
$jourdates=$dates[0];
$moisdates=$dates[1];
$anneedates=$dates[2];
$jourdatee=$datee[0];
$moisdatee=$datee[1];
$anneedatee=$datee[2];

$sqlReq="SELECT *";
$sqlReq.=" FROM ".MAIN_DB_PREFIX."rh_ressource_type as t";
$sqlReq.=" LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON r.fk_rh_ressource_type=t.rowid";
$sqlReq.=" LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON e.fk_rh_ressource=r.rowid";
$sqlReq.=" WHERE t.code='voiture'";
$sqlReq.=" AND e.type='emprunt'";
$sqlReq.=" AND e.fk_user=".$_REQUEST['fk_user'];
$sqlReq.=" AND NOT (UNIX_TIMESTAMP(e.date_debut) > ".mktime(0, 0, 0, $moisdatee, $jourdatee, $anneedatee);
$sqlReq.=" OR UNIX_TIMESTAMP(e.date_fin) < ".mktime(0, 0, 0, $moisdates, $jourdates, $anneedates).")";

$result = $db->query($sqlReq);

if($result->num_rows > 0){
	__out( array('result'=>1) ); // on enlève l'affichage de la combobox
}else{
	__out( array('result'=>0) ); // on conserve l'affichage de la combobox
}
