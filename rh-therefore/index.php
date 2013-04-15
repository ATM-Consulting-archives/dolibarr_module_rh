<?php
	
	require('config.php');


	if(isset($_REQUEST['ndfp'])) {
		dol_include_once("/ndfp/lib/ndfp.lib.php");
		$langs->load('ndfp@ndfp');
		
		$head = ndfp_prepare_head($_REQUEST['id']);
		llxHeader('',$langs->trans('Therefore'));
		dol_fiche_head($head, 'therefore', $langs->trans('Ndfp'));
	}

	$url = strtr(THEREFORE_READ,array(
		'[categorie]'=>$_REQUEST['categorie']
		,'[id]'=>$_REQUEST['id']
	));

?>
<form value="index.php" method="POST" enctype="multipart/form-data">
Charger un fichier <input type="file" name="fichier1" /> 
<input type="submit" value="envoyer" />
</form>
<iframe src="<?=$url ?>" width="100%" height="800">
</iframe>
<?

	llxFooter();
