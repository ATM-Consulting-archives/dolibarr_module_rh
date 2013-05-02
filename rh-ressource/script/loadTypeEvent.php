<?php
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;

if(isset($_REQUEST['type'])) {
		
		$TEvent = getTypeEvent($_REQUEST['type']);
		echo json_encode($TEvent);
		
		exit();
	}
	