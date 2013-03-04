<?php

//TODO ne pas mettre le chemin d'accès en dur.
require('config.php');
//require_once(DOL_DOCUMENT_ROOT."/main.inc.php");
//require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
//require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");

llxHeader();

?>
<h1>Bienvenue sur le module Absences <?= $user->firstname." ".$user->lastname?> !  </h1>

