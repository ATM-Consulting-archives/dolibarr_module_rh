
	[onshow;block=begin;when [view.mode]=='view']
        	[view.head;strconv=no]
     [onshow;block=end] 
     
     [onshow;block=begin;when [view.mode]=='edit']
        	[view.head2;strconv=no]
     [onshow;block=end] 


			[onshow;block=begin;when [view.mode]=='edit']
           		[absenceCourante.titreNvDemande;strconv=no;protect=no]                        
			[onshow;block=end]
			 [onshow;block=begin;when [view.mode]!='edit']
           		[absenceCourante.titreRecapAbsence;strconv=no;protect=no]                   
			[onshow;block=end]
			
			[absenceCourante.fk_user_absence;strconv=no;protect=no]
			<table class="border" style="width:40%">
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='1']
				<tr>
					<td>Utilisateur</td>
					<td>[absenceCourante.userAbsence;strconv=no;protect=no]</td>
				</tr>	
				[onshow;block=end]
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='0']
				<tr>
					<td>Utilisateur Courant</td>
					<td>[userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]</td>
					[absenceCourante.userAbsenceCourant;strconv=no;protect=no]
				</tr>
				[onshow;block=end]	
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
				[onshow;block=begin;when [view.mode]!='edit']
					<tr>
						<td>Durée (en journées)</td>
						<td>[absenceCourante.duree;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Durée (en heures)</td>
						<td>[absenceCourante.dureeHeure;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Durée comptabilisée (en heures)</td>
						<td>[absenceCourante.dureeHeurePaie;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Etat</td>
						<td>[absenceCourante.libelleEtat;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Avertissement</td>
						<td>[absenceCourante.avertissement;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Niveau de validation</td>
						<td>[absenceCourante.niveauValidation;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Commentaire du valideur</td>
						<td>[absenceCourante.commentaireValideur;strconv=no;protect=no]</td>
					</tr>
					[onshow;block=end]
					<tr>
						<td>Commentaire</td>
						<td>[absenceCourante.commentaire;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Créée le </td>
						<td>[absenceCourante.dt_cre;strconv=no;protect=no]</td>
					</tr>
					[onshow;block=begin;when [absenceCourante.time_validation]+-0 ]
					<tr>
						<td>Validée le </td>
						<td>[absenceCourante.date_validation;strconv=no;protect=no] par [absenceCourante.userValidation]</td>
					</tr>
					[onshow;block=end]
				
			</table>

   		 <br/>
     	[absenceCourante.titreJourRestant;strconv=no;protect=no] 			
            <table class="border" style="width:40%">
				<tr>
					<td>Congés payés</td>
					<td id='reste'>[congesPrec.reste;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>RTT cumulés</td>
					<td id='cumule'>[rttCourant.cumuleReste;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>RTT non cumulés</td>
					<td id='noncumule'>[rttCourant.nonCumuleReste;strconv=no;protect=no]</td>
				</tr>
			</table>
	
		[onshow;block=begin;when [absenceCourante.etat]!='Refusee']
		[onshow;block=begin;when [absenceCourante.etat]!='Validee']
			
				[onshow;block=begin;when [view.mode]=='edit']
					<br>
					<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [userCourant.valideurConges]=='1']
					
						<a class="butAction" id="action-update"  onclick="if (window.confirm('Voulez-vous vraiment accepter la demande d\'absence ?')){document.location.href='?action=accept&id=[absenceCourante.id]'};">Accepter</a>	
						<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('Voulez-vous vraiment refuser la demande d\'absence ?')){document.location.href='?action=refuse&id=[absenceCourante.id]'};">Refuser</span>
						<a style='width:30%' class="butAction" id="action-update"  onclick="if (window.confirm('Voulez-vous vraiment envoyer la demande d\'absence au valideur supérieur ?')){document.location.href='?action=niveausuperieur&id=[absenceCourante.id]&validation=ok'};">Envoyer au valideur supérieur</a>	
									
					[onshow;block=end]
				[onshow;block=end]
		[onshow;block=end]
		[onshow;block=end]	


		[onshow;block=begin;when [view.mode]!='edit']
				[onshow;block=begin;when [absenceCourante.droitSupprimer]==1]
						<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('Voulez-vous vraiment supprimer la demande d\'absence ?')){document.location.href='?action=delete&id=[absenceCourante.id]'};">Supprimer</span>
				[onshow;block=end]			
		[onshow;block=end]
		<div style="clear:both;"></div>
	</div>
	

		
		<div>
		[absenceCourante.titreDerAbsence;strconv=no;protect=no] 
		<table  class="liste formdoc noborder" style="width:100%">
				<tr class="liste_titre">
					<td><b>Date de début</b></td>
					<td><b>Date de fin</b></td>
					<td><b>Type d'absence</b></td>
					<td><b>Etat</b></td>
				</tr>
				<tbody  id="TRecapAbs">
					<tr>
						<td>[TRecap.date_debut;block=tr;strconv=no;protect=no]</td>
						<td>[TRecap.date_fin;block=tr;strconv=no;protect=no]</td>
						<td>[TRecap.libelle;block=tr;strconv=no;protect=no]</td>
						<td>[TRecap.libelleEtat;block=tr;strconv=no;protect=no]</td>
					</tr>

				</tbody>
		</table>
		</div>
		<br>
		
		

		<script type="text/javascript">
			function comparerDates(){
			/* TODO AA réécrire,  à chier  */
					var t1 = $("#date_debut").val().split('/');
					var t2 = $("#date_fin").val().split('/');
					jd = t1[0];
					md = t1[1];
					ad = t1[2];
					jf = t2[0];
					mf = t2[1];
					af = t2[2];

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
							else if(jf=jd){
								
								return;
							}
							else{return;}
							
						}
						else{return;}
					}
					else{return;}
					
					
			}
			function loadRecapCompteur() {
					
					if($('#fk_user').length>0) fk_user = $('#fk_user').val();
					else  fk_user = $('#fk_user_absence').val() ; 
				
					$.ajax({
						url: 'script/chargerCompteurDemandeAbsence.php?user='+fk_user
						,dataType:'json'
					}).done(function(liste) {
						
						$('#reste').empty();
						$('#reste').append(liste.reste);
						
						$('#cumule').empty();
						$('#cumule').append(liste.annuelCumule);
						
						$('#noncumule').empty();
						$('#noncumule').append(liste.annuelNonCumule);
						
						$('#mensuel').empty();
						$('#mensuel').append(liste.mensuel);
	
					});
				
				
			}
			
			function loadRecapAbsence() {
				
					if($('#fk_user').length>0) fk_user = $('#fk_user').val();
					else  fk_user = $('#fk_user_absence').val() ; 
				
					$.ajax({
						url: 'script/chargerRecapAbsenceUser.php?idUser='+fk_user
						,dataType:'json'
					}).done(function(liste) {
						$('#TRecapAbs').html('');
						for (var i=0; i<liste.length; i++){
							var texte = "<tr>"
								+"<td>"+liste[i].date_debut+"</td>"
								+"<td>"+liste[i].date_fin+"</td>"
								+"<td>"+liste[i].libelle+"</td>"
								+"<td>"+liste[i].libelleEtat+"</td>"
								+"</tr>";
							$('#TRecapAbs').html($('#TRecapAbs').html()+texte);
						}
					});
				
				
			}
			
			//	script vérifiant que la date de début ne dépasse pas celle de fin
			$(document).ready( function(){
				$("#dfMoment").val('apresmidi');
				$("#date_debut").change(comparerDates);
				$("#date_fin").change(comparerDates);
				$("#ddMoment").change(comparerDates);
				$("#dfMoment").change(comparerDates);
				
				loadRecapCompteur();
				loadRecapAbsence()
			});
				
		$('#fk_user').change(function(){
				loadRecapCompteur();
				loadRecapAbsence()
		});
		</script>

