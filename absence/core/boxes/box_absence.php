<?php

include_once(DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php");

class box_absence extends ModeleBoxes {

	var $boxcode = "absencebox";
	var $boximg = "absence@absence";
	var $boxlabel;
	var $depends = array("absence");

	var $db;
	var $param;

	var $info_box_head = array();
	var $info_box_contents = array();

	/**
	 *      \brief      Constructeur de la classe
	 */
	function box_absence()
	{
		global $langs;
		$langs->load("boxes");
        $langs->load('absence@absence');

		$this->boxlabel = $langs->trans("Absences");
		
	}

	/**
	 *      \brief      Charge les donnees en memoire pour affichage ulterieur
	 *      \param      $max        Nombre maximum d'enregistrements a charger
	 */
	function loadBox($max = 5)
	{
		global $conf, $user, $langs, $db;

		$this->max=$max;

		

		$text = $langs->trans("Absence à valider",$max);
		$this->info_box_head = array(
				'text' => $text,
				'limit'=> dol_strlen($text)
		);
		
		dol_include_once('/absence/lib/absence.lib.php');
		
		$sql = _getSQLListValidation($user->id);
// var_dump($sql);
	 	if($sql===false) {
			$this->info_box_contents[0][0] = array(
				'td' => 'align="left"',
            	'text' => $langs->trans("ReadPermissionNotAllowed")
            );
			
			return false;
		}
		else {
			
            dol_syslog("BoxAbsence sql=".$sql, LOG_DEBUG);

			$result = $db->query($sql);

			if ($result)
			{
				$num = $db->num_rows($result);
				$now = dol_now();

				$i = 0;


				while ($i < $num)
				{
					$objp = $db->fetch_object($result);

					$picto = 'object_absence@absence';

					$u=new User($db);
					$u->fetch($objp->fk_user);

					$this->info_box_contents[$i]=array(
						
						array('td' => 'align="left"',
						 	'logo' => $picto,
			                    'text' => $objp->libelle. ' - '. dol_print_date( strtotime($objp->date_debut) ).'',
			                    'url' => dol_buildpath('/absence/absence.php', 1).'?action=view&id='.$objp->ID.'&validation=ok')
			                    
						,array('td' => 'align="left"',
			                    'text' => $objp->duree.' '.$langs->trans('days')
			                   )
			             ,array('td' => 'align="left"',
			                    'text' => $u->getFullName($langs)
			                    ,'url'=> dol_buildpath('/user/fiche.php?id='.$u->id,1)
			                   )      
					);

					
                    
					
					$i++;
				}

				if ($num == 0) $this->info_box_contents[$i][0] = array('td' => 'align="center"','text'=>$langs->trans("MessageNothingAbsence"));
			}
			else
			{
				$this->info_box_contents[0][0] = array(	'td' => 'align="left"',
    	        										'maxlength'=>500,
	            										'text' => ($db->error().' sql='.$sql));
			}

		}
		
	}

	function showBox($head = null, $contents = null)
	{
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}

}

