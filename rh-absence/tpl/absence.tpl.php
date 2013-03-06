

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
							
							[absenceCourante.etat;strconv=no;protect=no]
							
							
						</div>
					</td>
				<tr>
			</table>
		
			

		
				
		  </div>
		</div>
			
			
		<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[absenceCourante.id]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[absenceCourante.id]&action=edit">Modifier</a>
		[onshow;block=end]
		</div>




