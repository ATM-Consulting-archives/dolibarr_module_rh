<?php

require('config.php');
include_once("../rh-library/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;


$method = $_GET["method"];
switch ($method) {
    case "list":
        $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_REQUEST['id'], $_REQUEST['idUser']);
        break;   

}
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idAbsence, $idUser){
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;
  try{
    
    $sql = "SELECT * FROM `llx_rh_absence` WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND fk_user=".$idUser;
   
   $ATMdb->Execute($sql);
    //echo $sql;
    while ($row=$ATMdb->Get_line()) {
      //$ret['events'][] = $row;
      //$attends = $row->AttendeeNames;
      //if($row->OtherAttendee){
      //  $attends .= $row->OtherAttendee;
      //}
      //echo $row->StartTime;
      $ret['events'][] = array(
        $row->rowid,
        $row->libelle,
        php2JsTime(mySql2PhpTime($row->date_debut)),
        php2JsTime(mySql2PhpTime($row->date_fin)),
        1,//$row->isAllDayEvent,
        0, //more than one day event
        //$row->InstanceType,
        $row->fk_user,//Recurring event,
        6,//$row->color,
        1,//editable
        "",//$row->location,
        '',//$attends
      );
    }
	}catch(Exception $e){
     $ret['error'] = $e->getMessage();
  }
  return $ret;
}

function listCalendar(&$ATMdb, $day, $type, $idAbsence, $idUser){
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
  return listCalendarByRange($ATMdb, $st, $et, $idAbsence, $idUser);
}

