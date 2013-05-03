<?php
	
	require('config.php');

	if(isset($_REQUEST['ndfp'])) {
		dol_include_once("/ndfp/lib/ndfp.lib.php");
		$langs->load('ndfp@ndfp');
		
		$head = ndfp_prepare_head($_REQUEST['id']);
		llxHeader('',$langs->trans('Therefore'));
		dol_fiche_head($head, 'therefore', $langs->trans('Ndfp'));
		
		$type='ndfp';
	}

	$url = strtr(THEREFORE_READ.'&time='.time(),array(
		'[categorie]'=>$_REQUEST['categorie']
		,'[id]'=>$_REQUEST['id']
	));
	
	
	if(isset($_FILES['fichier1'])) {
		/*
		 * Chargement d'un fichier
		 */
		 $xml=new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><Document></Document>');
		 
		 @mkdir('./tmp',0777);
		 
		 copy($_FILES['fichier1']['tmp_name'],'./tmp/'.$_FILES['fichier1']['name']);
		 
		 if($type=='ndfp') {
		 	 $filename='NDF'.$_REQUEST['id'].'.xml';
			
			 $xml->addChild('DocPath', $_FILES['fichier1']['name']);
			 $xml->addChild('index1', $_REQUEST['id']);
			 $xml->addChild('index2', $user->login);
			 $xml->addChild('index3', $user->name);
			 $xml->addChild('index4', $user->firstname);
			 $xml->addChild('index5', date('d/m/Y') );
		 }
		 
		 //print $filename.'<br>';
		 file_put_contents( './tmp/'.$filename , $xml->asXML() );
		
		 
		$cmd1 = 'smbclient '.THEREFORE_LOADER.' -W'.THEREFORE_GROUP.' -c "cd Loader;put ./tmp/'.$filename.' .\\'.$filename.';put ./tmp/'.$_FILES['fichier1']['name'].' .\\'.$_FILES['fichier1']['name'].'" -U '.THEREFORE_USER.'%'.THEREFORE_PASSWORD;
		file_put_contents('cmd.log',$cmd1."\n");
		print $cmd1.'<br/>';
		print exec($cmd1);
		
		print '<a href="./tmp/'.$filename.'">'.$filename.'</a> <a href="./tmp/'.$_FILES['fichier1']['name'].'">'.$_FILES['fichier1']['name'].'</a>'; 		
		
		// @unlink('./tmp/'.$_FILES['fichier1']['name']);
		// @unlink('./tmp/'.$filename);
		
		print "Fichier déposé sur le serveur";
	}
	

?>
<form value="index.php" method="POST" enctype="multipart/form-data">
Charger un fichier <input type="file" name="fichier1" />
<input type="hidden" name="<?=$type ?>" value="1" /> 
<input type="submit" value="envoyer" />
</form>
<iframe src="<?=$url ?>" width="100%" height="800">
</iframe>
<?

	llxFooter();
