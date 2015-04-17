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
		
			case 'sendByMail':
				
				_send_by_mail($ATMdb, unserialize(base64_decode( $_POST['serialData'] )));
				
				break;
		
			case 'save':
				_genererRapport($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['type'], $_REQUEST['idImport'], 'view', true);
				break;
			default:
				_genererRapport($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['type'],$_REQUEST['idImport'], 'view');

	}
	
	$ATMdb->close();
	llxFooter();

function _send_by_mail(&$ATMdb, $TLigne) {
global $user,$db;	
	
	llxHeader('', 'Exports Ressources');
	print dol_get_fiche_head(array()  , '', 'Export Ressources');
	
	print_fiche_titre('Exports des ressources', '', 'report.png@report');flush();
	
	$TNumero=array();
	
	dol_include_once('/core/lib/date.lib.php');
	dol_include_once('/ressource/class/numeros_speciaux.class.php');
	dol_include_once('/ressource/class/ressource.class.php');
	$TNumerosSpeciaux = TRH_Numero_special::getAllNumbers($db);
	
	$r1=new TRH_Ressource;
	$r2=new TRH_Ressource;
	
	$TBS=new TTemplateTBS();$html = '';
	foreach($TLigne as $ligne) {
		
		if(!isset($TNumero[$ligne['numero']])) {
			$TNumero[$ligne['numero']] = true;
			$email = $ligne['email'];
			//var_dump($_POST);
			$t_debut = Tools::get_time($_POST['date_debut']);
			$t_fin = Tools::get_time($_POST['date_fin']);
			
			$TLine=array();
			
			$r1->load_by_numId($ATMdb, $ligne['numero']);		
			$r2->load($ATMdb, $r1->fk_rh_ressource);
			
			$ATMdb->Execute("SET NAMES 'utf8'");
			
			$total = $duree_total_externe = $duree_total_interne = 0;
			$mail='';
			$sql=" SELECT date_appel, date_facture,num_appele, volume_reel,type_appel, montant_euros_ht
			FROM ".MAIN_DB_PREFIX."rh_evenement_appel 
			WHERE idImport='".$_POST['idImport']."' AND num_gsm='".$ligne['numero']."' AND date_appel BETWEEN '".date('Y-m-d 00:00:00',$t_debut)."' AND '".date('Y-m-d 23:59:59',$t_fin)."'
			ORDER BY date_appel";
			//print $sql;
			$Tab = $ATMdb->ExecuteAsArray($sql);
			foreach($Tab as $row) {
				
				$t_facture = strtotime($row->date_facture);
				
				$montant_ligne = $row->montant_euros_ht;
				
				if(strpos($row->volume_reel,':')!==false) {
					
					list($hh,$mm,$ss) = explode(':', $row->volume_reel);
					$duree = convertTime2Seconds($hh,$mm,$ss);

					
					if(in_array($row->num_appele, $TNumerosSpeciaux)) { //non facturé
						$duree_total_interne+=$duree;
						$montant_ligne=0;
					}
					else {
						$duree_total_externe+=$duree;
					}
					
				}
				else{
					$row->volume_reel='';
				}
				
				
				$t_appel = strtotime($row->date_appel);
				
				$total+=$montant_ligne;
				
				if($row->montant_euros_ht>0 || $conf->global->RH_RESSOURCE_SHOW_EMPTY_LINE__IN_REPORT) {
					$TLine[]=array(
						'date_appel'=> date('d/m/Y', $t_appel)
						,'heure_appel'=> date('H:i:s', $t_appel)
						,'numero'=>$row->num_appele
						,'type'=>$row->type_appel
						,'duree'=>$row->volume_reel
						,'cout'=>($montant_ligne>0 ? price($montant_ligne) : '')
					);
				}
			}
			
			$financement = isset($r2->financement) ? $r2->financement : 0;
			
			$mail.=$TBS->render('tpl/mailExportRessource.tpl.php'
				,array(
					'line'=>$TLine
				)
				,array(
					'card'=>array(
						'username'=>$ligne['nom']
						,'date_facture'=>date('d/m/Y', $t_facture)
						,'gsm'=>$ligne['numero']
						,'total'=>price(round($total,2)).' €'
						,'total_financement'=>price(round($financement,2)).' €'
						,'total_all'=>price(round($total+$financement,2)).' €'
						,'duree_total_interne'=>convertSecondToTime($duree_total_interne,'all')
						,'duree_total_externe'=>convertSecondToTime($duree_total_externe,'all')
					)
					,'view'=>array(
						'mode'=>$mode
					)
				)
			);	
			
			$html.=$mail.'<hr />';
			
			if(!isset($_POST['debugMode'])) {
				
				$from = empty($conf->global->RH_USER_MAIL_SENDER)?'conso-tel@cpro.fr':$conf->global->RH_USER_MAIL_SENDER;
				
				$r=new TReponseMail($from, $email, "Etat de la facturation hors forfait pour votre mobile", $mail);
				$r->send(true, 'utf8');
				
				print "Email envoyé à $email<br :>"; flush();	
				
			}
			
			
		}
		
		
			
	}
	if(isset($_POST['debugMode'])) print $html;
	llxFooter();
}

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
		{
			$TIdRessource[$row->rowid] = $idVoiture;
		}
		else if (strtolower($row->nom)=='orange')
			{$TIdRessource[$row->rowid] = $idOrange;}
		else if (strtolower($row->nom)=='total') 
			{$TIdRessource[$row->rowid] = $idTotal;}
		else if (strtolower($row->nom)=='euromaster') 
			{$TIdRessource[$row->rowid] = $idVoiture;}
		else {$TIdRessource[$row->rowid] = $idVoiture;}
	}
	
    dol_include_once('/ressource/lib/ressource.lib.php');
    $TIdFacture = getFactures($ATMdb, $type);
	
	print dol_get_fiche_head(array()  , '', 'Export Ressources');
	
	print_fiche_titre('Exports des ressources', '', 'report.png@report');
	
	$template = './tpl/exportsRessource.tpl.php';
	$npm = true;
	$sendMail=false;
	
	if($boutonGenerer){

        if(stripos($TType[$type],'orange')!==false) $TLignes = _exportOrange2($ATMdb, $date_debut, $date_fin, $conf->entity, $idImport);
        else $TLignes = _exportVoiture($ATMdb, $date_debut, $date_fin, $conf->entity, $type, $TIdRessource[$type], $idImport);
        
		
		if(isset($_REQUEST['DEBUG']))var_dump($TLignes);
		
		if(stripos($TType[$type],'orange')!==false) {
			$TLines = array();
		
			foreach($TLignes as $line) {
				foreach($line as $line_niveau2)
					foreach($line_niveau2 as $line_niveau3)
						$TLines[] = $line_niveau3;
			}
	
			$TLignes = $TLines;
	
			$npm = false;
			$sendMail=true;
		}
		else{
			unset($TLignes[0]);	
		}
		
		
		 
		
	}else{
		$date_debut = strtotime(date("Y-m-01"));
		$date_fin = strtotime(date("Y-m-t"));
	}
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	$form->Set_typeaff('new');
	

	$TBS=new TTemplateTBS();
	print $TBS->render($template
		,array(
			'ligne'=>$TLignes
		)
		,array(
			'exports'=>array(
				'date_debut'=>$form->calendrier('Date de début', 'date_debut', $date_debut,12, 10)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', $date_fin, 12,10)
				,'type'=>$form->combo('Fournisseur', 'type',$TType, $type)
				,'urlFacture'=>$form->hidden('urlFacture',dol_buildpath('/ressource/script/loadListeFactures.php?fk_fournisseur=',2) )
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

	
	if($boutonGenerer){
		if($npm) {
		
		 print "TotalTVA = ".$montantTVA."<br/>";
		 print "TotalHT = ".$montantHT."<br/>";
		 print "TotalTTC = ".$montantTTC."<br/>";
		if(isset($_REQUEST['DEBUG']))print "Ecart : ".$total;
		
		?>
		<br />
		<form name="downbut" style="text-align:center; display:inline;" action="<?php echo dol_buildpath('/report/downloadFile.php',2); ?>" method="POST">
			<input type="hidden" name="url" value="<? echo $url ?>" />
			<input type="hidden" name="typeFile" value="ressource" />
			<input type="hidden" name="filename" value="Export_ressource.pnm" />
			<input type="submit" class="button" value="Télécharger" />
		</form>
		<?php
		}
		
		?>
		<form name="downExcel" style="text-align:center; display:inline;" action="<?php echo dol_buildpath('/report/report.php',2); ?>" method="POST">
			<input type="hidden" name="serialData" value="<?=base64_encode(serialize($TLignes)) ?>" />
			<input type="hidden" name="format" value="ExcelTBS" />
			<input type="hidden" name="rapport" value="ExportRessource" />
			
			<input type="submit" class="button" value="Télécharger en Excel" />
		</form>
		
		<?php
		
		if($sendMail) {
		?>
		<form name="downMail" style="text-align:center; display:inline;" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
			<input type="hidden" name="action" value="sendByMail" />
			<input type="hidden" name="date_debut" value="<?php echo $date_debut ?>" />
			<input type="hidden" name="date_fin" value="<?php echo $date_fin ?>" />
			<input type="hidden" name="idImport" value="<?php echo $idImport ?>" />
			<input type="hidden" name="serialData" value="<?=base64_encode(serialize($TLignes)) ?>" />
			<input type="submit" class="button" value="Envoyer par mail" />
			<input type="checkbox" name="debugMode" value="1" checked="checked" /> Ne pas envoyer, juste afficher pour test
		
		</form>
		<?php
		
		}
		
		print '<br /></div>';
		
		
	}
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
