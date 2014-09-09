<?php
require('../config.php');
global $conf,$user;

if(isset($_REQUEST['groupe'])) {
		
		//echo $_REQUEST['type'];
		$TUser = array();
		$ATMdb =new TPDOdb;
		$TUser[0] = $langs->trans('AllThis');	
		if($_REQUEST['groupe']==0){
			$sqlReq="SELECT u.rowid,u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u";
		}else{
			$sqlReq="SELECT u.rowid,u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u,".MAIN_DB_PREFIX."usergroup_user as g  
			WHERE g.fk_user=u.rowid AND g.fk_usergroup=".$_REQUEST['groupe'];
		
		}
		$sqlReq.=" ORDER BY lastname";
		
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$TUser[$ATMdb->Get_field('rowid')] = ucwords(strtolower(html_entity_decode(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))).' '.html_entity_decode(htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1'));
		}
		
		echo json_encode($TUser);
		
		exit();
	}
	