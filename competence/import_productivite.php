<?php

/*
 * Import avec def manuel de mapping
 * Voir comment l'intégrer proprement après je suis dans l'urgence
 */
 

require('config.php');
dol_include_once('/importatm/class/import.class.php');
dol_include_once('/competence/class/productivite.class.php');
dol_include_once('/categories/class/categorie.class.php');
dol_include_once('/societe/class/societe.class.php');
dol_include_once('/contact/class/contact.class.php');

$step = __get('step',1,'int');
$action = __get('action','');
$type = __get('type','');

if(isset($_REQUEST['launchImport'])) {
	$step=3;
	$action='launchImport';
}

$ATMdb=new TPDOdb;

if($action=='upfile') {
	
	if($f1 = fopen($_FILES['file1']['tmp_name'],'r')) {
		
		TImportFile::delete_All($ATMdb);
		
		while(!feof($f1)) {
			
			$line = trim(fgets($f1, 10000));
			
			if(!empty($line)) {
				$i=new TImportFile;
				$i->line_from_file = $line;
				$i->save($ATMdb);
				
			}
			
		}
		
	}
	
		
}


llxHeader('', 'import fichier');

_upload_file($type);

if($step > 1) {
	
	_draw_table($ATMdb,$type);
	
}

if($step > 2 ) {
	
	//import du fichier avec mapping et delimiteur définis
	if($action=='launchImport') {
	
		_import($ATMdb,$type);
	
	}
	
}

llxFooter();

function _upload_file($type="societe") {
	
	
	$form=new TFormCore('auto', 'formUp', 'post', true);
	echo $form->hidden('action','upfile');
	echo $form->hidden('step','2');
	
	echo $form->combo('Type','type',array("productivite"=>"Productivite"),$type);
	echo $form->fichier('Fichier à importer', 'file1', '', 50);
	echo $form->btsubmit('Mapper le fichier', 'btup');
	
	$form->end();
}

function _draw_table(&$ATMdb,&$type) {
	
	$Tab = TImportFile::get_All($ATMdb);
	TImportFile::initField($ATMdb,$type);
	
	$TIndices = TRH_productivite::get_key_val_indices();
	
	$delimiter = __get('delimiter', IMPORT_FIELD_DELIMITER);
	$enclosure = __get('enclosure', IMPORT_FIELD_ENCLOSURE);
	
	if($delimiter == "tab"){
		$delimiter = "\t";
	}
	
	$TField = __get('TField',array());
	
	// str_getcsv ( string $input [, string $delimiter = ',' [, string $enclosure = '"' [, string $escape = '\\' ]]] )
	$THeader = str_getcsv($Tab[0]['line_from_file'], $delimiter, $enclosure);
	
	
	$form=new TFormCore('auto', 'formMap', 'post');
	echo $form->hidden('action','setMapping');
	echo $form->hidden('step','2');
	echo $form->hidden('type',$type);
	
	echo $form->texte('Délimiteur', 'delimiter', $delimiter, 3).'<br />';
	echo $form->texte('Encloseur (oui je sais)', 'enclosure', $enclosure, 3).'<br />';
	echo $form->checkbox1('Importer la 1ere ligne', 'withFirstLine', 1, __get('withFirstLine',0)).'<br />';
	echo $form->checkbox1('Ne pas réimporter les tiers', 'doNotReimportSociete', 1, __get('doNotReimportSociete', 0)).'<br />';	
	
	echo $form->btsubmit('Re-Mapper', 'btmap');
	

	?><table class="border" width="100%">
		<tr>
			<?php
				foreach($THeader as $key=>$values) {
					
					?><th><?php echo $form->combo('', 'TField['.$key.']', $TIndices, $TField[$key]) ?></th><?php
					
				}
			?>
		</tr>
		<tr>
			<?php
			
				echo '<th><a onclick="$(\'input[type=checkbox]\').attr(\'checked\', \'checkd\')" href="#">Cocher</a>/';
				echo '<a onclick="$(\'input[type=checkbox]\').removeAttr(\'checked\')" href="#">Décocher</a></th>';
			
				foreach($THeader as $key=>$values) {
					
					?><th><?php echo $values ?></th><?php
					
				}
			?>
		</tr>
		<?php
			$nb= count($Tab);
			if($nb>20) $nb = 20;
			
			for($i=1;$i<=$nb;$i++) {
				
				$row = str_getcsv($Tab[$i]['line_from_file'], $delimiter, $enclosure);
				
				$class = ($class=='impair') ? 'pair' : 'impair';
				
				?><tr class="<?php echo $class ?>">
					<?php
					
						echo "<td>".$form->checkbox1('', "TLinesToImport[".$i."]", $i, 1)."</td>";
					
						foreach($row as $value) {
							
							?>
								
								<td><?php echo strtr($value,array("\n"=>" ","\r\n"=>" ","\n\r"=>" ","\r"=>" ",CHR(10)=>" ",CHR(13)=>" ","\t"=>" ",PHP_EOL=>" ",chr(10).chr(13)=>" ")); ?></td>
							<?php
							
						}
					?>
				</tr><?php
			}
		
		?>
	</table><?php

	echo $form->btsubmit('Lancer l\'import', 'launchImport');
	
	$form->end();	
	
		
}

function _import(&$ATMdb,&$type) {
global $db,$user,$conf; 

	$TLinesToImport = $_REQUEST['TLinesToImport'];
	TImportFile::initField($ATMdb,$type);
	
	$delimiter = __get('delimiter', IMPORT_FIELD_DELIMITER);
	$enclosure = __get('enclosure', IMPORT_FIELD_ENCLOSURE);
	
	if($delimiter == "tab"){
		$delimiter = "\t";
	}
	
	$TField = __get('TField',array());	
	$Tab = TImportFile::get_All($ATMdb);
	
	$nb = count($Tab);
	$start = isset($_REQUEST['withFirstLine']) ? 0 : 1;
	
	$import = new TImport;
	
	for($i = $start;$i<$nb;$i++) {
		
		$row = str_getcsv($Tab[$i]['line_from_file'], $delimiter, $enclosure);
		
		if(isset($TLinesToImport[$i])) TImportFile::_import_productivite($ATMdb,$import,$row,$TField,$Tab);
		
	}
	
	
	
}
