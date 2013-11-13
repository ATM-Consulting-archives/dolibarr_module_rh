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

		if ($user->rights->absence->myactions->valideurConges)	{
			$sql="SELECT a.rowid , a.date_cre as 'DateCre',a.date_debut , a.date_fin, 
		 	a.libelle, ROUND(a.duree ,1) as 'duree', a.fk_user,  a.fk_user, u.login, u.firstname, u.lastname,
		  	a.libelleEtat as 'Statut demande', a.avertissement
			FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
			WHERE u.rowid=a.fk_user";

            $sql.= " ".$db->order('a.date_cre', 'DESC');
            $sql.= " ".$db->plimit($max, 0);
//print $sql;
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

					$this->info_box_contents[$i]=array(
						array('td' => 'align="left" width="16"',
			                    'logo' => $picto,
			                    'url' => dol_buildpath('/absence/absence.php', 1).'?action=view&id='.$objp->rowid)
						,array('td' => 'align="left"',
			                    'text' => utf8_decode($objp->libelle),
			                    'url' => dol_buildpath('/absence/absence.php', 1).'?action=view&id='.$objp->rowid)
			                    
						,array('td' => 'align="left"',
			                    'text' => utf8_decode($objp->duree)
			                   )
			             ,array('td' => 'align="left"',
			                    'text' => utf8_decode($objp->firsname.' '.$objp->name)
			                   )      
					);

					
                    
					
					$i++;
				}

				if ($num == 0) $this->info_box_contents[$i][0] = array('td' => 'align="center"','text'=>$langs->trans("NoRecordedNdfp"));
			}
			else
			{
				$this->info_box_contents[0][0] = array(	'td' => 'align="left"',
    	        										'maxlength'=>500,
	            										'text' => ($db->error().' sql='.$sql));
			}

		}
		else {
			$this->info_box_contents[0][0] = array('td' => 'align="left"',
            'text' => $langs->trans("ReadPermissionNotAllowed"));
		}
	}

	function showBox($head = null, $contents = null)
	{
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}

}

