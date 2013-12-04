<?php
/** Copyright (C) 2010-2012	Regis Houssin		<regis@dolibarr.fr>
 * Copyright (C) 2010		Laurent Destailleur	<eldy@users.sourceforge.net>
* Copyright (C) 2012-2013   Florian Henry  	<florian.henry@open-concept.pro>
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
 * 	\file       agefodd/core/modules/agefodd/certificate/mod_agefoddcertif_simple.php
 *	\ingroup    agefodd
 *	\brief      File with class to manage the numbering module Simple for agefodd references
*	\version    $Id$
*/

dol_include_once('/agefodd/core/modules/agefodd/modules_agefodd.php');


/**
 * 	Class to manage the numbering module Simple for project references
*/
class mod_agefoddcertif_simple extends ModeleNumRefAgefodd
{
	var $version='dolibarr';		// 'development', 'experimental', 'dolibarr'
	var $prefix='HT';
	var $error='';
	var $nom = "Simple";


	/**
	 *  Return description of numbering module
	 *
	 *  @return     string      Text with description
	 */
	function info()
	{
		global $langs;
		return $langs->trans("AgfSimpleNumRefModelCertifDesc",$this->prefix);
	}


	/**
	 *  Return an example of numbering module values
	 *
	 * 	@return     string      Example
	 */
	function getExample()
	{
		return $this->prefix."2013-00001";
	}


	/**  Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 *   de conflits qui empechera cette numerotation de fonctionner.
	 *
	 *   @return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
		global $conf,$langs;

		$coyymm=''; $max='';

		$posindice=8;
		$sql = "SELECT MAX(SUBSTRING(certif_code FROM ".$posindice.")) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_stagiaire_certif";
		$sql.= " WHERE certif_code LIKE '".$this->prefix."____-%'";
		// $sql.= " AND entity = ".$conf->entity;
		$resql=$db->query($sql);
		if ($resql)
		{
			$row = $db->fetch_row($resql);
			if ($row) {
				$coyymm = substr($row[0],0,6); $max=$row[0];
			}
		}
		if (! $coyymm || preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i',$coyymm))
		{
			return true;
		}
		else
		{
			$langs->load("errors");
			$this->error=$langs->trans('ErrorNumRefModel',$max);
			return false;
		}
	}


	/**
	 *  Return next value
	 *
	 *  @param   Societe		$objtraining		Object training
	 *  @param   Training	$agf		Object training
	 *  @return	string				Value if OK, 0 if KO
	 */
	function getNextValue($objtraining,$agf)
	{
		global $db,$conf;

		$prefix='';

		$sql = "SELECT";
		$sql.= " t.ref_interne";

		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_formation_catalogue as t";
		$sql.= " WHERE t.rowid=".$objtraining->id;

		dol_syslog("mod_agefoddcertif_simple::getNextValue sql=".$sql,LOG_DEBUG);
		$resql=$db->query($sql);
		if ($resql)
		{
			$obj = $db->fetch_object($resql);
			$prefix=$obj->ref_interne;
				
			//Format the two first certificate caracters
			if (strlen($prefix)>0) {
				$prefix=str_replace(' ', '', $prefix);
				$prefix=strtoupper($prefix);

				if (strlen($prefix)>=2) {
					$prefix=substr($prefix,0,2);
				} else {
					$prefix=$prefix.'X';
				}
			}
		}
		else
		{
			dol_syslog("mod_agefoddcertif_simple::getNextValue sql=".$sql, LOG_ERR);
			return -1;
		}
		if (empty($prefix)) {
			$this->prefix='CT';
		}else {
			$this->prefix=$prefix;
		}


		// D'abord on recupere la valeur max
		$posindice=8;
		$sql = "SELECT MAX(SUBSTRING(certif_code FROM ".$posindice.")) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_stagiaire_certif";
		$sql.= " WHERE certif_code LIKE '".$this->prefix."____-%'";

		dol_syslog("mod_agefoddcertif_simple::getNextValue sql=".$sql,LOG_DEBUG);
		$resql=$db->query($sql);
		if ($resql)
		{
			$obj = $db->fetch_object($resql);
			if ($obj) $max = intval($obj->max);
			else $max=0;
		}
		else
		{
			dol_syslog("mod_agefoddcertif_simple::getNextValue sql=".$sql, LOG_ERR);
			return -1;
		}

		$date=empty($agf->date_c)?dol_now():$agf->date_c;

		//$yymm = strftime("%y%m",time());
		$yyyy = strftime("%Y",$date);
		$num = sprintf("%05s",$max+1);

		dol_syslog("mod_agefoddcertif_simple::getNextValue return ".$this->prefix.$yyyy."-".$num);
		return $this->prefix.$yyyy."-".$num;
	}


	/**
	 * 	Return next reference not yet used as a reference
	 *
	 *  @param	Societe	$objsoc     Object third party
	 *  @param Training		$agf		Object training
	 *  @return string      		Next not used reference
	 */
	function project_get_num($objsoc=0,$agf='')
	{
		return $this->getNextValue($objsoc,$agf);
	}
}

?>