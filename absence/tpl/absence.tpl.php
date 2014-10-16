
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
					<td>[translate.User;strconv=no;protect=no]</td>
					<td>[absenceCourante.userAbsence;strconv=no;protect=no]</td>
				</tr>	
				[onshow;block=end]
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='0']
				<tr>
					<td>[translate.CurrentUser;strconv=no;protect=no]</td>
					<td>[userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]</td>
					[absenceCourante.userAbsenceCourant;strconv=no;protect=no]
				</tr>
				[onshow;block=end]	
				<tr>
					<td>[translate.AbsenceType;strconv=no;protect=no]</td>
					<td>[absenceCourante.comboType;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.StartDate;strconv=no;protect=no]</td>
			 		<td>[absenceCourante.date_debut;strconv=no;protect=no]  &nbsp; &nbsp;[absenceCourante.ddMoment;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.EndDate;strconv=no;protect=no]</td>
			 		<td>[absenceCourante.date_fin;strconv=no;protect=no]  &nbsp; &nbsp;[absenceCourante.dfMoment;strconv=no;protect=no]</td>
				</tr>
				[onshow;block=begin;when [view.mode]!='edit']
					<tr>
						<td>[translate.DurationInDays;strconv=no;protect=no]</td>
						<td>[absenceCourante.duree;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.DurationInHours;strconv=no;protect=no]</td>
						<td>[absenceCourante.dureeHeure;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.CountedDurationInHours;strconv=no;protect=no]</td>
						<td>[absenceCourante.dureeHeurePaie;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.State;strconv=no;protect=no]</td>
						<td>[absenceCourante.libelleEtat;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.Warning;strconv=no;protect=no]</td>
						<td>[absenceCourante.avertissement;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.ValidationLevel;strconv=no;protect=no]</td>
						<td>[absenceCourante.niveauValidation;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.ValidatorComment;strconv=no;protect=no]</td>
						<td>[absenceCourante.commentaireValideur;strconv=no;protect=no]</td>
					</tr>
					[onshow;block=end]
					<tr>
						<td>[translate.Comment;strconv=no;protect=no]</td>
						<td>[absenceCourante.commentaire;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.CreatedThe;strconv=no;protect=no]</td>
						<td>[absenceCourante.dt_cre;strconv=no;protect=no]</td>
					</tr>
					[onshow;block=begin;when [absenceCourante.time_validation]+-0 ]
					<tr>
						<td>[translate.ValidatedThe;strconv=no;protect=no]</td>
						<td>[absenceCourante.date_validation;strconv=no;protect=no] [translate.AbsenceBy;strconv=no;protect=no] [absenceCourante.userValidation]</td>
					</tr>
					[onshow;block=end]
				
			</table>

   		 <br/>
     	[absenceCourante.titreJourRestant;strconv=no;protect=no] 			
            <table class="border" style="width:40%">
				<tr>
					<td>[translate.HolidaysPaid;strconv=no;protect=no]</td>
					<td id='reste'>[congesPrec.reste;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.CumulatedDayOff;strconv=no;protect=no]</td>
					<td id='cumule'>[rttCourant.cumuleReste;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.NonCumulatedDayOff;strconv=no;protect=no]</td>
					<td id='noncumule'>[rttCourant.nonCumuleReste;strconv=no;protect=no]</td>
				</tr>
			</table>
	
		[onshow;block=begin;when [absenceCourante.etat]!='Refusee']
		[onshow;block=begin;when [absenceCourante.etat]!='Validee']
			
				[onshow;block=begin;when [view.mode]=='edit']
					<br>
					<input type="submit" value="[translate.Register;strconv=no;protect=no]" name="save" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [userCourant.valideurConges]=='1']
					
						<a class="butAction" id="action-update"  onclick="if (window.confirm(\"[translate.ConfirmAcceptAbsenceRequest;strconv=no]\")){document.location.href='?action=accept&id=[absenceCourante.id]'};">[translate.Accept;strconv=no;protect=no]</a>	
						<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm(\"[translate.ConfirmRefuseAbsenceRequest;strconv=no]\")){document.location.href='?action=refuse&id=[absenceCourante.id]'};">[translate.Refuse;strconv=no;protect=no]</span>
						<a style='width:30%' class="butAction" id="action-update"  onclick="if (window.confirm(\"[translate.ConfirmSendToSuperiorAbsenceRequest;strconv=no]\")){document.location.href='?action=niveausuperieur&id=[absenceCourante.id]&validation=ok'};">[translate.SendToSuperiorValidator;strconv=no;protect=no]</a>	
									
					[onshow;block=end]
				[onshow;block=end]
		[onshow;block=end]
		[onshow;block=end]	


		[onshow;block=begin;when [view.mode]!='edit']
				[onshow;block=begin;when [absenceCourante.droitSupprimer]==1]
						<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('[translate.ConfirmDeleteAbsenceRequest;strconv=no;protect=no]')){document.location.href='?action=delete&id=[absenceCourante.id]'};">[translate.Delete;strconv=no;protect=no]</span>
				[onshow;block=end]			
		[onshow;block=end]
		<div style="clear:both;"></div>
	</div>
	

		
		<div>
		[absenceCourante.titreDerAbsence;strconv=no;protect=no] 
		<table  class="liste formdoc noborder" style="width:100%">
				<tr class="liste_titre">
					<td><b>[absenceCourante.lib_date_debut;strconv=no;protect=no]</b></td>
					<td><b>[absenceCourante.lib_date_fin;strconv=no;protect=no]</b></td>
					<td><b>[absenceCourante.lib_type_absence;strconv=no;protect=no]</b></td>
					<td><b>[absenceCourante.lib_duree_decompte;strconv=no;protect=no]</b></td>
					<td><b>[absenceCourante.lib_conges_dispo_avant;strconv=no;protect=no]</b></td>
					<td><b>[absenceCourante.lib_etat;strconv=no;protect=no]</b></td>
				</tr>
				<tbody id="TRecapAbs">
					
				</tbody>
		</table>
		
		<div id="user-planning-dialog">
			<div class="content">
			</div>
		</div>
		
		<div id="user-planning">
				
		</div>
		
		</div>
		<br>
		
		
		

		

		<script type="text/javascript">
			function comparerDates(){
				
					dpChangeDay("date_debut","[view.dateFormat;strconv=no]");
					dpChangeDay("date_fin","[view.dateFormat;strconv=no]");	
						
					jd = $("#date_debutday").val();
					md = $("#date_debutmonth").val();
					ad = $("#date_debutyear").val();
					jf = $("#date_finday").val();
					mf = $("#date_finmonth").val();
					af = $("#date_finyear").val();

					var dFin = new Date(af, mf-1, jf, 0,0,0,0,0); 
					var dDeb = new Date(ad, md-1, jd, 0,0,0,0,0); 

					if(dDeb>dFin) {
						dFin = dDeb;
							
						$("#date_debut").val( formatDate( dDeb,"[view.dateFormat;strconv=no]" ) ) ;
	 					$("#date_fin").val( formatDate( dFin,"[view.dateFormat;strconv=no]" ) ) ;	
	 						
						
						dpChangeDay("date_debut","[view.dateFormat;strconv=no]");
						dpChangeDay("date_fin","[view.dateFormat;strconv=no]");		
					}
 				
 					
					
			}
			function loadRecapCompteur() {
					if($('#fk_user').length>0) fk_user = $('#fk_user').val();
					else  fk_user = $('#userRecapCompteur').val() ; 
					
					if(fk_user<=0) return false;
				

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
					else  fk_user = $('#userRecapCompteur').val() ; 
					
					if(fk_user<=0) return false;

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
									+"<td>"+liste[i].duree+"</td>"
									+"<td>"+liste[i].congesAvant+"</td>"
									+"<td>"+liste[i].libelleEtat+"</td>"
									+"</tr>";
								$('#TRecapAbs').html($('#TRecapAbs').html()+texte);
							}
						
						
					});
				

					$('#user-planning').load('planningUser.php?fk_user='+fk_user+' #plannings');
			}
			
			//	script vérifiant que la date de début ne dépasse pas celle de fin
			$(document).ready( function(){
				$("#dfMoment").val('apresmidi');
				/*$("#date_debut").change(comparerDates);
				$("#date_fin").change(comparerDates);*/
				
				$("#date_debut").attr('onchange', $("#date_debut").attr('onchange')+" ; comparerDates();" );
				$("#date_fin").attr('onchange', $("#date_fin").attr('onchange')+" ; comparerDates();" );
				
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




