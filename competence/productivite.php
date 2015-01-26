<?php
	require('config.php');
	require('./class/productivite.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	dol_include_once('/competence/lib/competence.lib.php');
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$productivite = new TRH_productivite;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'save':
				
				$productivite->load($ATMdb, $_REQUEST['id']);
				$productivite->set_values($_REQUEST);
				
				$mesg = '<div class="ok">Indice de productivité enregistré avec succès</div>';
				
				$productivite->save($ATMdb);
				$productivite->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite, 'view');
				break;
			
			case 'delete':
				$productivite->load($ATMdb, $_REQUEST['id']);
				$productivite->delete($ATMdb, $_REQUEST['id']);
				
				?>
					<script>
					
						document.location.href="<?php echo dol_buildpath("/competence/productivite_liste.php", 2) ?>"
					
					</script>
				<?php

				break;
			
			case 'view':
				$productivite->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite, 'view');
				break;
			
			case 'edit':
				$productivite->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite);
				break;
			
			case 'stat':
				_stat($ATMdb, $productivite);
				break;
			default:
				_fiche($ATMdb, $productivite);
				break;
			
		}
		
	}
	
	
	function _stat(&$ATMdb,& $productivite) {
		
		global $conf, $langs, $db;
		
		dol_include_once('/core/class/html.form.class.php');
		
		if(GETPOST('date_start') != '' ) {
			$date_start = date('Y-m-d', Tools::get_time(GETPOST('date_start')));
		}
		else{
			$date_start = date('Y-m-01');
		}
		
		if(GETPOST('date_end') != '' ) {
			$date_end = date('Y-m-d', Tools::get_time(GETPOST('date_end')));
		}
		else{
			$date_end = date('Y-m-t');
		}
		
		$fk_group = GETPOST('fk_group', 'int');
		
		llxHeader('','Statistique de productivité');
		
		print_fiche_titre("Statistiques productivité");
		
		$formATM=new TFormCore('auto', 'formStat');
		echo $formATM->hidden('action', 'stat');
		
		echo $formATM->calendrier('Début', 'date_start', strtotime( $date_start ) );
		echo $formATM->calendrier('Fin', 'date_end',strtotime( $date_end ));
		
		$form=new Form($db);
		echo $form->select_dolgroups($fk_group,'fk_group',1);
		
		echo $formATM->btsubmit('Go', 'bt_go');
		
		$formATM->end();
		
		
		$sql="SELECT u.rowid as idUser, p.rowid as idProductivite ,p.indice, SUM(pi.chiffre_realise) as 'nb'
		FROM ".MAIN_DB_PREFIX."rh_productivite_indice pi 
			INNER JOIN ".MAIN_DB_PREFIX."rh_productivite p ON (p.rowid=pi.fk_productivite)
			INNER JOIN ".MAIN_DB_PREFIX."user u ON (pi.fk_user=u.rowid)
			";
		if($fk_group>0) {
			$sql.="	LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user ugu ON (u.rowid=ugu.fk_user) ";
		}	
		 
		$sql.=" WHERE date_indice BETWEEN '$date_start' AND '$date_end' ";
		
		if($fk_group>0) {
			$sql.=" AND ugu.fk_usergroup IN (".$fk_group.") ";
		}
		
		$sql.=" GROUP BY u.rowid, p.rowid
		";
			
		$Tab = $ATMdb->ExecuteAsArray($sql);
		
		$TData = array();
		foreach ($Tab as $row) {
			
			$TData[$row->idUser][$row->idProductivite] = $row->nb;
			
		}
		
		$sql="SELECT rowid, indice FROM ".MAIN_DB_PREFIX."rh_productivite ORDER BY indice";
		$TProductivite = TRequeteCore::get_keyval_by_sql($ATMdb, $sql, 'rowid', 'indice');
		
		print '<table class="border" width="100%"><tr class="liste_titre"><td>'.$langs->trans('Users').'</th>';
		
		foreach($TProductivite as $idProductivite=>$label) {
				print '<th>'.$label.'</th>';
		}
		
		print '<th>Rendement</th><th>Productivité</th>';
		
		print '</td>';
		
		$idFacturable = __val($conf->global->RH_PRODUCTIVITE_FACTURABLE, 10, 'int', true);
		$idFacturee = __val($conf->global->RH_PRODUCTIVITE_FACTUREE, 7, 'int', true);
		$idImproductivite = __val($conf->global->RH_PRODUCTIVITE_IMPRODUCTIVITE, 9, 'int', true);
		
		$TTotal = array();
		foreach($TData as $idUser=>$TP) {
			
			$u=new User($db);
			$u->fetch($idUser);
			
			print '<tr><td>'.$u->getNomUrl(1).'</td>';
			
			foreach($TProductivite as $idProductivite=>$label) {
				
				$nb = (empty($TP[$idProductivite]) ? 0 : $TP[$idProductivite] );
				@$TTotal[$idProductivite]+=$nb;
				
				print '<td>'.$nb.'</td>';
				
			}
			
			$rendement = ($TP[$idFacturee]>0 ? $TP[$idFacturable] / $TP[$idFacturee] : 0);
			$productivite = ($TP[$idFacturable]>0 ? $TP[$idFacturee] / ($TP[$idFacturable]+$TP[$idImproductivite] ): 0);
			
			@$TTotal['rendement']+= $rendement;
			@$TTotal['productivite']+= $productivite;
			
			print '<td>'. round( $rendement,2)  .'</td>';
			print '<td>'. round($productivite,2) .'</td>';
			
			print '</tr>';
			
		}
		
		print '<tr><td>Total</td>';
		
		foreach($TProductivite as $idProductivite=>$label) {
			print '<td><strong>'.$TTotal[$idProductivite].'</strong></td>';	
		}
		
		$rendement = ($TTotal[$idFacturee]>0 ? $TTotal[$idFacturable] / $TTotal[$idFacturee] : 0);
		$productivite = ($TTotal[$idFacturable]>0 ? $TTotal[$idFacturee] / ($TTotal[$idFacturable]+$TTotal[$idImproductivite] ): 0);
		
		print '<td><strong>'.round($rendement,2).'</strong></td>';	
		print '<td><strong>'.round($productivite,2).'</strong></td>';	
		
		print '</tr>';
		
		
		print '</table>';	
		
		llxFooter();	
	}
	
	function _fiche(&$ATMdb, $productivite, $mode="edit") {
		
		global $db,$user,$langs,$conf;
		llxHeader('','Données de productivité');
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $productivite->getId());
		echo $form->hidden('action', 'save');
		echo $form->hidden('fk_user', $fuser->id);

		$TBS=new TTemplateTBS();

		print $TBS->render('./tpl/productivite.tpl.php'
			,array()
			,array(
				'user'=>array(
					'id'=>$fuser->id
					,'lastname'=>$fuser->lastname
					,'firstname'=>$fuser->firstname
				)
				,'productivite'=>array(
					'id'=>$productivite->getId()
					,'date_objectif'=>$form->calendrier('', 'date_objectif', $productivite->date_objectif, 12)
					,'indice'=>$form->texte('', 'indice', $productivite->indice, 20,255,'','','à saisir')
					,'label'=>$form->texte('', 'label', $productivite->label, 20,255,'','','à saisir')
					,'objectif'=>$form->texte('', 'objectif', $productivite->objectif, 20,255,'','','à saisir')
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'action'=>$_REQUEST['action']
					,'head'=>dol_get_fiche_head(competencePrepareHead($productivite, 'productivite'),'fiche','Productivité')
					,'onglet'=>dol_get_fiche_head(array(),'','Edition indice de productivité')
				)
				
			)	
			
		);
		
	}
