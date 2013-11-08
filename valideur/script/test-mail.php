#!/usr/bin/php
<?php
	define('INC_FROM_CRON_SCRIPT',true);
	require("../config.php");


	$r=new TReponseMail("ndfp@cpro.com","alexis@atm-consulting.fr","ceci est un test","Si quelqu'un d'autre que moi reçois ce message, merci de me le forwarder");

	print (int)$r->send(true, 'utf-8');
