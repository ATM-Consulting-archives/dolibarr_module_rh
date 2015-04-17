<?php
define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;


//print_r($TVoiture);exit();

$plagedeb = !empty($_REQUEST['plagedebut']) ? dateToInt($_REQUEST['plagedebut']) : (time()-31532400);
$plagefin = !empty($_REQUEST['plagefin']) ? dateToInt($_REQUEST['plagefin']) : (time()+31532400);
$fk_user = !empty($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : 0 ;
$limite = (isset($_REQUEST['limite'])) ? floatval($_REQUEST['limite']) : 0;
//$plagedeb = !empty($_REQUEST['plagedebut']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagedebut'])) : date("Y-m-d 00:00:00",time()-31532400);
//$plagefin = !empty($_REQUEST['plagefin']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagefin'])) : date("Y-m-d  00:00:00", time()+31532400);
$TRessource = getConsommation($ATMdb, $plagedeb, $plagefin, $fk_user,  $limite);

/*foreach ($TRessource as $key => $value) {
	echo $key.' : ';
	print_r($value);
	echo '<br>';
}*/
//print_r($TRessource);
//echo json_encode($TRessource);
__out($TRessource);


exit();
