<?php 	llxHeader('', 'Liste des ressources', '', '', 0, 0, array('../js/jquery.jOrgChart.js'));
?>
[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        
                                
[onshow;block=end]  


<link rel="stylesheet" type="text/css" href="./css/jquery.jOrgChart.css" />
<script>
    jQuery(document).ready(function() {
    	
    	$("#JQorganigramme").jOrgChart({
            chartElement : '#chart',
            dragAndDrop : false
        });
    });
</script>


[onshow;block=begin;when [view.mode]!='view']
			<h2>Créer une ressource </h2>
[onshow;block=end]

<div>

	<!-- entête du tableau -->
<table class="border" style="width:100%">
	<tr>
		<td>Type</td>
		<td>[ressource.type;strconv=no;protect=no]</td>
	</tr>
	
	<tr>
		<td>Numéro Id</td>
		<td>[ressource.numId;strconv=no;protect=no] </td>
	</tr>
	<tr>
		<td>Libellé</td>
		<td>[ressource.libelle;strconv=no;protect=no] </td>
	</tr>
	<tr>
		<td>Date d'achat</td>
		<td>[ressource.date_achat;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Date de vente</td>
		<td>[ressource.date_vente;strconv=no;protect=no]</td>
	</tr>	
	<tr>
		<td>Date de garantie</td>
		<td>[ressource.date_garantie;strconv=no;protect=no]</td>
	</tr>	
	<tr>
		<td>Agence Propriétaire</td>
		<td>[ressource.fk_proprietaire;strconv=no;protect=no]</td>
	</tr>		
	
</table>

</div>

<h2>Champs</h2>

<table class="border" style="width:50%">
	<tr>
		<td style="width:40%" [ressourceField.obligatoire;strconv=no;protect=no]> 
			[ressourceField.libelle;block=tr;strconv=no;protect=no] 
		</td>
		<td style="width:60%"> [ressourceField.valeur;strconv=no;protect=no] </td>
		
	</tr>
</table>

<br>


	
		[onshow;block=begin;when [view.mode]=='edit']
			<h2>Ressource associée </h2>
			<div>
				[fk_ressource.liste_fk_rh_ressource;strconv=no;protect=no]
			</div>
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [fk_ressource.fk_rh_ressource]!='aucune ressource']
				<h2>Ressource associée </h2>
				<div>
					Cette ressource est associée à <a href='ressource.php?id=[fk_ressource.id]'>[fk_ressource.fk_rh_ressource]</a>.
				</div>
			[onshow;block=end]
		[onshow;block=end]

[onshow;block=begin;when [view.userRight]==1]
<div class="tabsAction" style="text-align:center;" >
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
[onshow;block=end]

<div style="margin-left:70px;text-align:center;">

		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]=='aucune ressource']
			[onshow;block=begin;when [fk_ressource.reqExiste]=='1']
				[onshow;block=begin;when [view.mode]=='view']
				</br>
				<h2><span style="margin-left:-70px;">Organigramme des ressources associées</span></h2>
					<div id="organigrammePrincipal" style="text-align:center;">
						<br/>
						<div id="chart" class="orgChart" ></div>
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
		[onshow;block=end]
		
		
		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]!='aucune ressource']
			[onshow;block=begin;when [view.mode]=='view']
				</br>
				<h2><span style="margin-left:-70px;">Organigramme des ressources associées</span></h2>
					<div id="organigrammePrincipal" style="text-align:center;">
					<br/>
					<div id="chart" class="orgChart" ></div>
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
