<?php
	
	require('config.php');
ini_set('display_errors','On');
error_reporting(E_ALL);
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
			
			 $xml->addChild('DocPath', '.\\'.$_FILES['fichier1']['name']);
			 $xml->addChild('Index1', $_REQUEST['id']);
			 $xml->addChild('Index2', $user->login);
			 $xml->addChild('Index3', $user->lastname);
			 $xml->addChild('Index4', $user->firstname);
			 $xml->addChild('Index5', date('d/m/Y') );
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
<!-- <iframe src="<?=$url ?>" width="100%" height="800">
</iframe> -->
<?php

//print_r( PDO::getAvailableDrivers());
	$pdo=new TPDOdb('','odbc:Driver=FreeTDS;Server=192.168.1.117;Database=Therefore; Uid=dolibarr;Pwd=doli2013;');
	//$pdo = new PDO("odbc:Driver=FreeTDS; Server=sqlsrv4; Port=1433; Database=Therefore; UID=dolibarr; PWD=doli2013;");
//	$pdo = new PDO("odbc:Driver=FreeTDS;Server=192.168.1.117;Database=Therefore; Uid=dolibarr;Pwd=doli2013;");
$sql = "SELECT * FROM [Therefore].[dbo].[TheCat".$_REQUEST['categorie']."] WHERE [Id_Dolibarr]=".$_REQUEST['id'];
// $res=$pdo->prepare("SELECT [DocNp] FROM [Therefore].[dbo].[TheCat13] WHERE [Id_Dolibarr]=".$_REQUEST['id']);
// $res=$pdo->query("SELECT [*] FROM [Therefore].[dbo].[TheCat".$_REQUEST['categorie']."] WHERE [Id_Dolibarr]=".$_REQUEST['id']);
//$sql="SELECT [name],[xtype] FROM [Therefore].[dbo].[sysobjects] WHERE xtype='U'";
// $res=$pdo->prepare("SELECT [name],[xtype] FROM [Therefore].[dbo].[sysobjects] WHERE xtype='U'");
//$res->execute();
$pdo->debug=true;
$pdo->Execute($sql);

$TFichier = $pdo->Get_All();
pre($TFichier);
//print_r( $pdo->errorInfo());
	llxFooter();
