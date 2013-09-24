<?php
	
	require('config.php');

	header('location:'.DOL_URL_ROOT.'/user/fiche.php?id='.$user->id);
	exit;
