<?php

require('config.php');
include_once("../rhlibrary/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;

$method = $_GET["method"];
switch ($method) {
    case "list":
	       	$ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], $_GET['idUser'], $_GET['idGroupe'], $_GET['typeAbsence']);

        break; 

}
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idUser=0, $idGroupe=0, $typeAbsence='Tous'){

  global $conf,$user;
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = php2JsTime($sd);
  $ret["end"] = php2JsTime($ed);
  $ret['error'] = null;

  
  try{

	if($user->rights->absence->myactions->voirToutesAbsences){		//si on a le droit de voir toutes les absences
		
		if($idUser==0&&$idGroupe==0){	//on affiche toutes les absences 
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, r.libelle,  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat 
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r, `".MAIN_DB_PREFIX."user` as u 
	  		WHERE r.fk_user=u.rowid";
	  		if($typeAbsence!='Tous'){
	  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
	  		}
	  		//" AND (r.date_debut <= '".php2MySqlTime($ed)."' AND r.date_fin >='". php2MySqlTime($sd)."') ";  
	      
	  	}
	  	else if($idUser==0){		//on recherche un groupe
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, r.libelle,  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat 
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r, `".MAIN_DB_PREFIX."user` as u, `".MAIN_DB_PREFIX."usergroup_user` as g
	  		WHERE r.fk_user=u.rowid AND u.rowid=g.fk_user AND g.fk_usergroup=".$idGroupe; 
			if($typeAbsence!='Tous'){
	  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
	  		}
	 		//" AND (r.date_debut <= '".php2MySqlTime($ed)."' AND r.date_fin >='". php2MySqlTime($sd)."')";
	  	}
	  	else if($idGroupe==0){		//on recherche un utilisateur
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, r.libelle,  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat 
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r, `".MAIN_DB_PREFIX."user` as u, `".MAIN_DB_PREFIX."usergroup_user` as g
	  		WHERE r.fk_user=u.rowid AND u.rowid=g.fk_user AND u.rowid=".$idUser;
			if($typeAbsence!='Tous'){
	  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
	  		}
			//" AND (date_debut <= '".php2MySqlTime($ed)."' AND date_fin >='". php2MySqlTime($sd)."' ) "
	      
	  	}
	  	else{		//on recherche un groupe et un utilisateur
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, r.libelle,  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat 
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r, `".MAIN_DB_PREFIX."user` as u
	  		WHERE r.fk_user=u.rowid AND u.rowid=".$idUser;
			if($typeAbsence!='Tous'){
	  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
	  		}
	  		//" AND (date_debut <= '".php2MySqlTime($ed)."' AND date_fin >='". php2MySqlTime($sd)."' )";
	     
	  	}
		$sql1.= " AND u.entity IN (0,".$conf->entity.") ";
	}
	else{ //on ne peut voir que ses propres absences
		$sql1="SELECT DISTINCT r.rowid as rowid, r.libelle,  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat 
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r, `".MAIN_DB_PREFIX."user` as u
	  		WHERE r.fk_user=u.rowid AND u.rowid=".$user->id;
			if($typeAbsence!='Tous'){
	  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
	  		}
			//" AND (date_debut <= '".php2MySqlTime($ed)."' AND date_fin >='". php2MySqlTime($sd)."' )";
	      
	}
  	

    
   
  	$ATMdb->Execute($sql1);
    
   /* while ($row = $ATMdb->Get_line()) {
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
    	}*/
    while ($row = $ATMdb->Get_line()) {
    				
		$idAbs[]=$row->rowid;
    	switch($row->etat){
			case 'Avalider' : 
				$color=6;
				break;
			case 'Refusee':
				$color=14;
				break;
			case 'Validee':
				$color=8;
				break;
		}
		
	     $ret['events'][] = array(
	        $row->rowid,
	        htmlentities($row->name, ENT_COMPAT , 'ISO8859-1').' '.htmlentities($row->firstname, ENT_COMPAT , 'ISO8859-1')." : ".$row->libelle,
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
	        $row->fk_user
	      );
	  }  
	  
	  //récupération des jours fériés 
	$sql2=" SELECT DISTINCT * FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
	 WHERE entity IN (0,".$conf->entity.")";
	 //AND date_jourOff <='".php2MySqlTime($ed)."' AND date_jourOff >='". php2MySqlTime($sd)."' ";
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

function listCalendar(&$ATMdb, $day, $type, $idAbsence, $idGroupe, $typeAbsence){
  	
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
  /*$ret=array();
  $ret=listCalendarByRange($ATMdb, $st, $et, $idAbsence, $idGroupe);*/
  return listCalendarByRange($ATMdb, $st, $et, $idAbsence, $idGroupe, $typeAbsence);
}

