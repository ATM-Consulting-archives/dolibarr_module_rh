<?php
/**
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012-2013 Florian Henry <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file agefodd/cursus/card.php
 * \ingroup agefodd
 * \brief card of cursus
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die ( "Include of main fails" );

require_once ('../class/agefodd_cursus.class.php');
require_once ('../class/agefodd_formation_cursus.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden ();

$langs->load ( 'agefodd@agefodd' );
$langs->load ( 'companies' );

$action = GETPOST ( 'action', 'alpha' );
$confirm = GETPOST ( 'confirm', 'alpha' );
$id = GETPOST ( 'id', 'int' );
$arch = GETPOST ( 'arch', 'int' );

$sortorder=GETPOST('sortorder','alpha');
$sortfield=GETPOST('sortfield','alpha');
$page=GETPOST('page','int');

if ($page == -1) {
	$page = 0 ;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer) {
	$agf = new Agefodd_cursus ( $db );
	$agf->id = $id;
	$result = $agf->remove ( $user );
	
	if ($result > 0) {
		Header ( "Location: list.php" );
		exit ();
	} else {
		setEventMessage ( $agf->error, 'errors' );
	}
}

/*
 * Actions archive/active
*/
if ($action == 'arch_confirm_delete' && $user->rights->agefodd->creer) {
	if ($confirm == "yes") {
		$agf = new Agefodd_cursus ( $db );
		
		$result = $agf->fetch ( $id );
		
		$agf->archive = $arch;
		$result = $agf->update ( $user );
		
		if ($result > 0) {
			Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
			exit ();
		} else {
			setEventMessage ( $agf->error, 'errors' );
		}
	} else {
		Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
		exit ();
	}
}

/*
 * Action update (Cursus)
*/
if ($action == 'update' && $user->rights->agefodd->creer) {
	if (! $_POST ["cancel"]) {
		$agf = new Agefodd_cursus ( $db );
		
		$result = $agf->fetch ( $id );
		if ($result > 0) {
			$agf->ref_interne = GETPOST ( 'ref_interne', 'alpha' );
			$agf->intitule = GETPOST ( 'intitule', 'alpha' );
			$agf->note_private = GETPOST ( 'note_private', 'alpha' );
			$agf->note_public = GETPOST ( 'note_public', 'alpha' );
			
			$result = $agf->update ( $user );
			
			if ($result > 0) {
				Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
				exit ();
			} else {
				setEventMessage ( $agf->error, 'errors' );
				$action = 'edit';
			}
		} else {
			setEventMessage ( $agf->error, 'errors' );
		}
	} else {
		Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
		exit ();
	}
}

/*
 * Action create (Cursus)
*/

if ($action == 'create_confirm' && $user->rights->agefodd->creer) {
	if (! $_POST ["cancel"]) {
		$agf = new Agefodd_cursus ( $db );
		
		$agf->ref_interne = GETPOST ( 'ref_interne', 'alpha' );
		$agf->intitule = GETPOST ( 'intitule', 'alpha' );
		$agf->note_private = GETPOST ( 'note_private', 'alpha' );
		$agf->note_public = GETPOST ( 'note_public', 'alpha' );
		$result = $agf->create ( $user );
		
		if ($result > 0) {
			if ($url_return)
				Header ( "Location: " . $url_return );
			else
				Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?action=edit&id=" . $result );
			exit ();
		} else {
			setEventMessage ( $agf->error, 'errors' );
		}
	} else {
		Header ( "Location: list.php" );
		exit ();
	}
}


/*
 * Associate training to cursus
*/
if ($action=='addtraining') {
	$training = new Agefodd_formation_cursus ( $db );
	$training->fk_formation_catalogue=GETPOST('training_id','int');
	$training->fk_cursus=$id;
	$result=$training->create($user);
	if ($result < 0) {
		setEventMessage($training->error,'errors');
	}
}

