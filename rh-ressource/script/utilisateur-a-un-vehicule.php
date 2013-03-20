<?php

require('../config.php');

$sqlReq="SELECT *";
$sqlReq.=" FROM ".MAIN_DB_PREFIX."rh_ressource_type as t, ";
$sqlReq.=MAIN_DB_PREFIX."rh_ressource as r, ";
$sqlReq.=MAIN_DB_PREFIX."rh_evenement as e";
$sqlReq.=" WHERE t.code='voiture'";
$sqlReq.=" AND r.fk_rh_ressource_type=t.rowid";
$sqlReq.=" AND e.fk_rh_ressource=r.rowid";
$sqlReq.=" AND e.type='emprunt'";
$sqlReq.=" AND e.fk_user=".$_REQUEST['fk_user'];
$sqlReq.=" AND NOT (UNIX_TIMESTAMP(e.date_debut) > ".$_REQUEST['dates'];
$sqlReq.=" AND UNIX_TIMESTAMP(e.date_fin) < ".$_REQUEST['datee'].")";
$sqlReq.=" GROUP BY t.rowid";

$result = $db->query($sqlReq);

if($result->num_rows > 0){
	__out( array('result'=>1) );
}else{
	__out( array('result'=>0) );
}