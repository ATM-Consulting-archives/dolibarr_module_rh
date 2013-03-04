<?php


require('config.php');
require('./lib/absence.lib.php');
require('./class/absence.class.php');

//require_once(DOL_DOCUMENT_ROOT."/main.inc.php");
//require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
//require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");

llxHeader();

//$form = new Form($db);
//$formother = new FormOther($db);

$absence = new TRH_Absence;
print dol_get_fiche_head(absencePrepareHead($absence)  , 'fiche', 'Absence');

?>
<p align="center">
	<img src="./img/vacances.jpg" />
</p>
<?


llxfooter();
