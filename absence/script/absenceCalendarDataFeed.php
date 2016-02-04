<?php

require('../config.php');
dol_include_once("/absence/class/absence.class.php");
dol_include_once("/valideur/class/valideur.class.php");
dol_include_once("/rhlibrary/wdCalendar/php/functions.php");

$ATMdb=new TPDOdb;

$method = $_GET["method"];
switch ($method) {
    case "list":
	       	$ret = listCalendar($ATMdb, $_REQUEST["showdate"], $_REQUEST["viewtype"], $_REQUEST['idUser'], $_REQUEST['idGroupe'], $_REQUEST['typeAbsence']);

        break; 
		
	case 'add':
			
			$ret = addCalendar($ATMdb, $_REQUEST['CalendarTitle'], $_REQUEST['CalendarStartTime'], $_REQUEST['CalendarEndTime'], $_REQUEST['isAllDayEvent']);
			
		break;

}
echo json_encode($ret); 

function addCalendar(&$ATMdb, $title, $date_start,$date_end, $fulldayevent=1, $actionCode=50) {
global $db, $user;	
	
	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/cactioncomm.class.php';
	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
	
	
	$t_start = strtotime($date_start);
	$t_end = strtotime($date_end);
	
	$cactioncomm = new CActionComm($db);
	$actioncomm = new ActionComm($db);
	
	$result=$cactioncomm->fetch($actionCode);
	
	$actioncomm->label = $title;
	
	$actioncomm->type_id = $cactioncomm->id;
	$actioncomm->type_code = $cactioncomm->code;
	$actioncomm->priority = 0;
	$actioncomm->fulldayevent = $fulldayevent;
	$actioncomm->location = '';
	$actioncomm->transparency = 1;
	
	$actioncomm->datep = $t_start;
	$actioncomm->datef = $t_end;
	$actioncomm->percentage = 0;
	$actioncomm->duree=$t_end - $t_start;

	$actioncomm->usertodo = $user;

	$idaction=$actioncomm->add($user);
	
	$ret = array();
	  $ret['IsSuccess'] = true;
	  $ret['Msg'] = 'add success';
	  $ret['Data'] = 'url:'.dol_buildpath('/comm/action/fiche.php?action=edit&id='.$idaction,1) ;
	  	
	
	return $ret;
}


