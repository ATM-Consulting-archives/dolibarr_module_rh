[onshow;block=begin;when [view.mode]=='view'] 
<div class="fiche">
    <div class="tabBar">
		<div>				
			<table class="border" style="width:100%">			
				<tr>
					<td>Date début</td>
					<td>Date fin</td>
					<td>Expérience</td>
					<td>Supprimer</td>
				</tr>
				<tr>
					<td>[cv.date_debut;block=tr;strconv=no;protect=no]</td>
					<td>[cv.date_fin;strconv=no;protect=no]</td>
					<td>[cv.experience;strconv=no;protect=no]</td>
					<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?fk_user=[userCourant.id]&deleteId=[cv.id]&action=delete'"></td>
				</tr>
			</table>
		</div>
	</div>
</div>
[onshow;block=end] 


[onshow;block=begin;when [view.mode]=='edit']
<h2 style="color: #2AA8B9;">Nouvelle ligne de CV</h2>
<table
	<tr>
		<td>Date début</td>
		<td>Date fin</td>
		<td>Expérience</td>
	</tr>
	<tr>
		<td>[cv.date_debut;block=tr;strconv=no;protect=no]</td>
		<td>[cv.date_fin;strconv=no;protect=no]</td>
		<td>[cv.experience;strconv=no;protect=no]</td>
	</tr>
</table>

<div class="tabsAction" >
	<input type="submit" value="Enregistrer" name="save" class="button">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]

