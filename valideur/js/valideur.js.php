<?php

	/* Script pour le valideur */
require('../config.php');

	if(isset($_REQUEST['action'])) {
		
		
		
		if($_REQUEST['action']=='next-alert-level') {
			$ATMdb=new TPDOdb;
			$ATMdb->Execute("UPDATE ".MAIN_DB_PREFIX."ndfp SET alertLevel=alertLevel+1 WHERE rowid=".$_REQUEST['id_ndfp']);
		}
		
		exit();
	}
	
?>
function ndfp_alert_next_level(id_ndfp) {
	if(window.confirm('Etes-vous sûr de vouloir alerter le valideur suivant ?')) {

		$.ajax({
			url: '<?=dol_buildpath('/valideur/js/valideur.js.php',1) ?>?action=next-alert-level&id_ndfp='+id_ndfp
		}).done(function() {
			alert('Le valideur de niveau suivant a maintenant accès à cette note de frais.');
			document.location.href="<?=dol_buildpath('/ndfp/ndfp.php',1) ?>";
		});	
		
	}
	
}