function listCalendarByRange(&$ATMdb, $date_start, $date_end, $idUser=0, $idGroupe=0, $typeAbsence = 'Tous'){
  global $conf,$user, $langs;
    
  $ret = array();
  $ret['events'] = array();
  $ret["issort"] =true;
  $ret["start"] = _justDate($date_start);
  $ret["end"] =  _justDate($date_end);
  $ret['error'] = null;
  
  
  	$TJourFerie=getJourFerie($ATMdb, $date_start, $date_end);
	$ret['events'] = array_merge($TJourFerie, $ret['events']); 

  	if($user->rights->absence->myactions->voirToutesAbsences){		//si on a le droit de voir toutes les absences
	  	
	  	if($idUser>0){		//on recherche un groupe et un utilisateur
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle', r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat, r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd ,t.colorId
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
	  			LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
	  		WHERE u.rowid=".$idUser." AND r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";
	  	}
	  	else if($idGroupe>0){		//on recherche un groupe
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle',  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat, r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd ,t.colorId
	  			FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
	  				LEFT JOIN `".MAIN_DB_PREFIX."usergroup_user` as g ON (u.rowid=g.fk_user)
	  				LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
	  		WHERE g.fk_usergroup=".$idGroupe." AND r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";;
	  	}
		else {
			$sql1 = "SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle',  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat , r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd,t.colorId
			  		FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
	  						LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
			  		WHERE r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";
		}

		if($typeAbsence!= 'Tous'){
  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
  		}
	  	
		$sql1.= " AND u.entity IN (0,".$conf->entity.") ";
	}
	else if($user->rights->absence->myactions->voirGroupesAbsences) {
			
		$Tab = $ATMdb->ExecuteAsArray("SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE fk_user=".$user->id);
		$TGroup=array(0);
		foreach($Tab as $row)$TGroup[] = $row->fk_usergroup;		
				
				
		if($idUser>0){		//on recherche un groupe et un utilisateur
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle', r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat, r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd ,t.colorId
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
				LEFT JOIN `".MAIN_DB_PREFIX."usergroup_user` as g ON (u.rowid=g.fk_user)
	  			LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
	  		WHERE u.rowid=".$idUser." AND g.fk_usergroup IN (".implode(',',$TGroup).") AND r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";
	  	}
	  	else if($idGroupe>0){		//on recherche un groupe
	  		$sql1 = "SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle',  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat, r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd ,t.colorId
	  			FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
	  				LEFT JOIN `".MAIN_DB_PREFIX."usergroup_user` as g ON (u.rowid=g.fk_user)
	  				LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
	  		WHERE g.fk_usergroup=".$idGroupe." AND g.fk_usergroup IN (".implode(',',$TGroup).") AND r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";;
	  	}
		else {
			$sql1 = "SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle',  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat , r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd,t.colorId
			  		FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
			  			LEFT JOIN `".MAIN_DB_PREFIX."usergroup_user` as g ON (u.rowid=g.fk_user)
	  						LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
			  		WHERE  g.fk_usergroup IN (".implode(',',$TGroup).") AND r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";
		}

		if($typeAbsence!= 'Tous'){
  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
  		}
	  	
		$sql1.= " AND u.entity IN (0,".$conf->entity.") ";	
		
	}
	else{ //on ne peut voir que ses propres absences
		$sql1="SELECT DISTINCT r.rowid as rowid, t.libelleAbsence as 'libelle',  r.type, u.lastname, u.firstname, r.fk_user, r.date_debut, r.date_fin, r.etat , r.ddMoment, r.dfMoment,t.isPresence, r.date_hourStart, r.date_hourEnd,t.colorId
	  		FROM `".MAIN_DB_PREFIX."rh_absence` as r LEFT JOIN `".MAIN_DB_PREFIX."user` as u ON (r.fk_user=u.rowid)
	  						LEFT JOIN `".MAIN_DB_PREFIX."rh_type_absence` t ON (r.type=t.typeAbsence) 
	  		WHERE u.rowid=".$user->id." AND r.date_debut<='".$date_end."' AND r.date_fin>='".$date_start."'";
			if($typeAbsence != 'Tous'){
	  			$sql1.=" AND r.type LIKE '".$typeAbsence."' ";
	  		}
			//" AND (date_debut <= '".php2MySqlTime($ed)."' AND date_fin >='". php2MySqlTime($sd)."' )";
	      
	}
  	
  	$TRow = $ATMdb->ExecuteAsArray($sql1);
    
	

    foreach($TRow as $row) {
    				
		$idAbs[]=$row->rowid;
    
		if($row->etat=='Refusee' && !$user->rights->absence->myactions->voirAbsenceRefusee){
			continue;
		}
		
		if($row->isPresence==1) {
	
			$color= $row->colorId;
			
			$time_debut_jour = strtotime($row->date_debut);
			$time_fin_jour = strtotime($row->date_fin);
			
	        $moreOneDay=(int)($row->date_debut<$row->date_fin);
	        
	        $t_current = $time_debut_jour;
			while($t_current<=$time_fin_jour) {
				
				
				$timeDebut = strtotime( date('Y-m-d',$t_current).' '.substr($row->date_hourStart,11) ); 
				$timeFin = strtotime(date('Y-m-d',$t_current).' '.substr($row->date_hourEnd,11) ) ; 
				
				$url = "presence.php?id=".$row->rowid."&action=view";//$row->location,
		        	$attends = 'presence';//$attends

				if($user->id!=$row->fk_user && !TRH_valideur_groupe::isValideur($ATMdb, $user->id)) {
					$label = $row->lastname.' '.$row->firstname;
                                }
                                else {

					$label = $row->lastname.' '.$row->firstname.' : '.$row->libelle;
	
                                }
	

				if($moreOneDay) {
					$label.=' du '._justDate($timeDebut,'d/m').' au '._justDate($timeFin,'d/m/Y');
				}
				
				if(mb_detect_encoding($label,'UTF-8', true) === false  ) $label = utf8_encode($label);
				
				$ret['events'][] = array(
			        $row->rowid,
			        $label,
			        _justDate($timeDebut),
			        _justDate($timeFin),
			        0,//$row->isAllDayEvent,
			        0, //more than one day event
			        //$row->InstanceType,
			        $row->fk_user,//Recurring event,
			        $color,//$row->color,
			        0//editable
			        ,$url
			        ,$attends
			        ,$row->fk_user
			      );
				
				$t_current=strtotime('+1day', $t_current);
			}
			
			
		}
		else {
			
			switch($row->etat){
				case 'Avalider' : 
					$color=6;
					break;
				case 'Refusee':
					$color=14;
					break;
				default:
					$color=8;
					break;
			}
			
			$timeDebut = strtotime($row->date_debut);
			$timeFin = strtotime($row->date_fin)+86399; // par défaut 23:59:59
			
			if($row->ddMoment=='apresmidi')$timeDebut += (3600 * 12) ; //+12h
			if($row->dfMoment=='matin')$timeFin -= (3600 * 12) ; //-12h
	

					
			$allDayEvent=(int)($row->ddMoment=='matin' && $row->dfMoment=='apresmidi' || $row->date_debut<$row->date_fin);		
			$moreOneDay=(int)($row->date_debut<$row->date_fin);
			$url = "absence.php?id=".$row->rowid."&action=view";//$row->location,
		        $attends = 'absence';//$attends
				
			if($user->id!=$row->fk_user && !TRH_valideur_groupe::isValideur($ATMdb, $user->id)) {
                     $label = $row->lastname.' '.$row->firstname;
				     $url = '#';
            }
            else {
                     $label = $row->lastname.' '.$row->firstname.' : '.$row->libelle;

            }
			
			if(mb_detect_encoding($label,'UTF-8', true) === false  ) $label = utf8_encode($label);
			
//	var_dump($label, $user->id,$row->fk_user,TRH_valideur_groupe::isValideur($ATMdb, $row->fk_user), '<br>');        
	//	        $label = utf8_encode($row->lastname.' '.$row->firstname).' : '.$row->libelle;
			if($moreOneDay) {
				$label.=' du '._justDate($timeDebut,'d/m').' au '._justDate($timeFin,'d/m/Y');
			}
			
			$ret['events'][] = array(
		        $row->rowid,
		        $label,
		        _justDate($timeDebut),
		        _justDate($timeFin),
		        $allDayEvent,//$row->isAllDayEvent,
		        $moreOneDay, //more than one day event
		        //$row->InstanceType,
		        $row->fk_user,//Recurring event,
		        $color,//$row->color,
		        0//editable
		        ,$url
		        ,$attends
		        ,$row->fk_user
		      );
	        
		}	
		
	  }  
	    
	if($conf->agenda->enabled && $_REQUEST['withAgenda']==1) {
	    $TAgenda=getAgendaEvent($ATMdb, $date_start, $date_end);
		$ret['events'] = array_merge($ret['events'], $TAgenda); 
		//$ret['events'] = $TAgenda;
		
	}  
	

  return $ret;
}