/*
 * Remove training to cursus
*/
if ($action=='confirm_delete_training' && $confirm == "yes" && $user->rights->agefodd->creer) {
	$training = new Agefodd_formation_cursus ( $db );
	$training->id=GETPOST('lineid','int');
	$result=$training->delete($user);
	if ($result < 0) {
		setEventMessage($training->error,'errors');
	}
}

/*
 * View
*/

$title = ($action == 'create' ? $langs->trans ( "AgfMenuCursusNew" ) : $langs->trans ( "AgfMenuCursus" ));
llxHeader ( '', $title );

$form = new Form ( $db );
$formAgefodd = new FormAgefodd( $db );

/*
 * Action create
*/
if ($action == 'create' && $user->rights->agefodd->creer) {
	print_fiche_titre ( $langs->trans ( "AgfMenuCursusNew" ) );
	
	print '<form name="create" action="' . $_SERVER ['PHP_SELF'] . '" method="POST">' . "\n";
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
	print '<input type="hidden" name="action" value="create_confirm">' . "\n";
	
	print '<input type="hidden" name="url_return" value="' . $url_return . '">' . "\n";
	
	print '<table class="border" width="100%">' . "\n";
	
	print '<tr><td width="20%"><span class="fieldrequired">' . $langs->trans ( "AgfRefInterne" ) . '</span></td>';
	print '<td><input name="ref_interne" class="flat" size="50" value=""></td></tr>';
	
	print '<tr><td width="20%"><span class="fieldrequired">' . $langs->trans ( "AgfIntitule" ) . '</span></td>';
	print '<td><input name="intitule" class="flat" size="50" value=""></td></tr>';
	
	print '<tr><td valign="top">' . $langs->trans ( "NotePublic" ) . '</td>';
	print '<td><textarea name="note_public" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';
	
	print '<tr><td valign="top">' . $langs->trans ( "NotePrivate" ) . '</td>';
	print '<td><textarea name="note_private" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';
	
	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" name="importadress" class="butAction" value="' . $langs->trans ( "Save" ) . '"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans ( "Cancel" ) . '">';
	print '</td></tr>';
	print '</table>';
	print '</form>';
} else {
	// Card
	if ($id) {
		$agf = new Agefodd_cursus ( $db );
		$result = $agf->fetch ( $id );
		
		if ($result > 0) {
			$head = cursus_prepare_head ( $agf );
			
			dol_fiche_head ( $head, 'card', $langs->trans ( "AgfMenuCursus" ), 0, 'document' );
			
			// Card in edit mode
			if ($action == 'edit') {
				print '<form name="update" action="' . $_SERVER ['PHP_SELF'] . '" method="post">' . "\n";
				print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
				print '<input type="hidden" name="action" value="update">' . "\n";
				print '<input type="hidden" name="id" value="' . $id . '">' . "\n";
				
				print '<table class="border" width="100%">' . "\n";
				print '<tr><td width="20%">' . $langs->trans ( "Id" ) . '</td>';
				print '<td>' . $form->showrefnav ( $agf, 'id', '', 1, 'rowid', 'id' ) . '</td></tr>';
				
				print '<tr><td  class="fieldrequired">' . $langs->trans ( "AgfRefInterne" ) . '</td>';
				print '<td><input name="ref_interne" class="flat" size="50" value="' . $agf->ref_interne . '"></td></tr>';
				
				print '<tr><td width="20%"><span class="fieldrequired">' . $langs->trans ( "AgfIntitule" ) . '</span></td>';
				print '<td><input name="intitule" class="flat" size="50" value="' . $agf->intitule . '"></td></tr>';
				
				print '<tr><td valign="top">' . $langs->trans ( "NotePublic" ) . '</td>';
				print '<td><textarea name="note_public" rows="3" cols="0" class="flat" style="width:360px;">' . $agf->note_public . '</textarea></td></tr>';
				
				print '<tr><td valign="top">' . $langs->trans ( "NotePrivate" ) . '</td>';
				print '<td><textarea name="note_private" rows="3" cols="0" class="flat" style="width:360px;">' . $agf->note_private . '</textarea></td></tr>';
				
				print '</table>';
				print '</div>';
				print '<table style=noborder align="right">';
				print '<tr><td align="center" colspan=2>';
				print '<input type="submit" class="butAction" value="' . $langs->trans ( "Save" ) . '"> &nbsp; ';
				print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans ( "Cancel" ) . '">';
				print '</td></tr>';
				print '</table>';
				print '</form>';
				
				print '</div>' . "\n";
			} else {
				// Display View mode
				
				/*
				 * Confirm delete
				*/
				if ($action == 'delete') {
					$ret = $form->form_confirm ( $_SERVER ['PHP_SELF'] . "?id=" . $id, $langs->trans ( "AgfDeleteCursus" ), $langs->trans ( "AgfConfirmDeleteCursus" ), "confirm_delete", '', '', 1 );
					if ($ret == 'html')
						print '<br>';
				}
				/*
				 * Confirm archive
				*/
				if ($action == 'archive' || $action == 'active') {
					if ($action == 'archive')
						$value = 1;
					if ($action == 'active')
						$value = 0;
					
					$ret = $form->form_confirm ( $_SERVER ['PHP_SELF'] . "?arch=" . $value . "&id=" . $id, $langs->trans ( "AgfFormationArchiveChange" ), $langs->trans ( "AgfConfirmArchiveChange" ), "arch_confirm_delete", '', '', 1 );
					if ($ret == 'html')
						print '<br>';
				}
				
				/*
				 * Confirm delete
				*/
				if ($action=='delete_training')
				{
					// Param url = id de la ligne stagiaire dans session - id session
					$ret=$form->form_confirm($_SERVER ['PHP_SELF'] . '?id='.$id.'&lineid='.GETPOST('lineid','int'),$langs->trans("AgfRemoveTrainingCursus"),$langs->trans("AgfConfirmRemoveTrainingCursus"),"confirm_delete_training",'','',1);
					if ($ret == 'html') print '<br>';
				}
				
				print '<table class="border" width="100%">';
				
				print '<tr><td width="20%">' . $langs->trans ( "Id" ) . '</td>';
				print '<td>' . $form->showrefnav ( $agf, 'id	', '', 1, 'rowid', 'id' ) . '</td></tr>';
				
				print '<tr><td>' . $langs->trans ( "AgfRefInterne" ) . '</td>';
				print '<td>' . $agf->ref_interne . '</td></tr>';
				
				print '<tr><td width="20%">' . $langs->trans ( "AgfIntitule" ) . '</td>';
				print '<td>' . $agf->intitule . '</td></tr>';
				
				print '<tr><td valign="top">' . $langs->trans ( "NotePublic" ) . '</td>';
				print '<td>' . $agf->note_public . '</td></tr>';
				
				print '<tr><td valign="top">' . $langs->trans ( "NotePrivate" ) . '</td>';
				print '<td>' . $agf->note_private . '</td></tr>';
				
				print "</table>";
				
				print '</div>';
			}
		} else {
			setEventMessage ( $agf->error, 'errors' );
		}
	}
}

