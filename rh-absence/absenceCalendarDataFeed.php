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
		$sql1 = "SELECT r.rowid as rowid, r.libelle, r.type, u.name, u.firstname, r.fk_user, r.date_debut, r.date_fin FROM `llx_rh_absence` as r, `llx_user` as u WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND r.fk_user=u.rowid";  
    
	  if ($idUser!=0){
	  	$sql1.=" AND r.fk_user=".$idUser;
      }
	}else{
		$sql1 = "SELECT r.rowid as rowid, r.libelle,  r.type, u.name, u.firstname, r.fk_user, r.date_debut, r.date_fin FROM `llx_rh_absence` as r, `llx_user` as u WHERE `date_debut` between '"
      .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."' AND r.fk_user=u.rowid";  
	  if ($idUser!=0){
	  	$sql1.=" AND r.fk_user IN(".implode(',', $TabUser).")";
      }
		
	}
	
	//echo $sql1;
  	$ATMdb->Execute($sql1);
    
    while ($row = $ATMdb->Get_line()) {
    	switch($row->type){
			case 'rttcumule' : 
				$color=6;
				break;
			case 'rttnoncumule':
				$color=8;
				break;
			case 'conges':
				$color=10;
				break;
			case 'maladiemaintenue':
				$color=12;
				break;
			case 'maladienonmaintenue':
				$color=14;
				break;
			case 'maternite':
				$color=16;
				break;
			case 'paternite':
				$color=18;
				break;
			case 'nonremuneree':
				$color=15;
				break;
			case 'accidentdetravail':
				$color=2;
				break;
			case 'maladieprofessionnelle':
				$color=4;
				break;
			case 'congeparental':
				$color=19;
				break;
			case 'accidentdetrajet':
				$color=17;
				break;
			case 'mitempstherapeutique':
				$color=0;
				break;
    	}

      $ret['events'][] = array(
        $row->rowid,
        $row->libelle." ".$row->name.' '.$row->firstname,
        php2JsTime(mySql2PhpTime($row->date_debut)),
        php2JsTime(mySql2PhpTime($row->date_fin)),
        1,//$row->isAllDayEvent,
        0, //more than one day event
        //$row->InstanceType,
        $row->fk_user,//Recurring event,
        $color,//$row->color,
        1,//editable
        "absence.php?id=".$row->rowid."&action=view",//$row->location,
        '',//$attends
      );
	  
	  }  
	  
	  //récupération des jours fériés 
	$sql2=" SELECT DISTINCT * FROM  llx_rh_absence_jours_feries
	 WHERE entity=".$conf->entity;
	 //echo $sql2;
  	 $ATMdb->Execute($sql2);
   		
     while ($row = $ATMdb->Get_line()) {
		  switch($row->moment){
			case 'apresmidi' : 
				$moment="Après-midi";
				break;
			case 'matin':
				$moment="Matin";
				break;
			case 'allday':
				$moment="Toute la journée";
				break;
    	}
	      $ret['events'][] = array(
	        $row->rowid,
	        "Férié ".$moment." ".$row->commentaire,
	        php2JsTime(mySql2PhpTime($row->date_jourOff)),
	       	php2JsTime(mySql2PhpTime($row->date_jourOff)),
	        1,//$row->isAllDayEvent,
	        0, //more than one day event
	        //$row->InstanceType,
	        0,//Recurring event,
	        1,//$row->color,
	        1,//editable
	        "joursferies.php?idJour=".$row->rowid."&action=view",//$row->location,
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