function _justDate($date,$frm = 'm/d/Y H:i') {
	if(is_int($date))$time=$date;
	else $time = strtotime($date);
	
	return date($frm, $time);
}

function getJourFerie(&$ATMdb, $date_start, $date_end) {
	global $conf, $langs;	
	
	$Tab=array();

	$TJF=TRH_JoursFeries::getAll($ATMdb, $date_start, $date_end);
		  //récupération des jours fériés 
	foreach($TJF as $row) {
		  switch($row->moment){
			case 'apresmidi' : 
				$moment= $langs->trans('ClosedTheAfternoon');
				break;
			case 'matin':
				$moment= $langs->trans('ClosedTheMorning');
				break;
			case 'allday':
				$moment= $langs->trans('PublicHoliday');
				break;
    		}
		  
		  
	      $Tab[] = array(
	        100000+$row->rowid,
	        $moment.' : '.$row->commentaire,
	        _justDate($row->date_jourOff),
	       	_justDate($row->date_jourOff),
	        1,//$row->isAllDayEvent,
	        0, //more than one day event
	        //$row->InstanceType,
	        0,//Recurring event,
	        1,//$row->color,
	        0,//editable
	        "joursferies.php?idJour=".$row->rowid."&action=view",//$row->location,
	        '',//$attends
	      );
	  
     }
	
	return $Tab;
	
}

