<?php

require('config.php');
include_once("../rh-library/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;

$method = $_GET["method"];
switch ($method) {
    case "list":
        if (isset($_GET['idUser'])){
				
	       	 $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_GET['idUser']);
		}else {
			 $ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"]);
		}
        break; 

}
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idUser=0){
  	global $conf;
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;
  
  try{
   
    //LISTE USERS À VALIDER
	$sql=" SELECT DISTINCT u.fk_user FROM `llx_rh_valideur_groupe` as v, llx_usergroup_user as u 
			WHERE v.fk_user=".$idUser." 
			AND v.type='Conges'
			AND v.fk_usergroup=u.fk_usergroup
			AND v.entity=".$conf->entity;
		//echo $sql;
	$ATMdb->Execute($sql);
	$TabUser=array();
	$k=0;
	while($ATMdb->Get_line()) {
				$TabUser[]=$ATMdb->Get_field('fk_user');
				$k++;
	}
	//print_r($TabUser);
	
	if($k==0){
		$sql1 = "SELECT r.rowid as rowid, r.libelle, u.name, u.firstname, r.fk_user, r.date_debut, r.date_fin FROM `llx_rh_absence` as r, `llx_user` as u WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND r.fk_user=u.rowid";  
    
	  if ($idUser!=0){
	  	$sql1.=" AND r.fk_user=".$idUser;
      }
	}else{
		$sql1 = "SELECT r.rowid as rowid, r.libelle, u.name, u.firstname, r.fk_user, r.date_debut, r.date_fin FROM `llx_rh_absence` as r, `llx_user` as u WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND r.fk_user=u.rowid";  
    
	  if ($idUser!=0){
	  	$sql1.=" AND r.fk_user IN(".implode(',', $TabUser).")";
      }
		
	}
	
	//LISTE DES ABSENCES À VALIDER
	
	/*$sql="SELECT a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut , DATE(a.date_fin) as 'Date Fin', 
			  a.libelle as 'Type absence',a.fk_user as 'Utilisateur Courant',  u.firstname as 'Prenom', u.name as 'Nom',
			  a.libelleEtat as 'Statut demande', '' as 'Supprimer'
		FROM llx_rh_absence as a, llx_user as u
		WHERE a.fk_user IN(".implode(',', $TabUser).") AND a.entity=".$conf->entity." AND u.rowid=a.fk_user";*/
   
   
   /*
    $sql1 = "SELECT r.rowid as rowid, r.libelle, u.name, u.firstname, r.fk_user, r.date_debut, r.date_fin FROM `llx_rh_absence` as r, `llx_user` as u WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND r.fk_user=u.rowid";  
    
	  if ($idUser!=0){
	  	$sql1.=" AND r.fk_user IN(".implode(',', $TabUser).")";
      }*/
	  
 	//echo $sql;
  	$ATMdb->Execute($sql1);
    
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

