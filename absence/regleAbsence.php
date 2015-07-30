<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');

	$ATMdb=new TPDOdb;
	$regle=new TRH_RegleAbsence;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$regle->load($ATMdb, GETPOST('id','integer'));
				_fiche($ATMdb,$regle,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$regle->load($ATMdb, GETPOST('id','integer'));
				_fiche($ATMdb,  $regle, 'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				$regle->load($ATMdb, GETPOST('id','integer'));
				$regle->restrictif=0;
				$regle->contigue=0;
				$regle->contigueNoJNT=0;
				$regle->set_values($_REQUEST);				
				$regle->save($ATMdb);
				$mesg = '<div class="ok">' . $langs->trans('ChangesMade') . '</div>';
				_fiche($ATMdb,  $regle,'view');
				break;
			
			case 'view':
				$regle->load($ATMdb, GETPOST('id','integer'));
				_fiche($ATMdb,  $regle,'view');
				break;
		
			case 'delete':
				$regle->load($ATMdb, GETPOST('id','integer'));
				$regle->delete($ATMdb);
				$mesg = '<div class="ok">' . $langs->trans('RuleDeleted') . '</div>';
				_liste($ATMdb, $regle);
			
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$regle->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $regle);
	}
	else {
		 _liste($ATMdb, $regle);
	}
	
	
	$ATMdb->close();
	
	
function _liste(&$ATMdb, $regle) {
	global $langs,$conf, $db, $user;	

	llxHeader('', $langs->trans('HolidaysRules'));
	print dol_get_fiche_head(reglePrepareHead($regle,'regle')  , 'regle', $langs->trans('Rules'));
		
	$r = new TSSRenderControler($regle);
	$sql="SELECT DISTINCT r.rowid as 'ID',r.periode, CONCAT(u.firstname,' ',u.lastname) as 'Utilisateur', g.nom as 'Groupe',
		r.typeAbsence, r.nbJourCumulable , r.restrictif as 'Restrictif', '' as 'Supprimer'
		FROM ".MAIN_DB_PREFIX."rh_absence_regle as r
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (r.fk_user = u.rowid)
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup as g ON (r.fk_usergroup = g.rowid)
		WHERE r.entity IN (0,".$conf->entity.")";
	
	//echo $sql;
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
			'ID'=>'<a href=?id=@ID@&action=view&fk_user='.$user->id.'>@val@</a>'
			,'typeAbsence'=>'<a href=?id=@ID@&action=view&fk_user='.$user->id.'>@val@</a>'
			,'Supprimer'=>"<a onclick=\"if (window.confirm('" . $langs->trans('ConfirmDeleteRule') . "')){href='?id=@ID@&fk_user=".$user->id."&action=delete'};\"><img src='./img/delete.png'></a>"
			
		)
		,'translate'=>array(
			'typeAbsence'=>array(
				'rttcumule'				=> $langs->trans('CumulatedDayOff'),
				'rttnoncumule'			=> $langs->trans('NonCumulatedDayOff'), 
				'conges' 				=> $langs->trans('Holidays'), 
				'maladiemaintenue' 		=> $langs->trans('MaintainedSickness'), 
				'maladienonmaintenue'	=> $langs->trans('NoMaintainedSickness'),
				'maternite'				=> $langs->trans('Maternity'),
				'paternite'				=> $langs->trans('Paternity'), 
				'chomagepartiel'		=> $langs->trans('PartialUnemployment'),
				'nonremuneree'			=> $langs->trans('UnpaidAbsence'),
				'accidentdetravail'		=> $langs->trans('WorkAccident'),
				'maladieprofessionnelle'=> $langs->trans('ProfessionalSickness'),
				'congeparental'			=> $langs->trans('ParentalHoliday'),
				'accidentdetrajet'		=> $langs->trans('TravelAccident'),
				'mitempstherapeutique'	=> $langs->trans('TherapeuticHalftime')
			)
			,'Restrictif'=>array('1'=> $langs->trans('Yes'), '0'=> $langs->trans('No'))
		)
		,'hide'=>array('periode')
		,'type'=>array()
		,'liste'=>array(
			'titre'=> $langs->trans('AbsencesPresencesRequestRulesList')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['ID'])
			,'messageNothing'=> $langs->trans('NoRulesToShow')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'title'=>array(
			'typeAbsence'=> $langs->trans('AbsencePresenceType')
			,'nbJourCumulable'=> $langs->trans('NbPossibleContiguousDays')
		)
		,'eval'=>array(
			'Utilisateur'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "UTF-8")))'
			,'Groupe'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "UTF-8")))'
			,'nbJourCumulable'=>'_periode("@val@", "@periode@")'
		)
		,'orderBy'=>$TOrder
		
	));
	
	?><div class="tabsAction" >
		<a class="butAction" href="?id=<?=$regle->getId()?>&action=new"><?php echo $langs->trans('NewRule'); ?></a>
		<div style="clear:both"></div></div><?php
	$form->end();
	llxFooter();
}	

