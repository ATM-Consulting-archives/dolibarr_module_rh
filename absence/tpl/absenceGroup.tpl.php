
	 [onshow;block=begin;when [view.mode]=='edit']
        	[view.head2;strconv=no]
     [onshow;block=end] 


			[onshow;block=begin;when [view.mode]=='edit']
           		[absenceCourante.titreNvDemande;strconv=no;protect=no]                        
			[onshow;block=end]
	

		<div id="fiche-abs">
			
			
				<table class="border" width="100%">
				<tr>
					<td>[translate.Group;strconv=no;protect=no]</td>
					<td>[absenceCourante.group;strconv=no;protect=no]</td>
				</tr>	
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
				
					<tr>
						<td>[translate.Comment;strconv=no;protect=no]</td>
						<td>[absenceCourante.commentaire;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>[translate.CreatedThe;strconv=no;protect=no]</td>
						<td>[absenceCourante.dt_cre;strconv=no;protect=no]</td>
					</tr>
				
				
			</table>		



   		 <br/>
     
		<div class="tabsAction" >
				
					<input type="submit" value="[translate.showMe;strconv=no;protect=no]" name="save" class="button" />

			

		</div>
		<div style="clear:both;"></div>
		
		
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
			
			//	script vérifiant que la date de début ne dépasse pas celle de fin
			$(document).ready( function(){
				$("#dfMoment").val('apresmidi');
				/*$("#date_debut").change(comparerDates);
				$("#date_fin").change(comparerDates);*/
				
				$("#date_debut").attr('onchange', $("#date_debut").attr('onchange')+" ; comparerDates();" );
				$("#date_fin").attr('onchange', $("#date_fin").attr('onchange')+" ; comparerDates();" );
				
				$("#ddMoment").change(comparerDates);
				$("#dfMoment").change(comparerDates);
				
			
			});
		
		
		</script>




