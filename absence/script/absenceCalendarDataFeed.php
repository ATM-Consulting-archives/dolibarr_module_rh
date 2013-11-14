<?php

require('../config.php');
include_once("../../rhlibrary/wdCalendar/php/functions.php");
$ATMdb=new TPDOdb;

$method = $_GET["method"];
switch ($method) {
    case "list":
	       	$ret = listCalendar($ATMdb, $_REQUEST["showdate"], $_REQUEST["viewtype"], $_REQUEST['idUser'], $_REQUEST['idGroupe'], $_REQUEST['typeAbsence']);

        break; 

}
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $date_start, $date_end, $idUser=0, $idGroupe=0, $typeAbsence='Tous'){

  global $conf,$user;
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = _justDate($date_start);
  $ret["end"] =  _justDate($date_end);
  $ret['error'] = null;
  
  
  	$TJourFerie=getJourFerie($ATMdb);
	$ret['events'] = array_merge($ret['events'], $TJourFerie); 
  
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
	        utf8_encode($row->name.' '.$row->firstname).' : '.$row->libelle,
	        _justDate($row->date_debut),
	        _justDate($row->date_fin),
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
	  
	
      
	  
	if($conf->agenda->enabled) {
	    $TAgenda=getAgendaEvent($ATMdb, $date_start, $date_end);
		$ret['events'] = array_merge($ret['events'], $TAgenda); 
		//$ret['events'] = $TAgenda;
		
	}  
	

  return $ret;
}

function _justDate($date,$frm = 'Y-m-d') {
	return date($frm, strtotime($date));
}

function getJourFerie(&$ATMdb) {
global $conf;	
	
	$Tab=array();
		  //récupération des jours fériés 
	$sql2=" SELECT moment,commentaire,date_jourOff,rowid FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
	 WHERE entity IN (0,".$conf->entity.")";
	 //AND date_jourOff <='".php2MySqlTime($ed)."' AND date_jourOff >='". php2MySqlTime($sd)."' ";
	 //echo $sql2;
  	 $ATMdb->Execute($sql2);
   		
     while ($row = $ATMdb->Get_line()) {
		  switch($row->moment){
			case 'apresmidi' : 
				$moment="Fermé l'après-midi";
				break;
			case 'matin':
				$moment="Fermé le matin";
				break;
			case 'allday':
				$moment="Jour férié";
				break;
    	}
	      $Tab[] = array(
	        100000+$row->rowid,
	        $moment." ".$row->commentaire,
	        _justDate($row->date_jourOff),
	       	_justDate($row->date_jourOff),
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
	
	return $Tab;
	
}

function listCalendar(&$ATMdb, $day, $type, $idAbsence, $idGroupe, $typeAbsence){
  	
  /*$phpTime = js2PhpTime($day);
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
	default:  
	
		$st = strtotime( date('Y-m-01', strtotime($day)) );
		$et = strtotime( date('Y-m-t', strtotime($day)) );
	  
  }*/
  
  
  $st = date('Y-m-01', strtotime($day)) ;
  $et = date('Y-m-t', strtotime($day)) ;
	  
  

	return listCalendarByRange($ATMdb, $st, $et, $idAbsence, $idGroupe, $typeAbsence);
}

function getAgendaEvent(&$ATMdb, $date_start, $date_end, $socid=0, $actioncode='', $pid=0, $status='', $filtera='', $filterd='',$filtert='') {
global $user;
			
	$sql = 'SELECT a.id,a.label,';
	$sql.= ' a.datep,';
	$sql.= ' a.datep2,';
	$sql.= ' a.datea,';
	$sql.= ' a.datea2,';
	$sql.= ' a.percent,';
	$sql.= ' a.fk_user_author,a.fk_user_action,a.fk_user_done,';
	$sql.= ' a.priority, a.fulldayevent, a.location,';
	$sql.= ' a.fk_soc, a.fk_contact,';
	$sql.= ' ca.code';
	$sql.= ' FROM ('.MAIN_DB_PREFIX.'c_actioncomm as ca,';
	$sql.= " ".MAIN_DB_PREFIX.'user as u,';
	$sql.= " ".MAIN_DB_PREFIX."actioncomm as a)";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON a.fk_soc = sc.fk_soc";
	$sql.= ' WHERE a.fk_action = ca.id';
	$sql.= ' AND a.fk_user_author = u.rowid';
	$sql.= ' AND a.entity IN ('.getEntity().')';
	if ($actioncode) $sql.=" AND ca.code='".$db->escape($actioncode)."'";
	if ($pid) $sql.=" AND a.fk_project=".$db->escape($pid);
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND (a.fk_soc IS NULL OR sc.fk_user = " .$user->id . ")";
	if ($user->societe_id) $sql.= ' AND a.fk_soc = '.$user->societe_id; // To limit to external user company
	
	
    $sql.= " AND (";
    $sql.= " (datep BETWEEN '".$date_start."'";
    $sql.= " AND '".$date_end."')";
    $sql.= " OR ";
    $sql.= " (datep2 BETWEEN '".$date_start."'";
    $sql.= " AND '".$date_end."')";
    $sql.= " OR ";
    $sql.= " (datep < '".$date_start."'";
    $sql.= " AND datep2 > '".$date_end."')";
    $sql.= ')';

    if ($type) $sql.= " AND ca.id = ".$type;
	if ($status == 'done') { $sql.= " AND (a.percent = 100 OR (a.percent = -1 AND a.datep2 <= '".$db->idate($now)."'))"; }
	if ($status == 'todo') { $sql.= " AND ((a.percent >= 0 AND a.percent < 100) OR (a.percent = -1 AND a.datep2 > '".$db->idate($now)."'))"; }
	if ($filtera > 0 || $filtert > 0 || $filterd > 0)
	{
	    $sql.= " AND (";
	    if ($filtera > 0) $sql.= " a.fk_user_author = ".$filtera;
	    if ($filtert > 0) $sql.= ($filtera>0?" OR ":"")." a.fk_user_action = ".$filtert;
	    if ($filterd > 0) $sql.= ($filtera>0||$filtert>0?" OR ":"")." a.fk_user_done = ".$filterd;
	    $sql.= ")";
	}
	// Sort on date
	$sql.= ' ORDER BY datep';
	
	
	$ATMdb->Execute($sql);
	$Tab = $ATMdb->Get_All();
	
	$TEvent=array();
	
	foreach($Tab as $row) {
		
		 if(empty($row->datep2)) $row->datep2 = date('Y-m-d H:i:s', strtotime($row->datep) + (60 * 30) ) ; // 1/2h
		
		
		 $TEvent[] = array(
	        200000+$row->id
	        ,$row->label
	        ,_justDate( $row->datep)
	        ,_justDate( $row->datep2)
	        ,0
	        ,0, //more than one day event
	        //$row->InstanceType,
	        0,//Recurring event,
	        -1,//$row->color,
	        1,//editable
	        '/comm/action/fiche.php?id='.$row->id  //dol_build_path('/comm/action/fiche.php?id='.$row->id)
	        ,
	        '',//$attends
	     );
	  	
		
	}
	
	return $TEvent;
}