function _periode($nbJourCumulable, $periode) {
	
	
	if($periode=='ONE')return $nbJourCumulable;
	else return $nbJourCumulable.' / '.TRH_RegleAbsence::$TPeriode[$periode] ;
	
}

function _fiche(&$ATMdb, $regle, $mode) {
	global $langs;
	
	llxHeader('', $langs->trans('AbsenceRule'), '', '', 0, 0);
	
	global $user,$conf;
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('id', $regle->getId());
	echo $form->hidden('action', 'save');
	
	$regle->load_liste($ATMdb);
	
	$TTypeAbsence = array_merge(TRH_TypeAbsence::getTypeAbsence($ATMdb, 'admin'), TRH_TypeAbsence::getTypeAbsence($ATMdb, 'admin', true));
	
	$TBS=new TTemplateTBS();
	$regle->load_liste($ATMdb);
	print $TBS->render('./tpl/regleAbsence.tpl.php'
		,array()
		,array(
			'newRule'=>array(
				'id'=>$regle->getId()
				,'choixApplication'=>$form->radiodiv('','choixApplication',$regle->TChoixApplication, $regle->choixApplication)
				,'choixApplicationViewMode'=>$regle->TChoixApplication[$regle->choixApplication]
				,'fk_user'=>$form->combo('', 'fk_user',$regle->TUser, $regle->fk_user)
				,'fk_group'=>$form->combo('', 'fk_usergroup',$regle->TGroup, $regle->fk_usergroup)
				,'nbJourCumulable'=>$form->texte('', 'nbJourCumulable', $regle->nbJourCumulable,30 ,255,'','','')
				,'typeAbsence'=>$form->combo('', 'typeAbsence',$TTypeAbsence, $regle->typeAbsence)
				,'periode'=>$form->combo('', 'periode',TRH_RegleAbsence::$TPeriode, $regle->periode)
				,'restrictif'=>$form->checkbox1('','restrictif','1',$regle->restrictif==1?true:false)
				,'contigue'=>$form->checkbox1('','contigue','1',$regle->contigue==1?true:false)
				,'contigueNoJNT'=>$form->checkbox1('','contigueNoJNT','1',$regle->contigueNoJNT==1?true:false)
				,'titreRegle'=>load_fiche_titre($langs->trans('AbsencePresenceRule'),'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$user->id
				,'lastname'=>htmlentities($user->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($user->firstname, ENT_COMPAT , 'ISO8859-1')
				,'valideurConges'=>($user->rights->absence->myactions->valideurConges && $estValideur)
				,'enregistrerPaieAbsences'=>($user->rights->absence->myactions->enregistrerPaieAbsences && $estValideur)	
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(reglePrepareHead($regle)  , 'regle', $langs->trans('Rules'))
			)
			,'trad' => array(
				'user' => $langs->trans('User'),
				'group' => $langs->trans('Group'),
				'all' => $langs->trans('AllThis')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
