#!/usr/bin/php
<?php

	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');

	$ATMdb=new TPDOdb;
	
	$TId = TRequeteCore::get_id_from_what_you_want($ATMdb, 'llx_user');
	
	foreach($TId as $id) {
		$user=new User($db);
		
		$user->fetch($id);
	
		/*
		 * Ajout des droit NDF pour user standart
		 */	
		$user->addrights(70301);
		$user->addrights(70302);
		$user->addrights(70303);
		$user->addrights(70305);
		$user->addrights(70306);
		
	}
	