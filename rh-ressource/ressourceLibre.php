<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/evenement.class.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$emprunt=new TRH_Evenement;
	$ressource=new TRH_ressource;
	
	$mesg = '';
	$error=false;
	

	/*
	 * Liste
	 */
	 //$ATMdb->db->debug=true;
	 _liste($ATMdb, $ressource);

	
	
	$ATMdb->close();
	llxFooter();
	
	
function _liste(&$ATMdb, &$ressource) {
	global $langs,$conf,$db,$user;	
	llxHeader('','Liste des ressources');
	print dol_get_fiche_head(array()  , '', 'Liste ressources');
	
	
	//récupération des champs spéciaux à afficher.
	$sqlReq="SELECT code, libelle, type, options FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE inliste='oui' ";
	$ATMdb->Execute($sqlReq);
	$TSpeciaux = array();
	
	$TSearch=array();
	while($ATMdb->Get_line()) {
		$TSpeciaux[$ATMdb->Get_field('code')]= $ATMdb->Get_field('libelle');
		if ($ATMdb->Get_field('type')=='liste'){
			$TSearch[$ATMdb->Get_field('code')] = array_combine(explode(';', $ATMdb->Get_field('options')), explode(';', $ATMdb->Get_field('options')));
		}
		else {
			$TSearch[$ATMdb->Get_field('code')] = true;}
	}
	
	
	
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', r.libelle, r.fk_rh_ressource_type, 
		r.numId ";
	foreach ($TSpeciaux as $key=>$value) {
		$sql .= ','.$key.' ';
	}
	if($user->rights->ressource->ressource->createRessource){
		$sql.=", '' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_ressource as r
		LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON ( (e.fk_rh_ressource=r.rowid OR e.fk_rh_ressource=r.fk_rh_ressource) AND e.type='emprunt')
		AND e.date_debut<='".date("Y-m-d")."' AND e.date_fin >= '". date("Y-m-d")."' 
	 LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid )";	
	$sql.=" WHERE  (e.fk_rh_ressource IS NULL) ";
	
	
	if(!$user->rights->ressource->ressource->viewRessource){
		$sql.=" AND e.fk_user=".$user->id;
	}
	$ressource->load_liste_type_ressource($ATMdb);

	$TOrder = array('ID'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelle'=>'<a href="ressource.php?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\" onclick=\"if (confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete'};\"><img src=\"./img/delete.png\"></a>"
		)
		,'translate'=>array(
			'fk_rh_ressource_type'=>$ressource->TType
			)
		,'hide'=>array('DateCre')
		,'type'=>array('libelle'=>'string')
		,'liste'=>array(
			'titre'=>'Liste des ressources libres'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'title'=>array_merge(array(
			'libelle'=>'Libellé'
			,'numId'=>'Numéro Id'
			,'fk_rh_ressource_type'=> 'Type'), $TSpeciaux
		)
		,'search'=>($user->rights->ressource->ressource->searchRessource) ? 		
			array_merge(array(
				'fk_rh_ressource_type'=>array('recherche'=>$ressource->TType)
				,'numId'=>true
				,'libelle'=>true
			), $TSearch)
			: array()
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
	llxFooter();
}	



	
	
