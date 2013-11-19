<?php

require('config.php');
require('./lib/ressource.lib.php');
include_once("../rhlibrary/wdCalendar/php/functions.php");
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
				$idRessourceSup = $row->fk_rh_ressource ;
			}
		}
		else {$idRessourceSup = 0;}
		
		if ($idRessourceSup!= 0){$type = 0;}
		else {$type = $_REQUEST['type'];}
		//echo $sql.' '.$id;
		$ret = listCalendar($ATMdb, $_POST["showdate"], $_POST["viewtype"], 
					$type, $_REQUEST['id'], $_REQUEST['fk_user'], $_REQUEST['typeEven'],$idRessourceSup);
        
        break;   

}
$ATMdb->close();
echo json_encode($ret); 

function listCalendarByRange(&$ATMdb, $sd, $ed, $idTypeRessource=0, $idRessource = 0,$fk_user = 0, $typeEven = 'all', $idRessourceSup = 0){
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
	$sql = "SELECT e.rowid,  date_debut, date_fin, isAllDayEvent, fk_user, color, type, e.fk_rh_ressource 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
	WHERE ";
	
	$sql .= " 1 ";
	//$sql .= " AND date_debut<='".php2MySqlTime($ed)."' AND date_fin >= '". php2MySqlTime($sd)."' ";
	//$sql .= " `date_debut` between '"
    //  .php2MySqlTime($sd)."' and '". php2MySqlTime($ed)."'";
    
	if ($idTypeRessource!=0) {$sql .= " AND r.fk_rh_ressource_type=".$idTypeRessource;}
	
	if ($idRessource!=0) {
		if ($idRessourceSup!=0){	
			$sql .= " AND (e.fk_rh_ressource=".$idRessource." OR e.fk_rh_ressource=".$idRessourceSup.") ";}
		else {$sql .= " AND e.fk_rh_ressource=".$idRessource;}
	}
	if ($fk_user!=0) {$sql .= " AND e.fk_user=".$fk_user;}
	if ($typeEven && $typeEven!='all') {$sql .= " AND e.type='".$typeEven."'";}
	//echo $sql;
	/*else{
    	$sql.=" AND e.fk_rh_ressource=".$idRessource;
	}//*/
	
	if (!$user->rights->ressource->agenda->viewAgenda){
    	$sql.=" AND e.fk_user=".$user->id;
	}
	//echo '     '.$sql.'      ';
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
	 
	  $moreOneDay=(int)( strtotime($row->date_debut) < strtotime($row->date_fin) );
		
	 
	  //on écrit l'intitulé du calendrier en fonction des données de la fonction
	  $sujet = '';
	  $sujet .= (empty($idRessource) || ($idRessource==0)) ? html_entity_decode($TRessource[$row->fk_rh_ressource], ENT_COMPAT , "ISO8859-1").', ' : '';
	  $sujet .=  ($typeEven=='all') ? $TEvent[$row->type] : '' ;
	  $sujet .= ($fk_user==0) ? ', '.$TUser[$row->fk_user] : ''; 
	  if (empty($sujet)){$sujet=' Emprunt ';}
	  
      $ret['events'][] = array(
       $row->rowid,
        $sujet,
        php2JsTime(mySql2PhpTime($row->date_debut)),
        php2JsTime(mySql2PhpTime($row->date_fin)),
        $row->isAllDayEvent,
        $moreOneDay, //more than one day event
        //$row->InstanceType,
        $row->fk_user,//Recurring event,
        $row->color,
        0,//editable
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

function listCalendar(&$ATMdb, $day, $type, $idTypeRessource=0, $idRessource = 0,$fk_user = 0, $typeEven = 'all', $idRessourceSup = 0){
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
 
	return listCalendarByRange($ATMdb, $st, $et, $idTypeRessource, $idRessource ,$fk_user , $typeEven, $idRessourceSup );
  

}

