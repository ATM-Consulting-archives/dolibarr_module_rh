<?php
	require('config.php');

	set_time_limit(0);
	ini_set('memory_limit','512M');

	dol_include_once('/ressource/lib/ressource.lib.php');
		
	$langs->load('report@report');
	
	$ATMdb=new TPDOdb;
	
	$mesg = '';
	$error=false;
	
	$action = __get('action','');
	switch($action) {
			case 'save':
				_genererRapport($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['type'], $_REQUEST['idImport'], 'view', true);
				break;
			default:
				_genererRapport($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['type'],$_REQUEST['idImport'], 'view');

	}
	
	$ATMdb->close();
	llxFooter();

function _genererRapport(&$ATMdb, $date_debut, $date_fin, $type, $idImport , $mode, $boutonGenerer = false) {
	global $db, $user, $langs, $conf;
	llxHeader('', 'Exports Ressources');
	
	$TLignes = array();
	
	$idVoiture = getIdType('voiture');
	$idTotal = getIdType('cartetotal');
	$idOrange = getIdType('cartesim');
	
	$TType = array();
	$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TType[$row->rowid] = $row->nom;
		if (strtolower($row->nom)=='parcours')
			{$TIdRessource[$row->rowid] = $idVoiture;}
		else if (strtolower($row->nom)=='orange')
			{$TIdRessource[$row->rowid] = $idOrange;}
		else if (strtolower($row->nom)=='total') 
			{$TIdRessource[$row->rowid] = $idTotal;}
		else if (strtolower($row->nom)=='euromaster') 
			{$TIdRessource[$row->rowid] = $idVoiture;}
		else {$TIdRessource[$row->rowid] = $idVoiture;}
	}
	
	$url ='http://'.$_SERVER['SERVER_NAME'].DOL_URL_ROOT_ALT.'/ressource/script/loadListeFactures.php?fk_fournisseur='.$type.'&mode_retour=autre';	
	if(isset($_REQUEST['DEBUG'])) { print $url.'<br>'; }
	$result = file_get_contents($url);
	$TIdFacture = unserialize($result);  
	
	print dol_get_fiche_head(array()  , '', 'Export Ressources');
	
	print_fiche_titre('Exports des ressources', '', 'report.png@report');
	
	if($boutonGenerer){	
		// ---- Exports
		$url ='http://'.$_SERVER['SERVER_NAME']. DOL_URL_ROOT_ALT."/ressource/script/interface.php?date_debut=".$date_debut."&date_fin=".$date_fin."&get=".$TType[$type]."&fk_fournisseur=".$type."&idTypeRessource=".$TIdRessource[$type]."&entity=".$conf->entity;

		if(!empty($_REQUEST['idImport'])) $url.='&idImport='.$_REQUEST['idImport'];
		
		if(isset($_REQUEST['DEBUG'])) { print $url."&withLogin=1"; }
		$result = file_get_contents($url."&withLogin=1");
		$TLignes = unserialize($result); 
		if(isset($_REQUEST['DEBUG'])) { print_r($TLignes); }
		//print $url.'<br>';
		 
		unset($TLignes[0]);
		//$date_debut = strtotime(str_replace("/","-",$date_debut));
		//$date_fin = strtotime(str_replace("/","-",$date_fin));
	}else{
		$date_debut = strtotime(date("Y-m-01"));
		$date_fin = strtotime(date("Y-m-t"));
	}
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	$form->Set_typeaff('new');
	

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/exportsRessource.tpl.php'
		,array(
			'ligne'=>$TLignes
		)
		,array(
			'exports'=>array(
				'date_debut'=>$form->calendrier('Date de début', 'date_debut', $date_debut,12, 10)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', $date_fin, 12,10)
				,'type'=>$form->combo('Fournisseur', 'type',$TType, $type)
				,'urlFacture'=>$form->hidden('urlFacture', 'http://'.$_SERVER['SERVER_NAME'].DOL_URL_ROOT_ALT.'/ressource/script/loadListeFactures.php?fk_fournisseur=')
				,'idImport'=>$form->combo('Facture', 'idImport',$TIdFacture, $idImport)
				,'action'=>$form->hidden('action','save')
				,'typeDirect'=>$TType[$type]
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)
	);
	
	echo $form->end_form();

 		$total = 0; $montantHT=0;$montantTTC=0;$montantTVA=0;
        foreach($TLignes as $ligne) {
                $credit = $ligne['sens'];
                $montant = $ligne['montant'];
                $type =  $ligne['typeCompte'];

                if($type=='G' || $type=='X') {
                        if($credit=='C') {
                                $montantTTC+=$montant;
                                $total+=$montant;
                        }
                        else {
                                if($ligne['compteGeneral']=='445660') $montantTVA+=$montant;
                                else $montantHT+=$montant;

                                $total-=$montant;
                        }
                }

        }

	 print "TotalTVA = ".$montantTVA."<br/>";
	 print "TotalHT = ".$montantHT."<br/>";
	 print "TotalTTC = ".$montantTTC."<br/>";
	
	if(isset($_REQUEST['DEBUG']))print "Ecart : ".$total;
	
	if($boutonGenerer){
		
		?>
		<br />
		<form name="downbut" style="text-align:center; display:inline;" action="../report/downloadFile.php" method="POST">
			<input type="hidden" name="url" value="<? echo $url ?>" />
			<input type="hidden" name="typeFile" value="ressource" />
			<input type="hidden" name="filename" value="Export_ressource.pnm" />
			<input type="submit" class="button" value="Télécharger" />
		</form>
		
		<form name="downbut" style="text-align:center; display:inline;" action="../report/report.php" method="POST">
			<input type="hidden" name="serialData" value="<?=base64_encode(serialize($TLignes)) ?>" />
			<input type="hidden" name="format" value="ExcelTBS" />
			<input type="hidden" name="rapport" value="ExportRessource" />
			
			<input type="submit" class="button" value="Télécharger en Excel" />
		</form>
		<br /></div>
		<?
	}
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
