<?php

require('config.php');
require('./lib/ressource.lib.php');
include_once("../rh-library/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;


$method = $_GET["method"];
switch ($method) {
    case "list": 
		if (isset($_REQUEST['id'])){
			//on regarde si la ressource courante est une sous-ressource
			$sql = "SELECT fk_rh_ressource FROM ".MAIN_DB_PREFIX."rh_ressource 
			WHERE rowid=".$_REQUEST['id']."
			AND entity IN (0, ".$conf->entity.")";
			$ATMdb->Execute($sql);
			if ($row=$ATMdb->Get_line()) {
				$id = ($row->fk_rh_ressource != 0) ? $row->fk_rh_ressource : $_REQUEST['id'];
			}
		}
		else {$id = 0;}
		echo $sql;
		echo $id;
		$ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], 
					$_REQUEST['type'], $id, $_REQUEST['fk_user'], $_REQUEST['typeEven']);
        
        break;   

}
$ATMdb->close();
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idTypeRessource=0, $idRessource = 0,$fk_user = 0, $typeEven = 'all' ){
  global $user;
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;
  
  $TEvent = getTypeEvent($idTypeRessource);
  $TRessource = getRessource(0);
  $TUser = getUsers();
 
  try{
	$sql = "SELECT e.rowid,  date_debut, date_fin, isAllDayEvent, fk_user, color, type, subject, e.fk_rh_ressource 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
	WHERE ";
	
	$sql .= " date_debut<='".php2MySqlTime($ed)."' AND date_fin >= '". php2MySqlTime($sd)."' ";
	//$sql .= " `date_debut` between '"
    //  .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."'";
    
	if ($idTypeRessource!=0) {$sql .= " AND r.fk_rh_ressource_type=".$idTypeRessource;}
	if ($idRessource!=0) {$sql .= " AND e.fk_rh_ressource=".$idRessource;}
	if ($fk_user!=0) {$sql .= " AND e.fk_user=".$fk_user;}
	if ($typeEven && $typeEven!='all') {$sql .= " AND e.type='".$typeEven."'";}
	//echo $sql;
	/*else{
    	$sql.=" AND e.fk_rh_ressource=".$idRessource;
	}//*/
	
	if (!$user->rights->ressource->agenda->viewAgenda){
    	$sql.=" AND e.fk_user=".$user->id;
	}
	//echo $sql;
   $ATMdb->Execute($sql);
    while ($row=$ATMdb->Get_line()) {
      //$ret['events'][] = $row;
      //$attends = $row->AttendeeNames;
      //if($row->OtherAttendee){
      //  $attends .= $row->OtherAttendee;
      //}
      if ($row->type == 'emprunt'){
      	$lien = 'attribution.php?id='.$row->fk_rh_ressource.'&idEven='.$row->rowid.'&action=view';
      }
	  else {
	  	$lien = 'evenement.php?id='.$row->fk_rh_ressource.'&idEven='.$row->rowid.'&action=view';
	  }
	 
	 
	 
	  //on écrit l'intitulé du calendrier en fonction des données de la fonction
	  $sujet = '';
	  $sujet .= (empty($idRessource) || ($idRessource==0)) ? $TRessource[$row->fk_rh_ressource].', ' : '';
	  $sujet .=  ($typeEven=='all') ? $TEvent[$row->type] : '' ;
	  $sujet .= ($fk_user==0) ? ', '.$TUser[$row->fk_user] : '';  
      $ret['events'][] = array(
       $row->rowid,
        $sujet,
        php2JsTime(mySql2PhpTime($row->date_debut)),
        php2JsTime(mySql2PhpTime($row->date_fin)),
        $row->isAllDayEvent,
        0, //more than one day event
        //$row->InstanceType,
        $row->fk_user,//Recurring event,
        $row->color,
        1,//editable
        $lien,//$row->location,
        '',//$attends
        $row->fk_user
      );
    }
	}catch(Exception $e){
     $ret['error'] = $e->getMessage();
  }
  return $ret;
}

function listCalendar(&$ATMdb, $day, $type, $idTypeRessource=0, $idRessource = 0,$fk_user = 0, $typeEven = 'all'){
  $phpTime = js2PhpTime($day);
  //echo $phpTime . "+" . $type;
  switch($type){
    case "month":
      $st = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime));
      $et = mktime(0, 0, -1, date("m", $phpTime)+1, 1, date("Y", $phpTime));
      break;
    case "week":
      //suppose first day of a week is monday 
      $monday  =  date("d", $phpTime) - date('N', $phpTime) + 1;
      //echo date('N', $phpTime);
      $st = mktime(0,0,0,date("m", $phpTime), $monday, date("Y", $phpTime));
      $et = mktime(0,0,-1,date("m", $phpTime), $monday+7, date("Y", $phpTime));
      break;
    case "day":
      $st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
      $et = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime)+1, date("Y", $phpTime));
      break;
  }
 
	return listCalendarByRange($ATMdb, $st, $et, $idTypeRessource, $idRessource ,$fk_user , $typeEven );
  

}

