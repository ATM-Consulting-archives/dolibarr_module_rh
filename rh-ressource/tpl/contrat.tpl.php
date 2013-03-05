<h1>Contrat</h1>


<div>
	Libellé du contrat : [contrat.libelle;strconv=no;protect=no] <br>
	Type de ressource associée : [contrat.typeRessource;strconv=no;protect=no] <br>
	Type de contrat : [contrat.typeContrat;strconv=no;protect=no] <br>
	Tiers :	[contrat.tiersFournisseur;strconv=no;protect=no] <br>
	Agence concernée : 	[contrat.tiersAgence;strconv=no;protect=no] <br>
	Date de début : [contrat.date_debut;strconv=no;protect=no] <br>
	Date de fin : [contrat.date_fin;strconv=no;protect=no] <br>
	Loyer TTC : [contrat.loyer_TTC;strconv=no;protect=no] <br>
	TVA : [contrat.TVA;strconv=no;protect=no] <br>
	Loyer HT : [contrat.loyer_HT;strconv=no;protect=no] <br>
	
</div>



	
<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[contrat.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[contrat.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[contrat.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>


