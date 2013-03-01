<?php 	llxHeader('', 'Liste des ressources', '', '', 0, 0, array('../js/jquery.jOrgChart.js'));
?>
<link rel="stylesheet" type="text/css" href="./css/jquery.jOrgChart.css" />
<script>
    jQuery(document).ready(function() {
    	
    	$("#JQorganigramme").jOrgChart({
            chartElement : '#chart',
            dragAndDrop : false
        });
    });
</script>


[onshow;block=begin;when [view.mode]=='view']
			<h1>Visualisation de la ressource</h1>
[onshow;block=end]
[onshow;block=begin;when [view.mode]!='view']
			<h1>Créer une ressource </h1>
[onshow;block=end]

<div>

	<!-- entête du tableau -->
<table class="border">
	<tr>
		<td>Id</td>
		<td>Libellé</td>
		<td>Type</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td></td>
		[onshow;block=end]
		
	</tr>
	
	<tr>
		<td>[ressource.id;strconv=no;protect=no] </td>
		<td>[ressource.libelle;strconv=no;protect=no] </td>
		<td>[ressource.type;strconv=no;protect=no] </td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td><input type="submit" value="Valider" name="save" class="butAction"></td>
		[onshow;block=end]	
	</tr>
	
</table>

</div>

<h2>Champs</h2>

<table class="border">
	<tr>
	<td>Libellé</td> 
	<td>Valeur</td>
	</tr>
	
	<tr>
		<td> [ressourceField.libelle;block=tr;strconv=no;protect=no] </td>
		<td> [ressourceField.valeur;strconv=no;protect=no] </td>
		
	</tr>
</table>

<br>

<h2>Ressources associées </h2>
<div>
	
		[onshow;block=begin;when [view.mode]=='edit']
			 [fk_ressource.liste_fk_rh_ressource;strconv=no;protect=no]
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			Cette ressource est associée à [fk_ressource.fk_rh_ressource].
		[onshow;block=end]
							

		

<div class="tabsAction" >

		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressource.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[ressource.id]&action=delete">Supprimer</a>
			<!--&nbsp; &nbsp; <input type="button" value="Supprimer" name="cancel" class="butActionDelete" onclick="document.location.href='?id=[ressource.id]&action=edit'">-->

		[onshow;block=end]
	

</div>
<br>


<h2>Organigramme des ressources associées</h2>

<div>
		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]=='aucune ressource']
			[onshow;block=begin;when [view.mode]=='view']
				<div id="organigrammePrincipal">
					<br/>
					<div id="chart" class="orgChart"></div>
						<ul id="JQorganigramme" style="display:none;">
							<li> [ressource.libelle;strconv=no;protect=no]
								(Ressource courante)
								<ul>
										<li>
											[sous_ressource.libelle;block=li;strconv=no;protect=no]
											<ul>
												
											</ul>
										</li>
								</ul>
							</li>
						</ul>	
				</div>
			[onshow;block=end]
		[onshow;block=end]
		
		
		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]!='aucune ressource']
			[onshow;block=begin;when [view.mode]=='view']
					<div id="organigrammePrincipal">
					<br/>
					<div id="chart" class="orgChart"></div>
						<ul id="JQorganigramme" style="display:none;">
							<li> [fk_ressource.fk_rh_ressource;strconv=no;protect=no]
								<ul>
										<li>
											 [ressource.libelle;strconv=no;protect=no]
											 (Ressource courante)
											<ul>
												
											</ul>
										</li>
								</ul>
							</li>
						</ul>	
				</div>
			[onshow;block=end]
		[onshow;block=end]

</div>
