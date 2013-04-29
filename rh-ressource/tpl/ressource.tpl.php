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
	[onshow;block=begin;when [view.mode]=='new']
		<tr>
			<td>Type</td>
			<td>[ressourceNew.typeCombo;strconv=no;protect=no]</td>
			<td>[ressourceNew.validerType;strconv=no;protect=no]</td>
		</tr>
	[onshow;block=end]
	[onshow;block=begin;when [view.mode]!='new']
	<tr>
		<td>Type</td>
		<td>[ressource.type;strconv=no;protect=no]</td>[ressource.typehidden;strconv=no;protect=no]
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
		
[onshow;block=begin;when [view.mode]=='edit']
<h2>Attribution de la ressource</h2>

<p> Attribuer directement cette ressource à un utilisateur : 
<INPUT type=radio name="fieldChoice" value="O" id="ouiChecked"><label for="ouiChecked"> Oui</label>
<INPUT type=radio name="fieldChoice" value="N" id="nonChecked" checked="checked"><label for="nonChecked"> Non</label>
</p>

<table id="tableAttribution" class="border" style="width:100%">
	[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
	[NEmprunt.type;strconv=no;protect=no]
	<tr>
		<td>Utilisateur</td>
		<td>[NEmprunt.fk_user;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Date début</td>
		<td>[NEmprunt.date_debut;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Date fin</td>
		<td>[NEmprunt.date_fin;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Commentaire</td>
		<td>[NEmprunt.commentaire;strconv=no;protect=no]</td>
	</tr>
</table>
[onshow;block=end]


<script>
	$(document).ready( function(){
		$('#tableAttribution').hide();
		$('#ouiChecked').click(function(){
			$('#tableAttribution').show();
		});
		$('#nonChecked').click(function(){
			$('#tableAttribution').hide();
		})
		
		//on empêche que la date de début dépasse pas celle de fin
		function comparerDates(){
			jd = parseInt($("#date_debut").val().substr(0,2));
			md = parseInt($("#date_debut").val().substr(3,2));
			ad = parseInt($("#date_debut").val().substr(6,4));
			jf = parseInt($("#date_fin").val().substr(0,2));
			mf = parseInt($("#date_fin").val().substr(3,2));
			af = parseInt($("#date_fin").val().substr(6,4));
			if(af<ad){
				$("#date_fin").val($("#date_debut").val());
				return;
			}
			else if(af==ad){
				
				if(mf<md){
					$("#date_fin").val($("#date_debut").val());
					return;}
					
				else if(mf==md){
					
					if(jf<jd){
						$("#date_fin").val($("#date_debut").val());
						return;}
					else if(jf=jd){return;}
					else{return;}
					
				}
				else{return;}
			}
			else{return;}
			
			
		};
		
		$("#date_debut").change(comparerDates);
		$("#date_fin").change(comparerDates);
			
	});
</script>



[onshow;block=begin;when [view.userRight]==1]
<div class="tabsAction" style="text-align:center;" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			[onshow;block=begin;when [ressource.id]!=0]
				&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
			[onshow;block=end]
			[onshow;block=begin;when [ressource.id]==0]
				&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href=''">
			[onshow;block=end]
			
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressource.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[ressource.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>
[onshow;block=end]

<div>

		[onshow;block=begin;when [fk_ressource.fk_rh_ressource]=='aucune ressource']
			[onshow;block=begin;when [fk_ressource.reqExiste]=='1']
				[onshow;block=begin;when [view.mode]=='view']
				</br>
				<h2>Organigramme des ressources associées</h2>
					<div id="organigrammePrincipal" style="margin-left:70px;text-align:center;">
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
				<h2>Organigramme des ressources associées</h2>
					<div id="organigrammePrincipal" style="margin-left:70px;text-align:center;">
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

	[onshow;block=end]
</div>
