[exports.action;strconv=no;protect=no]
<table>
	<tr>
		<td>[exports.type;strconv=no;protect=no]</td>
		<td>[exports.idImport;strconv=no;protect=no]</td>
		<td>[exports.date_debut;strconv=no;protect=no]</td>
		<td>[exports.date_fin;strconv=no;protect=no]</td>
		[exports.urlFacture;strconv=no;protect=no]
		<td><input type="submit" class="button" value="Générer" /></td>
	</tr>
</table>

<script>

	function load(){
		var urlFact = $('#urlFacture').val()+$('#type option:selected').val()
		$.ajax({
			 url: urlFact
			 ,dataType:'json'
		}).done(function(liste) {
			$("#idImport").empty(); // remove old options
			$.each(liste, function(key, value) {
			  $("#idImport").append($("<option></option>")
			     .attr("value", key).text(value));
			});	
		});
		
		
	}
	
	$('#type').change(function(){
		load();
	});

	
</script>

[onshow;block=begin;when [view.mode]=='view']
<br />
<table class="liste" style="width:100%">
	<thead>
		<tr class="liste_titre">
			[onshow;block=begin;when [exports.typeDirect]=='Orange']
				<th>Affectation</th>
				<th>GSM</th>
				<th>Email</th>
				<th>Code compta</th>
				<th>Agence</th>
				<th>Code Analytique</th>
				<th>Pourcentage</th>
				<th>Dépassement Tél. du M-2/Mois en cours</th>
				<th>Total</th>
			[onshow;block=end]
			[onshow;block=begin;when [exports.typeDirect]!='Orange']
				<th>Facture</th>
				<th>Code journal</th>
				<th>Date de pièce</th>
				<th>Type de pièce</th>
				<th>Compte général</th>
				<th>Type de compte</th>
				<th>Code analytique</th>
				<th>Nom</th>
				<th>Prénom</th>
				<th>Référence de l'écriture</th>
				<th>Libellé de l'écriture</th>
				<th>Mode de paiement</th>
				<th>Date d'échéance</th>
				<th>Sens</th>
				<th>Montant</th>
				<th>Type d'écriture</th>
				<th>Numéro de pièce</th>
				<th>Devise</th>
			[onshow;block=end]
		</tr>
	</thead>
	<tbody>
		<tr 
		[onshow;block=begin;when [ligne.4;noerr]=='G'] class="impair" [onshow;block=end] 
		[onshow;block=begin;when [ligne.4;noerr]=='A'] class="pair" [onshow;block=end] 
		[onshow;block=begin;when [ligne.4;noerr]=='X'] 
			class="impair" style="color:#2AA8B9;font-weight:bold" 
		[onshow;block=end]
		>
			[onshow;block=begin;when [exports.typeDirect]=='Orange']
				<td> [ligne.nom;block=tr;strconv=no;protect=no;noerr] </td>
				<td> [ligne.numero;strconv=no;protect=no;noerr] </td>
				<td> [ligne.email;strconv=no;protect=no;noerr] </td>
				<td> [ligne.compte_tier;strconv=no;protect=no;noerr] </td>
				<td> [ligne.code_agence;strconv=no;protect=no;noerr] </td>
				<td> [ligne.code_analytique;strconv=no;protect=no;noerr] </td>
				<td> [ligne.pourcentage;protect=no;noerr] </td>
				<td> [ligne.total;protect=no;noerr] </td>
				<td> [ligne.total_non_pondere;protect=no;noerr] </td>
			[onshow;block=end]
			[onshow;block=begin;when [exports.typeDirect]!='Orange']
				<td> [ligne.numFacture;block=tr;strconv=no;protect=no;noerr] </td>
				<td> [ligne.codeJournal;strconv=no;protect=no;noerr] </td>
				<td> [ligne.datePiece;strconv=no;protect=no;noerr] </td>
				<td> [ligne.typePiece;strconv=no;protect=no;noerr] </td>
				<td> [ligne.compteGeneral;strconv=no;protect=no;noerr] </td>
				<td> [ligne.typeCompte;strconv=no;protect=no;noerr] </td>
				<td> [ligne.codeAnalytique;strconv=no;protect=no;noerr] </td>
				<td> [ligne.nom;strconv=no;strconv=no;protect=no;noerr] </td>
				<td> [ligne.prenom;strconv=no;protect=no;noerr] </td>
				
				<td> [ligne.referenceEcriture;strconv=no;protect=no;noerr] </td>
				<td> [ligne.libelleEcriture;strconv=no;protect=no;noerr] </td>
				<td> [ligne.modePaiement;strconv=no;protect=no;noerr] </td>
				<td> [ligne.dateEcheance;strconv=no;protect=no;noerr] </td>
				<td> [ligne.sens;strconv=no;protect=no;noerr] </td>
				<td> [ligne.montant;strconv=no;protect=no;noerr] </td>
				<td> [ligne.typeEcriture;strconv=no;protect=no;noerr] </td>
				<td> [ligne.numeroPiece;strconv=no;protect=no;noerr] </td>
				<td> [ligne.devise;strconv=no;protect=no;noerr] </td>
				
			[onshow;block=end]
		</tr>
	</tbody>
</table>
[onshow;block=end]