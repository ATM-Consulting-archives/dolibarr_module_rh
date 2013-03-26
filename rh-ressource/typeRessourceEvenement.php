<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$ressourceType=new TRH_ressource_type;
	$mesg = '';
	$error=false;
	llxHeader('','Règle sur les Ressources', '', '', 0, 0);
	
	
	if(isset($_REQUEST['id'])){
		$ressourceType->load($ATMdb, $_REQUEST['id']);
		if (isset($_REQUEST['action'])){
		if ($_REQUEST['action'] == 'save'){
			$ressourceType->liste_evenement = implode(';', $_REQUEST['TEvenements']);
			//echo $ressourceType->liste_evenement;
			$ressourceType->save($ATMdb);
			$mesg = '<div class="ok">Modifications effectuées</div>';
			$mode = 'view';
		}
		if ($_REQUEST['action'] == 'delete'){
			$TEvenements = explode(';',$ressourceType->liste_evenement);
			unset($TEvenements[$_REQUEST[key]]);
			$ressourceType->liste_evenement = implode(';', $TEvenements);
			//echo $ressourceType->liste_evenement;
			$ressourceType->save($ATMdb);
			
		}
	}

		//$ATMdb->db->debug=true;
		
		?><div class="fiche"><?	
		dol_fiche_head(ressourcePrepareHead($ressourceType, 'type-ressource')  , 'event', 'Type de ressource');
	
		?><h2>Créer des événements associés au type de la ressource</h2><?	
		
		//on récupère le champs 
		
		$TEvenements = empty($ressourceType->liste_evenement) ? array() : explode(';',$ressourceType->liste_evenement);
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		echo $form->hidden('id', $ressourceType->getId());
		echo $form->hidden('action','save');
		$key = -1;
		if (!empty($TEvenements)){
			foreach ($TEvenements as $key => $value) {
				?>
				<input class="" type="text" id="TEvenements[<? echo $key; ?>]"  name="TEvenements[<? echo $key; ?>]" value = "<? echo $value; ?>" size="20" size="255" >
				<a href="?id=<? echo $ressourceType->getId(); ?>&key=<? echo $key; ?>&action=delete"><img src="./img/delete.png"></a>
				<br>
				<?
			}
		}
		?>
		<input class="" type="text" id="TEvenements[<? echo $key+1; ?>]"  name="TEvenements[<? echo $key+1; ?>]" size="20" size="255" >
		<input type="submit" value="Ajouter" name="save" class="button" >
		<?
		
		echo $form->end_form();
		
		global $mesg, $error;
		dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
		llxFooter();
				
	}
	
		
	$ATMdb->close();
	

	
	
