<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>
 * Copyright (C) 2012 Florian Henry  <florian.henry@open-concept.pro>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
* or see http://www.gnu.org/
*/

/**
 *		\file       agefodd/modules/agefodd/modules_agefodd.php
 *      \ingroup    project
 *      \brief      File that contain parent class for projects models
 *                  and parent class for projects numbering models
*/
require_once(DOL_DOCUMENT_ROOT."/core/class/commondocgenerator.class.php");


/**
 *  \class      ModelePDFCommandes
 *  \brief      Classe mere des modeles de commandes
*/
abstract class ModelePDFAgefodd extends CommonDocGenerator
{
	var $error='';

	/**
	 *  Return list of active generation modules
	 *
	 *  @param	DoliDB	$db     			Database handler
	 *  @param  string	$maxfilenamelength  Max length of value to show
	 *  @return	array						List of templates
	 */
	static function liste_modeles($db,$maxfilenamelength=0)
	{
		global $conf;

		$type='agefodd';
		$liste=array();

		$liste[]='agefodd';

		return $liste;
	}
}

/**
 *  Classe mere des modeles de numerotation des references de Agefodd
 */
abstract class ModeleNumRefAgefodd
{
	var $error='';

	/**
	 *  Return if a module can be used or not
	 *
	 *  @return		boolean     true if module can be used
	 */
	function isEnabled()
	{
		return true;
	}

	/**
	 *  Renvoi la description par defaut du modele de numerotation
	 *
	 *  @return     string      Texte descripif
	 */
	function info()
	{
		global $langs;
		$langs->load("agefodd@agefodd");
		return $langs->trans("AgfNoDescription");
	}

	/**
	 *  Renvoi un exemple de numerotation
	 *
	 *  @return     string      Example
	 */
	function getExample()
	{
		global $langs;
		$langs->load("agefodd@agefodd");
		return $langs->trans("AgfNoExample");
	}

	/**
	 *  Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 *  de conflits qui empechera cette numerotation de fonctionner.
	 *
	 *  @return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
		return true;
	}

	/**
	 *  Renvoi prochaine valeur attribuee
	 *
	 *	@param	Societe		$objsoc		Object third party
	 *	@param	Project		$project	Object project
	 *	@return	string					Valeur
	 */
	function getNextValue($objsoc, $project)
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 *  Renvoi version du module numerotation
	 *
	 *  @return     string      Valeur
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		return $langs->trans("NotAvailable");
	}
}

/**
 *	\brief   	Crée un document PDF
 *	\param   	db  			objet base de donnee
 *  \param   	id	  			can be object or rowid
 *	\param   	modele  		modele à utiliser
 *	\param		outputlangs		objet lang a utiliser pour traduction
 *	\return  	int        		<0 if KO, >0 if OK
 */
function agf_pdf_create($db, $id, $message, $typeModele, $outputlangs, $file, $socid, $courrier='')
{		
	global $conf,$langs;
	$langs->load('agefodd@agefodd');
	$langs->load('bills');

	$typeModele='demo';
	// Charge le modele
	$nomModele = dol_buildpath('/agefodd/core/modules/agefodd/pdf/pdf_'.$typeModele.'.modules.php');

	if (file_exists($nomModele))
	{
		require_once($nomModele);

		$classname = "pdf_".$typeModele;

		$obj = new $classname($db);
		$obj->message = $message;

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output=$outputlangs->charset_output;
		if ($obj->write_file($id, $outputlangs, $file, $socid, $courrier) > 0)
		{
			$outputlangs->charset_output=$sav_charset_output;
			return 1;
		}
		else
		{
			$outputlangs->charset_output=$sav_charset_output;
			dol_print_error($db,"pdf_create Error: ".$obj->error);
			return -1;
		}

	}
	else
	{
		dol_print_error('',$langs->trans("Error")." ".$langs->trans("ErrorFileDoesNotExists",$file));
		return -1;
	}
}


?>
