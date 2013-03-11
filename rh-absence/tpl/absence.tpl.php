

			<h1 style="text-align:center;">Visualisation de vos jours acquis [userCourant.firstname] [userCourant.lastname]</h1>

        
        <div class="fiche"> <!-- begin div class="fiche" -->
          <div class="tabBar">
            
            [onshow;block=begin;when [view.mode]=='edit']
            <h1 style="color: #2AA8B9;"> Déclaration d'absence</h1>                         
			[onshow;block=end]
			 [onshow;block=begin;when [view.mode]!='edit']
            <h1 style="color: #2AA8B9;"> Visualisation absence</h1>                         
			[onshow;block=end]
			<table  border ="1" style="display: inline-block; margin-left:20px;">
				<tr>
					<td>
						<div>
							[onshow;block=begin;when [view.mode]=='edit']
							<h2> Nouvelle absence </h2><br/>	
							[onshow;block=end]
							[onshow;block=begin;when [view.mode]!='edit']
							<h2> Récapitulatif absence </h2><br/>	
							[onshow;block=end]
							
							
							id Utilisateur courant :  [absenceCourante.idUser;strconv=no;protect=no]	<br/><br/>	
							Type d'absence :  [absenceCourante.comboType;strconv=no;protect=no] &nbsp; &nbsp;&nbsp; &nbsp;<br/><br/>
							Date début : 	[absenceCourante.date_debut;strconv=no;protect=no]  &nbsp; &nbsp;[absenceCourante.ddMoment;strconv=no;protect=no]<br/><br/>
							Date fin : 		[absenceCourante.date_fin;strconv=no;protect=no]  &nbsp; &nbsp;[absenceCourante.dfMoment;strconv=no;protect=no]<br/><br/>
							Commentaire : 	[absenceCourante.commentaire;strconv=no;protect=no]<br/><br/>
							[onshow;block=begin;when [view.mode]!='edit']
							Duree : [absenceCourante.duree;strconv=no;protect=no]
							[onshow;block=end]
							[absenceCourante.etat;strconv=no;protect=no]
							
							
							
						</div>
					</td>
				<tr>
			</table>
		
			<table style="margin-left: 150px; display: inline-block; "  border ="1">
				<tr>
					<td>
						<div>
							<h2>Jours restants</h2>
							<b>Reste à prendre</b><br/>
							
							Congés payés : [congesPrec.reste;strconv=no;protect=no]<br/>
							RTT cumulés : 	[rttCourant.annuelCumule;strconv=no;protect=no]<br/>
							RTT non cumulés : 	[rttCourant.annuelNonCumule;strconv=no;protect=no]<br/>
							RTT mensuels : 	[rttCourant.mensuel;strconv=no;protect=no] <br/>

						</div>
					</td>
				<tr>
			</table>
			

		
				
		  </div>
		</div>
		<div id="test"></div>
		
			
		<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" href="?id=[absenceCourante.id]&action=view">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[absenceCourante.id]&action=edit">Modifier</a>
			<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=delete&id=[absenceCourante.id]'">Supprimer</span>
		[onshow;block=end]
		</div>
		
		
		
		<script>
			$(document).ready( function(){
				//on empêche que la date de début dépasse celle de fin
				 $('body').click( 	function(){
					if($("#date_debut").val()>$("#date_fin").val()){
						$("#date_fin").val($("#date_debut").val());
					}
	    		});	
				
			});
		</script>