function listCalendar(&$ATMdb, $day, $type, $idAbsence, $idGroupe, $typeAbsence){
  	
  $phpTime = js2PhpTime($day);
  switch($type){
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
	
		$st = strtotime( date('Y-m-01 00:00:00', strtotime($day)));
		$et =  strtotime( date('Y-m-t 23:59:59', strtotime($day)));
	  
  }

  $date_start = date('Y-m-d 00:00:00', $st);
  $date_end = date('Y-m-d 23:59:59', $et);
	  
	//  $ATMdb->debug=true;

	return listCalendarByRange($ATMdb, $date_start, $date_end, $idAbsence, $idGroupe, $typeAbsence);
}

function getAgendaEvent(&$ATMdb, $date_start, $date_end) {
global $user, $conf;
		
	
	$filter=GETPOST("filter",'',3);
	$filtera = GETPOST("userasked","int",3)?GETPOST("userasked","int",3):GETPOST("filtera","int",3);
	$filtert = GETPOST("usertodo","int",3)?GETPOST("usertodo","int",3):GETPOST("filtert","int",3);
	$filterd = GETPOST("userdone","int",3)?GETPOST("userdone","int",3):GETPOST("filterd","int",3);
	$showbirthday = empty($conf->use_javascript_ajax)?GETPOST("showbirthday","int"):1;
	$socid = GETPOST("socid","int",1);
	if ($user->societe_id) $socid=$user->societe_id;
	
	$result = restrictedArea($user, 'agenda', 0, '', 'myactions');

	$canedit=1;
	if (! $user->rights->agenda->myactions->read) return array();
	if (! $user->rights->agenda->allactions->read) $canedit=0;
	if (! $user->rights->agenda->allactions->read || $filter =='mine')  // If no permission to see all, we show only affected to me
	{
	    $filtera=$user->id;
	    $filtert=$user->id;
	    $filterd=$user->id;
	}
	
	$action=GETPOST('action','alpha');
	$pid=GETPOST("projectid","int",3);
	$status=GETPOST("status");
	$type=GETPOST("type");
	$actioncode=GETPOST("actioncode","alpha",3)?GETPOST("actioncode","alpha",3):(GETPOST("actioncode")=="0"?'':(empty($conf->global->AGENDA_USE_EVENT_TYPE)?'AC_OTH':''));
		
		
			
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
	if ($actioncode) $sql.=" AND ca.code=".$ATMdb->quote($actioncode);
	if ($pid) $sql.=" AND a.fk_project=".$ATMdb->quote($pid);
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
		
		 if(empty($row->datep2)) $row->datep2 = date('Y-m-d H:i:s', strtotime($row->datep) + (60 * 60) ) ; // 1h
		
		
		 if($row->code=='AC_OTH_AUTO')$color=5;
		 else $color = -1; 
		
		 $TEvent[] = array(
	        200000+$row->id
	        ,utf8_encode( $row->label )
	        ,_justDate( $row->datep)
	        ,_justDate( $row->datep2)
	        ,$row->fulldayevent
	        ,0, //more than one day event
	        //$row->InstanceType,
	        0,//Recurring event,
	        $color,//$row->color,
	        1,//editable
	        ($canedit ? dol_buildpath('/comm/action/fiche.php?action=edit&id='.$row->id,1) : dol_buildpath('/comm/action/fiche.php?id='.$row->id,1) )  //dol_build_path('/comm/action/fiche.php?id='.$row->id)
	        ,
	        '',//$attends
	     );
	  	
		
	}
	
	return $TEvent;
}
