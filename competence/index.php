<?php
	require('default.config.php');
	$dol_version = (float) DOL_VERSION;
	if($dol_version > 3.7)
		header('location:'.DOL_URL_ROOT.'/user/card.php?id='.$user->id);
	else
		header('location:'.DOL_URL_ROOT.'/user/fiche.php?id='.$user->id);
	llxHeader('','Informations salarié');
	llxFooter();
	exit;
