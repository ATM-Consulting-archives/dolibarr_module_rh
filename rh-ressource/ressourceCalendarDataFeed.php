<?php

require('config.php');
include_once("../rh-library/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;


$method = $_GET["method"];
switch ($method) {
    case "list": 
		if ($_REQUEST['id']!=0){
			$ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_REQUEST['id'], false);	
		}
		else {
			$ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_REQUEST['type'], true);
		}
        
        break;   

}
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idRessource=null, $typeRessource=false){
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;
  try{
    
	$sql = "SELECT * 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
	WHERE ";
	if ($typeRessource) {$sql .= "r.fk_rh_ressource_type=". $idRessource." AND ";}
	
	$sql .= " `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."'";
    if (! $typeRessource){
    	$sql.=" AND e.fk_rh_ressource=".$idRessource;
	}
	
	//echo $sql;
   $ATMdb->Execute($sql);
    while ($row=$ATMdb->Get_line()) {
      //$ret['events'][] = $row;
      //$attends = $row->AttendeeNames;
      //if($row->OtherAttendee){
      //  $attends .= $row->OtherAttendee;
      //}
      //echo $row->StartTime;
      if ($row->type == 'emprunt'){
      	$lien = 'attribution.php?id='.$row->fk_rh_ressource.'&idEven='.$row->rowid.'&action=view';
      }
	  else {
	  	$lien = 'evenement.php?id='.$row->fk_rh_ressource.'&idEven='.$row->rowid.'&action=view';
	  }
      $ret['events'][] = array(
       $row->rowid,
        $row->subject,
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

function listCalendar(&$ATMdb, $day, $type, $idRessource=null, $typeRessource=false){
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
 
	return listCalendarByRange($ATMdb, $st, $et, $idRessource, $typeRessource);
  

}

