<?php

	require('../config.php');
	
	
	$ATMdb=new TPDOdb;
	
	$ATMdb->Execute("SELECT DISTINCT u.rowid FROM
llx_user u LEFT OUTER JOIN llx_rh_valideur_groupe uvg ON (uvg.`fk_user`=u.rowid AND `type`='Conges')
WHERE u.rowid IN (SELECT fk_user FROM llx_user) AND uvg.rowid IS NULL");

	$TUser = $ATMdb->Get_All();
	print_r($TUser);
	foreach($TUser as $row) {
		
	}
