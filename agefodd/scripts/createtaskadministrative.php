<?php
/* Copyright (C) 2013 Florian Henry  <florian.henry@open-concept.pro>
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
*/

/**
 *       \file       /agefodd/scripts/createtaskadmin.php
*       \brief      Generate script
*/
if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
if (! defined('NOLOGIN'))        define('NOLOGIN','1');

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

dol_include_once('/agefodd/class/agsession.class.php');
dol_include_once('/user/class/user.class.php');

$userlogin=GETPOST('login');
$id=GETPOST('id');

$user=new User($db);
$result=$user->fetch('',$userlogin);

$agf = new Agsession($db);
$agf->fetch($id);
if (!empty($agf->id)) {
	
	$result = $agf->createAdmLevelForSession($user);
	if ($result>0) {
		print -1;
	} else {
		print 1;
	}
}


/*
 * //Use for data retreive from Akteos

$sql = "SELECT s.rowid";
$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
$sql .= " WHERE s.rowid NOT IN (select fk_agefodd_session FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu)";
$sql .= " AND s.archive=0";
$resql = $db->query ( $sql );
if ($resql) {

		while ( $obj = $db->fetch_object ( $resql )) {
			$agf = new Agsession($db);
			$agf->fetch($obj->rowid);
			if (!empty($agf->id)) {
			
				$result = $agf->createAdmLevelForSession($user);
				if ($result>0) {
					print -1;
				} else {
					print 1;
				}
			}
		}
}
 */
			