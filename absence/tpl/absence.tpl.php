
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


		<div id="fiche-abs">
			[view.form_start;strconv=no]
			
				<table class="border" width="100%">
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='1']
				<tr>
					<td>[translate.User;strconv=no;protect=no]</td>
					<td>[absenceCourante.userAbsence;strconv=no;protect=no]</td>
				</tr>	
				[onshow;block=end]
				[onshow;block=begin;when [userCourant.droitCreationAbsenceCollaborateur]=='0']
				<tr>
					<td>[translate.CurrentUser;strconv=no;protect=no]</td>
					<td>[userCourant.link;strconv=no;protect=no]</td>
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
            <table class="border" id="compteur-user"  width="100%">
                <tr>
                    <td>[translate.HolidaysPaid;strconv=no;protect=no] N-1</td>
                    <td id="reste"></td>
                </tr>   
                <tr>
                    <td>[translate.HolidaysPaid;strconv=no;protect=no] N</td>
                    <td id="resteN"></td>
                </tr>   
				<tr>
					<td>[translate.CumulatedDayOff]</td>
					<td id="cumule"></td>
				</tr>
				<tr>
					<td>[translate.NonCumulatedDayOff]</td>
					<td id="noncumule"></td>
				</tr>
				<tr>
					<td>[translate.acquisRecuperation;strconv=no;protect=no]</td>
					<td id="recup"></td>
				</tr>
				
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
				
					<input type="submit" value="[translate.Register;strconv=no;protect=no]" name="save" class="button" />
				[onshow;block=end]
				
				
				[onshow;block=begin;when [view.mode]!='edit']
					[onshow;block=begin;when [userCourant.valideurConges]=='1']
					
						<a class="butAction" id="action-update"  onclick="if (window.confirm('[translate.ConfirmAcceptAbsenceRequest;strconv=no]')){actionValidAbsence('accept')};">[translate.Accept;strconv=no;protect=no]</a>	
						<span class="butActionDelete" id="action-delete"  onclick="refuseAbsence()">[translate.Refuse;strconv=no;protect=no]</span>
						<a style='width:30%' class="butAction" id="action-update"  onclick="if (window.confirm('[translate.ConfirmSendToSuperiorAbsenceRequest;strconv=no]')){actionValidAbsence('sendToSuperior')};">[translate.SendToSuperiorValidator;strconv=no;protect=no]</a>	
									
					[onshow;block=end]
				[onshow;block=end]
		[onshow;block=end]
		[onshow;block=end]	

			[view.form_end;strconv=no]

		[onshow;block=begin;when [view.mode]!='edit']
				[onshow;block=begin;when [absenceCourante.droitSupprimer]==1]
						<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('[translate.ConfirmDeleteAbsenceRequest;strconv=no;protect=no]')){document.location.href='?action=delete&id=[absenceCourante.id]'};">[translate.Delete;strconv=no;protect=no]</span>
				[onshow;block=end]			
		[onshow;block=end]
		</div>
		<div style="clear:both;"></div>
		
		
		[listUserAlreadyAccepted.titre;strconv=no;protect=no] 
		<table class="liste formdoc noborder">
			<tr class="liste_titre">
				<td><b>Date d'acceptation</b></td>
				<td><b>Accéptée par</b></td>
			</tr>
			<tr>
				<td>[TUserAccepted.date_acceptation;strconv=no;protect=no]</td>
				<td>[TUserAccepted.username;strconv=no;protect=no]</td>
			</tr>
		</table>
		<br />
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
			function refuseAbsence() {
				
				var caseDontSendMail = $("#dontSendMail");

				if(caseDontSendMail.is(':checked')){
					var dontSendMail = '&dontSendMail=1'
				};
				
				if (commentaireValideur = window.prompt('[translate.ConfirmRefuseAbsenceRequest;strconv=no]')){
					
					var link = '?action=refuse&id=[absenceCourante.id]&commentaireValideur='+commentaireValideur+dontSendMail;
					document.location.href=link;
					
				};
				
			}
		
		    
		
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
					console.log("loadRecapCompteur "+fk_user);
					if(fk_user<=0) return false;
				
					$('#reste,#cumule,#noncumule,#recup').html('...');

					$.ajax({
						url: 'script/chargerCompteurDemandeAbsence.php?user='+fk_user
						,dataType:'json'
					}).done(function(liste) {
						
						$('#reste').html(liste.reste);
                        $('#resteN').html(liste.resteN);
                        
						if(liste.reste<0)$('#reste').css({'color':'red', 'font-weight':'bold'});
						else $('#reste').css({'color':'black', 'font-weight':'normal'});
						
						$('#cumule').html(liste.annuelCumule);
						
						$('#noncumule').html(liste.annuelNonCumule);
						$('#recup').html(liste.acquisRecuperation);
						
						$('#mensuel').html(liste.mensuel); //TODO n'existe pas ?
	
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
				
                    $.ajax({
                        url: "planningUser.php"
                        ,async: true
                        ,crossDomain: true
                        ,data: {
                            actionSearch:1
                            ,fk_user : fk_user
                            ,'no-link':1
                        }
                        
                    }).done(function(response) {
					    $('#user-planning').html($(response).find("#plannings"));
                        $('#user-planning tr.footer').remove();
                        $(".classfortooltip").tipTip({maxWidth: "600px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50});
    			    });
    			    
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
		
		$("#type").change(function() {
		   var TUnsecable = [ [absenceCourante.unsecableIds;protect=no;strconv=no] ];
		   
		   $("#ddMoment,#dfMoment").prop("disabled",false);
		   
		   for(x in TUnsecable) {
		       
		       if($(this).val() == TUnsecable[x]) {
		              $("#ddMoment,#dfMoment").prop("disabled",true);     
		       }
		       
		   }
		    
		});
		
		function actionValidAbsence(type) {
			
			var link = '';
			var caseDontSendMail = $("#dontSendMail");
			
			if(type == 'sendToSuperior') {
				link = '?action=niveausuperieur&id=[absenceCourante.id]&validation=ok';
			} else {
				link = '?action=accept&id=[absenceCourante.id]';
			}
			
			if(caseDontSendMail.is(':checked')){
				link += '&dontSendMail=1'
			};
			
			document.location.href=link;
			
		}
		
		</script>




