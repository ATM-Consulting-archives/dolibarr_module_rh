
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
					<tr>
						<td>Niveau de validation</td>
						<td>[absenceCourante.niveauValidation;strconv=no;protect=no]</td>
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
					<td>RTT cumulés </td>
					<td id='cumule'>[rttCourant.annuelCumule;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>RTT non cumulés</td>
					<td id='noncumule'>[rttCourant.annuelNonCumule;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>RTT mensuels</td>
					<td id='mensuel'>[rttCourant.mensuel;strconv=no;protect=no]</td>
				</tr>
			</table>

			
		<div class="tabsAction" >	
		[onshow;block=begin;when [absenceCourante.etat]!='Refusee']
		[onshow;block=begin;when [absenceCourante.etat]!='Validee']
			
				[onshow;block=begin;when [view.mode]=='edit']
					<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [userCourant.valideurConges]=='1']
					
						<a class="butAction" id="action-update"  onclick="document.location.href='?action=accept&id=[absenceCourante.id]'">Accepter</a>	
						<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=refuse&id=[absenceCourante.id]'">Refuser</span>
						<a style='width:30%' class="butAction" id="action-update"  onclick="document.location.href='?action=niveausuperieur&id=[absenceCourante.id]&validation=ok'">Envoyer au valideur supérieur</a>	
					
					[onshow;block=end]
				[onshow;block=end]
		[onshow;block=end]
		[onshow;block=end]	

		[onshow;block=begin;when when [absenceCourante.etat]!='Validee']
		[onshow;block=begin;when [view.mode]!='edit']
				[onshow;block=begin;when [absenceCourante.fk_user]==[absenceCourante.idUser]]

						<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=delete&id=[absenceCourante.id]'">Supprimer</span>

				[onshow;block=end]
					
		[onshow;block=end]
		[onshow;block=end]
	</div></div>
		
		
		<div>
		<br/><br/><br/><br/>
		[absenceCourante.titreDerAbsence;strconv=no;protect=no] 
		<table  class="liste formdoc noborder" style="width:100%">
				<tr class="liste_titre">
					<td><b>Date de début</b></td>
					<td><b>Date de fin</b></td>
					<td><b>Type d'absence</b></td>
					<td><b>Etat</b></td>
				</tr>
				<tr class="pair">
					<td>[TRecap.date_debut;block=tr;strconv=no;protect=no]</td>
					<td>[TRecap.date_fin;block=tr;strconv=no;protect=no]</td>
					<td>[TRecap.libelle;block=tr;strconv=no;protect=no]</td>
					<td>[TRecap.libelleEtat;block=tr;strconv=no;protect=no]</td>
				</tr>	
		</table>
		</div>
		
		
		<div>
		[absenceCourante.titreRegle;strconv=no;protect=no] 
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
		</div>
		
		

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
							else if(jf=jd){
								if($("#ddMoment").val()=='apresmidi'){
									$("#dfMoment").val($("#ddMoment").val());
								}
								return;
							}
							else{return;}
							
						}
						else{return;}
					}
					else{return;}
					
					
				};
				
				$("#date_debut").change(comparerDates);
				$("#date_fin").change(comparerDates);
				$("#ddMoment").change(comparerDates);
				$("#dfMoment").change(comparerDates);
			});
		</script>
		
		<script>
		$(document).ready( function(){
			if($('#userRecapCompteur').val()==0){
				
				if($('#userAbsenceCree').val()!=0){
					var urlajax='script/chargerCompteurDemandeAbsence.php?user='+$('#userAbsenceCree').val();
				}else{	
					
					if($('#fk_user option:selected').val()){
						var urlajax='script/chargerCompteurDemandeAbsence.php?user='+$('#fk_user option:selected').val();
					}else{
						var urlajax='script/chargerCompteurDemandeAbsence.php?user='+$('#fk_user').val();
					}			
					
				}
				
				$.ajax({
					url: urlajax
				}).done(function(data) {

					liste = JSON.parse(data);

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
			else{
				$.ajax({
					url: 'script/chargerCompteurDemandeAbsence.php?user='+$('#userRecapCompteur').val()
				}).done(function(data) {
					liste = JSON.parse(data);

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
			
		});
		
		$('#fk_user').change(function(){
				//alert('top');
				$.ajax({
					url: 'script/chargerCompteurDemandeAbsence.php?user='+$('#fk_user option:selected').val()
				}).done(function(data) {
					liste = JSON.parse(data);

					$('#reste').empty();
					$('#reste').append(liste.reste);
					
					$('#cumule').empty();
					$('#cumule').append(liste.annuelCumule);
					
					$('#noncumule').empty();
					$('#noncumule').append(liste.annuelNonCumule);
					
					$('#mensuel').empty();
					$('#mensuel').append(liste.mensuel);

				});
		});
		</script>




