
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
			<table class="border" style="width:100%">
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='1']
				<tr>
					<td>[translate.User;strconv=no]</td>
					<td>[absenceCourante.userAbsence;strconv=no;protect=no]</td>
				</tr>	
				[onshow;block=end]
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='0']
				<tr>
					<td>[translate.CurrentUser;strconv=no]</td>
					<td>[userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]</td>
					[absenceCourante.userAbsenceCourant;strconv=no;protect=no]
				</tr>
				[onshow;block=end]	
				<tr>
					<td>[translate.PresenceType;strconv=no]</td>
					<td>[absenceCourante.comboType;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.StartDate;strconv=no]</td>
			 		<td>[absenceCourante.date_debut;strconv=no;protect=no]&nbsp; &nbsp;[absenceCourante.ddMoment;strconv=no;protect=no]&nbsp; &nbsp;[absenceCourante.hourStart;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.EndDate;strconv=no]</td>
			 		<td>[absenceCourante.date_fin;strconv=no;protect=no]&nbsp; &nbsp;[absenceCourante.dfMoment;strconv=no;protect=no]&nbsp; &nbsp;[absenceCourante.hourEnd;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.Warning;strconv=no;protect=no]</td>
					<td>[absenceCourante.avertissement;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.Comment;strconv=no]</td>
					<td>[absenceCourante.commentaire;strconv=no;protect=no]</td>
				</tr>
					<tr>
						<td>[translate.CreatedThe;strconv=no] </td>
						<td>[absenceCourante.dt_cre;strconv=no;protect=no]</td>
					</tr>
					[onshow;block=begin;when [absenceCourante.time_validation]+-0 ]
					<tr>
						<td>[translate.ValidatedThe;strconv=no] </td>
						<td>[absenceCourante.date_validation;strconv=no;protect=no] par [absenceCourante.userValidation]</td>
					</tr>
					[onshow;block=end]
				
					[onshow;block=begin;when [other.dontSendMail]==1]
					<tr>
						<td>[translate.dontSendMail;strconv=no;protect=no]</td>
						<td id="dont_send_mail">[other.dontSendMail_CB;strconv=no;protect=no]</td>
					</tr>
					[onshow;block=end]
				
			</table>
			
		<div class="tabsAction" >
		[onshow;block=begin;when [absenceCourante.etat]!='Refusee']
		[onshow;block=begin;when [absenceCourante.etat]!='Validee']
			
				[onshow;block=begin;when [view.mode]=='edit']
					<br>
					<input type="submit" value="[translate.Register;strconv=no]" name="save" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [userCourant.valideurConges]=='1']
					
						<a class="butAction" id="action-update"  onclick="if (window.confirm('[translate.ConfirmAcceptPresenceRequest;strconv=no]')){document.location.href='?action=accept&id=[absenceCourante.id]'};">[translate.Accept;strconv=no]</a>	
						<span class="butActionDelete" id="action-delete"  onclick="refusePresence();">[translate.Refuse;strconv=no]</span>
						<a style='width:30%' class="butAction" id="action-update"  onclick="if (window.confirm('[translate.ConfirmSendToSuperiorAbsenceRequest;strconv=no]')){document.location.href='?action=niveausuperieur&id=[absenceCourante.id]&validation=ok'};">[translate.ConfirmSendToSuperiorAbsenceRequest;strconv=no]</a>	
									
					[onshow;block=end]
				[onshow;block=end]
		[onshow;block=end]
		[onshow;block=end]	


		[onshow;block=begin;when [view.mode]!='edit']
				[onshow;block=begin;when [absenceCourante.droitSupprimer]==1]
						<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('[translate.ConfirmDeletePresenceRequest;strconv=no]')){document.location.href='?action=delete&id=[absenceCourante.id]'};">[translate.Delete;strconv=no]</span>
				[onshow;block=end]			
		[onshow;block=end]
		<div style="clear:both;"></div>
	</div>
	

		
		<div>
		[absenceCourante.titreDerAbsence;strconv=no;protect=no] 
		<table  class="liste formdoc noborder" style="width:100%">
				<tr class="liste_titre">
					<td><b>[translate.StartDate;strconv=no]</b></td>
					<td><b>[translate.EndDate;strconv=no]</b></td>
					<td><b>[translate.AbsenceType;strconv=no]</b></td>
					<td><b>[translate.State;strconv=no]</b></td>
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
			function refusePresence() {
				
				if (commentaireValideur = window.prompt('[translate.ConfirmRefusePresenceRequest;strconv=no]')){
					
					document.location.href='?action=refuse&id=[absenceCourante.id]&commentaireValideur='+commentaireValideur
					
				};
				
			}
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
				

					$('#user-planning').load('planningUser.php?fk_user='+fk_user+'&no-link #plannings');
			}
			
			function loadDefaultTimes() {
				
				type = $('#type').val();
				
				$.ajax({
						url: 'script/interface.php?get=typeAbsence_hour&json=1&type='+type
						,dataType:'json'
					}).done(function(hours) {
							$('#date_hourStart').val(hours.start);
							$('#date_hourEnd').val(hours.end);
						
					});
				
				
				
				
			}
			
			//	script vérifiant que la date de début ne dépasse pas celle de fin
			$(document).ready( function(){
				$("#dfMoment").val('apresmidi');
				$("#date_debut").change(comparerDates);
				$("#date_fin").change(comparerDates);
				$("#ddMoment").change(comparerDates);
				$("#dfMoment").change(comparerDates);
				
				
				$('#type').change( loadDefaultTimes);
				
				loadRecapCompteur();
				loadRecapAbsence();
				
				loadDefaultTimes();
				
			});
				
		$('#fk_user').change(function(){
				loadRecapCompteur();
				loadRecapAbsence()
		});
		</script>




