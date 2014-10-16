<?php
	require('config.php');
	require('./class/productivite.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$productivite_user = new TRH_productiviteUser;
	$productivite = new TRH_productivite;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'add_indice':
				
				$id_indice = $_REQUEST['fk_indice_prod'];
				
				if($id_indice != 0) {
					
					$productivite->load($ATMdb, $id_indice);
					$TChamps = array(
						'fk_user'=>$_REQUEST['fk_user']
						,'fk_productivite'=>$productivite->rowid
						,'indice'=>$productivite->indice
						,'objectif'=>$productivite->objectif
						,'date_objectif'=>date("Y-m-d H:i:s", $productivite->date_objectif)
					);
					
					$productivite_user->set_values($TChamps);
					$productivite_user->save($ATMdb);
				
				}
				
				_liste($ATMdb, $productivite_user, 'view');
				break;
			
			case 'save':
				
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				$productivite_user->set_values($_REQUEST);
				
				$mesg = '<div class="ok">Grille de salaire enregistrée avec succès</div>';
				
				$productivite_user->save($ATMdb);
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_user, 'view');
				break;
			
			case 'delete':
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				$productivite_user->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Grille de salaire enregistrée avec succès</div>';
				
				$productivite_user->save($ATMdb);
				?>
					<script>
						document.location.href='fiche_type_poste.php?id=<?php echo $_REQUEST['fk_type_poste'] ?>&action=view';
					</script>
				<?php
				break;
			
			case 'view':
				_liste($ATMdb, $productivite_user, 'view');
				break;
			
			case 'edit':
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_user);
				break;
			
			default:
				_fiche($ATMdb, $productivite_user);
				break;
			
		}
		
	}
	
	function _fiche(&$ATMdb, $productivite_user, $mode="edit") {
		
		global $db,$user,$langs,$conf;
		llxHeader('','Données de productivité');
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		$head = user_prepare_head($fuser);
		$current_head = 'productivite';
		dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $productivite_user->getId());
		echo $form->hidden('action', 'save');
		echo $form->hidden('fk_user', $fuser->id);

		$TBS=new TTemplateTBS();

		print $TBS->render('./tpl/productivite_user.tpl.php'
			,array()
			,array(
				'user'=>array(
					'id'=>$fuser->id
					,'lastname'=>$fuser->lastname
					,'firstname'=>$fuser->firstname
				)
				,'productivite_user'=>array(
					'id'=>$productivite_user->getId()
					,'date_objectif'=>$form->calendrier('', 'date_objectif', $productivite_user->date_objectif, 12)
					,'indice'=>$form->texte('', 'indice', $productivite_user->indice, 20,255,'','','à saisir')
					,'objectif'=>$form->texte('', 'objectif', $productivite_user->objectif, 20,255,'','','à saisir')
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'action'=>$_REQUEST['action']
				)
				
			)	
			
		);
		
	}

	function _liste(&$ATMdb, $productivite_user) {
		global $langs, $conf, $db, $user;	
		llxHeader('','Indices de productivité utilisateur');
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		$head = user_prepare_head($fuser);
		dol_fiche_head($head, 'productivite', $langs->trans('Utilisateur'),0, 'user');
		
		// On récupère la liste des indices de productivité existants
		$TIndices = array(0=>"(Sélectionnez un indice)");
		$sql = 'SELECT p.rowid, p.indice, p.label ';
		$sql.= 'FROM '.MAIN_DB_PREFIX.'rh_productivite p ';
		$sql.= 'WHERE p.rowid NOT IN (';
			$sql.= 'SELECT p.rowid ';
			$sql.= 'FROM '.MAIN_DB_PREFIX.'rh_productivite p ';
			$sql.= 'INNER JOIN '.MAIN_DB_PREFIX.'rh_productivite_user pu on (p.rowid = pu.fk_productivite)';
			$sql.= 'WHERE pu.fk_user = '.$_REQUEST['fk_user'];
		$sql.= ')';
		
		$resql = $db->query($sql);
		if($resql) {
			while($res = $db->fetch_object($resql)) {
				$TIndices[$res->rowid] = $res->rowid." : ".$res->label." (".$res->indice.")";
			}
		}
		
		$r = new TSSRenderControler($productivite_user);
		$sql = "SELECT rowid as 'ID', indice as 'Indice', objectif as 'Objectif'";
		$sql.=" FROM ".MAIN_DB_PREFIX."rh_productivite_user";
		$sql.= ' WHERE fk_user = '.$_REQUEST['fk_user'];

		$TOrder = array('rowid'=>'ASC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
					
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
		$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
		
		print '<table width="100%" class="border"><tbody>';
		print '<tr><td width="25%" valign="top">Réf.</td><td>'.$fuser->ref.'</td></tr>';
		print '<tr><td width="25%" valign="top">Nom</td><td>'.$fuser->lastname.'</td></tr>';
		print '<tr><td width="25%" valign="top">Prénom</td><td>'.$fuser->firstname.'</td></tr>';
		print '</tbody></table>';
		print '<br />';
		
		print $form->combo('Indices de productivité disponibles', 'fk_indice_prod',$TIndices, 0);
		
		print $form->hidden('action', 'add_indice');
		print $form->hidden('fk_user', $_REQUEST['fk_user']);
		
		print $form->btsubmit('Ajouter indice', 'add_indice');
		
		print '<br /><br />';
		
		//function btsubmit($pLib,$pName,$plus="", $class='button'){
		
		$r->liste($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				//'Rémunération brute annuelle'=>'<a href="?id=@ID@&action=view&fk_user='.$fuser->id.'">@val@</a>'
				'ID'=>'<a href="'.dol_buildpath("/competence/productivite_user_fiche.php?id=@ID@&action=view&fk_user=".$_REQUEST['fk_user'], 2).'">@val@</a>'
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
			)
			,'translate'=>array(
				
			)
			,'hide'=>array('DateCre', 'fk_user')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Visualisation des indices de productivité de l\'utilisateur'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Aucun type de poste"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			)
			,'title'=>array(
				'type_poste'=>'Type poste'
				,'numero_convention'=>'Numero convention'
				,'descriptif'=>'Descriptif'
			)
			,'search'=>array(
			)
			,'orderBy'=>$TOrder
			
		));
	
	
		$form->end();
		
		_displayChartProductivite($ATMdb);
		
		llxFooter();
	}

	function _displayChartProductivite(&$ATMdb) {
		
		global $conf,$langs;
		
		$langs->load('report@report');
		dol_include_once("/report/class/dashboard.class.php");
		//llxHeader('', '', '', '', 0, 0, array('http://www.google.com/jsapi'));
		
		$title = $langs->trans('Graphiques des primes');
		print_fiche_titre($title, '', 'report.png@report');
		
		$dash=new TReport_dashboard;
		
		$TIndicesuser = _getArrayIndicesuser($_REQUEST['fk_user']);
		
		$TData = array();
		
		foreach($TIndicesuser as $indice_user) {
			$TData[] = array("code" => 'CHIFFRESUSER'
							,'yDataKey' => 'Indice '.$indice_user
							,"sql" => "SELECT DATE_FORMAT(date_indice, \"%Y-%m\" ) AS 'mois'
							, SUM( chiffre_realise ) AS 'Indice ".$indice_user."' 
							FROM ".MAIN_DB_PREFIX."rh_productivite_indice 
							WHERE fk_user=".$_REQUEST['fk_user']."
							AND indice=".$indice_user." 
							GROUP BY `mois`");
		}
		
		$dash->initByData($ATMdb,$TData);

		?><div id="chart_productivite_user" style="height:<?=$dash->hauteur?>px; margin-bottom:20px;"></div><?
				
		$dash->get('chart_productivite_user');
		
	}

	function _getArrayIndicesuser($id_user) {
			
		global $db;
		
		$TIndicesuser = array();
		
		$sql = "SELECT DISTINCT indice ";
		$sql.= "FROM ".MAIN_DB_PREFIX."rh_productivite_indice ";
		$sql.= "WHERE fk_user = ".$id_user;
		$resql = $db->query($sql);
		
		while($res = $db->fetch_object($resql)) {
			
			$TIndicesuser[] = $res->indice;
			
		}
		
		return $TIndicesuser;
		
	}
