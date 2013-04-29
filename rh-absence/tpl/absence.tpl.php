
	[onshow;block=begin;when [view.mode]=='view']
        	[view.head;strconv=no]
     [onshow;block=end] 
     
     [onshow;block=begin;when [view.mode]=='edit']
        	[view.head2;strconv=no]
     [onshow;block=end] 


			[onshow;block=begin;when [view.mode]=='edit']
            <h1 style="color: #2AA8B9;"> Nouvelle demande d'absence</h1>                         
			[onshow;block=end]
			 [onshow;block=begin;when [view.mode]!='edit']
            <h1 style="color: #2AA8B9;"> Visualisation de la demande d'absence</h1>                         
			[onshow;block=end]


			<table class="border" style="width:40%">
				<tr>
					<td>Utilisateur Courant</td>
					<td>[userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Type d'absence</td>
					<td>[absenceCourante.comboType;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Date début</td>
					<td>[absenceCourante.date_debut;strconv=no;protect=no]  &nbsp; &nbsp;[absenceCourante.ddMoment;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Date fin</td>
					<td>[absenceCourante.date_fin;strconv=no;protect=no]  &nbsp; &nbsp;[absenceCourante.dfMoment;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Commentaire</td>
					<td>[absenceCourante.commentaire;strconv=no;protect=no]</td>
				</tr>
				[onshow;block=begin;when [view.mode]!='edit']
					<tr>
						<td>Duree (en demi-journées)</td>
						<td>[absenceCourante.duree;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Duree (en heures)</td>
						<td>[absenceCourante.dureeHeure;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Etat</td>
						<td>[absenceCourante.libelleEtat;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Avertissement</td>
						<td>[absenceCourante.avertissement;strconv=no;protect=no]</td>
					</tr>
				[onshow;block=end]
			</table>

    <br/>
     <h3 style="color: #2AA8B9;">Jours restants à prendre</h3>
							
            <table class="border" style="width:30%">
				<tr>
					<td>Congés payés</td>
					<td>[congesPrec.reste;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>RTT cumulés </td>
					<td>[rttCourant.annuelCumule;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>RTT non cumulés</td>
					<td>[rttCourant.annuelNonCumule;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>RTT mensuels</td>
					<td>[rttCourant.mensuel;strconv=no;protect=no]</td>
				</tr>
			</table>

			
			
		[onshow;block=begin;when [absenceCourante.etat]!='Refusee']
		[onshow;block=begin;when [absenceCourante.etat]!='Validee']
		[onshow;block=begin;when [absenceCourante.etat]!='Enregistree']
			<div class="tabsAction" >
				[onshow;block=begin;when [view.mode]=='edit']
					<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [userCourant.valideurConges]=='1']
						<a class="butAction" id="action-update"  onclick="document.location.href='?action=accept&id=[absenceCourante.id]'">Accepter</a>	
						<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=refuse&id=[absenceCourante.id]'">Refuser</span>
					[onshow;block=end]
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [absenceCourante.fk_user]==[absenceCourante.idUser]]
						<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=delete&id=[absenceCourante.id]'">Supprimer</span>
					[onshow;block=end]
				[onshow;block=end]
			</div>
		[onshow;block=end]
		[onshow;block=end]	
		[onshow;block=end]	

		</div>
		
		
		<h3 style="color: #2AA8B9;">Règles concernant le collaborateur</h3>
		<table  class="liste formdoc noborder" style="width:100%">
				<tr class="liste_titre">
					<td><b>Type d'absence concerné</b></td>
					<td><b>Nombre de jours cumulables possible</b></td>
					<td><b>Restrictif</b></td>
				</tr>
				<tr class="pair">
					<td>[TRegle.libelle;block=tr;strconv=no;protect=no]</td>
					<td>[TRegle.nbJourCumulable;block=tr;strconv=no;protect=no]</td>
					<td>[TRegle.restrictif;block=tr;strconv=no;protect=no]</td>
				</tr>	
		</table>

		<script>
			$(document).ready( function(){
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