/*
 * Actions tabs
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit') {
	if ($user->rights->agefodd->creer) {
		print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans ( 'Modify' ) . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag ( $langs->trans ( "NotAllowed" ) ) . '">' . $langs->trans ( 'Modify' ) . '</a>';
	}
	if ($user->rights->agefodd->creer) {
		print '<a class="butActionDelete" href="' . $_SERVER ['PHP_SELF'] . '?action=delete&id=' . $id . '">' . $langs->trans ( 'Delete' ) . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag ( $langs->trans ( "NotAllowed" ) ) . '">' . $langs->trans ( 'Delete' ) . '</a>';
	}
	if ($user->rights->agefodd->modifier) {
		if ($agf->archive == 0) {
			print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=archive&id=' . $id . '">' . $langs->trans ( 'AgfArchiver' ) . '</a>';
		} else {
			print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=active&id=' . $id . '">' . $langs->trans ( 'AgfActiver' ) . '</a>';
		}
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag ( $langs->trans ( "NotAllowed" ) ) . '">' . $langs->trans ( 'AgfArchiver' ) . '/' . $langs->trans ( 'AgfActiver' ) . '</a>';
	}
}

print '</div>';


if ($action!='edit' && $action!='create') {
	/*
	 * Manage training
	*/
	
	if (empty($sortorder)) $sortorder="ASC";
	if (empty($sortfield)) $sortfield="f.ref";
	
	print '&nbsp';
	
	$training = new Agefodd_formation_cursus ( $db );
	$training->fk_cursus=$agf->id;
	$result = $training->fetch_formation_per_cursus ( $sortorder, $sortfield, $limit, $offset );
	if ($result < 0) {
		setEventMessage ( $training->error, 'errors' );
	}
	$nbcursus = count ( $training->lines );
	
	print_barre_liste($langs->trans("AgfTraining"), $page, $_SERVER['PHP_SELF'], '&id='.$id ,$sortfield, $sortorder, "", $nbcursus);
	
	print '<table class="noborder" width="100%">';
	print '<tr>';
	if ($nbcursus < 1) {
		print '<td style="text-decoration: blink;">' . $langs->trans ( "AgfLimiteNoOne" ) . '</td>';
	} else {
		print '<td>'.$langs->trans ( "AgfTraining" ).' (' . $nbcursus . ')'.'</td>';
	}
	print '</tr>';
	
	if ($nbcursus > 0 ){
		print '<tr class="liste_titre">';
		print_liste_field_titre($langs->trans("AgfFormRef"),$_SERVER['PHP_SELF'],"f.ref",'','','',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("AgfRefInterne"),$_SERVER['PHP_SELF'],"f.ref_interne",'','','',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("AgfIntitule"),$_SERVER['PHP_SELF'],'f.intitule','','','',$sortfield,$sortorder);
		print '<td></td>';
		print "</tr>\n";
		
		$var=true;

		foreach ( $training->lines as $line ) {
			
			$var=!$var;

			if ($action=='delete_training' && $line->id==GETPOST('lineid','int')) {
				print '<tr bgcolor="#d5baa8">';
			}
			else {
				print "<tr $bc[$var]>";
			}
			
			
			print '<td>'.$line->ref.'</td>';
			print '<td>'.$line->ref_interne.'</td>';
			print '<td>'.$line->intitule.'</td>';
			print '<td><a href="' . $_SERVER ['PHP_SELF'] . '?id='.$agf->id.'&action=delete_training&lineid='.$line->id.'"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="stag_remove" alt="'.$langs->trans("AgfModSave").'"></a></td>';
			print "</tr>\n";
		}
	}		
	print "</table>";
	
	if($user->rights->agefodd->modifier) {
		print '<form name="update" action="' . $_SERVER ['PHP_SELF'] . '?id='.$agf->id.'" method="post">' . "\n";
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
		print '<input type="hidden" name="action" value="addtraining">' . "\n";
		
		print '<table class="noborder" width="100%">';
		print '<tr>';
		print '<td>'.$langs->trans('AgfAddTraining');
		print $formAgefodd->select_formation('','training_id','intitule',1, 0, array(), array(' AND c.rowid NOT IN (SELECT fk_formation_catalogue FROM '.MAIN_DB_PREFIX.'agefodd_formation_cursus WHERE fk_cursus='.$id.')'));
		print '<input type="submit" class="butAction" value="' . $langs->trans ( "Add" ) . '"></td>';
		print '</tr>';
		print "</table>";
		print '</form>';
		
	}
	
	print '</div>';
}

llxFooter ();
$db->close ();