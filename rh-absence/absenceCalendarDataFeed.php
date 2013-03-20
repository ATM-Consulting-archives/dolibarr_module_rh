<?php

require('config.php');
include_once("../rh-library/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;

$method = $_GET["method"];
switch ($method) {
    case "list":
		/*if (isset($_REQUEST['idUser'])){
			
	       	 $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_REQUEST['idUser']);
		}else {
			 $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"]);
		}
        break;   */
        if (isset($_GET['idUser'])){
				
	       	 $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_GET['idUser']);
		}else {
			 $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"]);
		}
        break; 

}
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idUser=0){
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;
  try{
    
    $sql = "SELECT r.rowid as rowid, r.libelle, u.name, u.firstname, r.fk_user, r.date_debut, r.date_fin FROM `llx_rh_absence` as r, `llx_user` as u WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND r.fk_user=u.rowid";  
    
	  if ($idUser!=0){
	  	$sql.=" AND r.fk_user=".$idUser;
      }
 	//echo $sql;
  	$ATMdb->Execute($sql);
    
    while ($row = $ATMdb->Get_line()) {
    	//print_r($row);
      //$ret['events'][] = $row;
      //$attends = $row->AttendeeNames;
      //if($row->OtherAttendee){
      //  $attends .= $row->OtherAttendee;
      //}
      //echo $row->StartTime;

      $ret['events'][] = array(
        $row->rowid,
        $row->libelle." ".$row->name.' '.$row->firstname,
        php2JsTime(mySql2PhpTime($row->date_debut)),
        php2JsTime(mySql2PhpTime($row->date_fin)),
        1,//$row->isAllDayEvent,
        0, //more than one day event
        //$row->InstanceType,
        $row->fk_user,//Recurring event,
        6,//$row->color,
        1,//editable
        "absence.php?id=".$row->rowid."&action=view",//$row->location,
        '',//$attends
      );
    }
	}catch(Exception $e){
     $ret['error'] = $e->getMessage();
  }
  return $ret;
}

function listCalendar(&$ATMdb, $day, $type, $idAbsence){
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
  //echo $st . "--" . $et;
  return listCalendarByRange($ATMdb, $st, $et, $idAbsence);
}

