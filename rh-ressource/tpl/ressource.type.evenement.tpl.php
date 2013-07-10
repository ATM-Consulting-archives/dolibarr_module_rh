[onshow;block=begin;when [view.mode]=='view']
	[view.head;strconv=no]                     
[onshow;block=end]  

[onshow;block=begin;when [view.mode]!='view']
	[view.onglet;strconv=no]                     
[onshow;block=end]  
	
			
<table width="100%" class="border">
	<tr><td width="20%">Libellé</td><td>[ressourceType.libelle; strconv=no]</td></tr>
	<tr><td width="20%">Code</td><td>[ressourceType.code; strconv=no]</td></tr>
</table>
<br>

[ressourceType.titreEvenement; strconv=no]

<table width="100%" class="border">
	<tr>
		<td width="20%">Libellé</td>
		<td>[newEvent.libelle;strconv=no]</td>
	</tr>
	<tr>
		<td>Code</td>
		<td>[newEvent.code;strconv=no]</td>
	</tr>
	
	<script>
		String.prototype.sansAccent = function(){
		    var accent = [
		        /[\300-\306]/g, /[\340-\346]/g, // A, a
		        /[\310-\313]/g, /[\350-\353]/g, // E, e
		        /[\314-\317]/g, /[\354-\357]/g, // I, i
		        /[\322-\330]/g, /[\362-\370]/g, // O, o
		        /[\331-\334]/g, /[\371-\374]/g, // U, u
		        /[\321]/g, /[\361]/g, // N, n
		        /[\307]/g, /[\347]/g, // C, c
		    ];
		    var noaccent = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c',''];
		    var str = this;
		    for(var i = 0; i < accent.length; i++){
		        str = str.replace(accent[i], noaccent[i]);
		        str = str.replace(' ','');
		    }
    		return str;
		}
		
		$('#libelle').change(function(){
			texte = $('#libelle').val();
			$('#code').val(texte.sansAccent());
		})
		
	</script>
	<tr>
		<td>Code Comptable</td>
		<td>[newEvent.codecomptable;strconv=no]</td>
	</tr>
	
</table>


[onshow;block=begin;when [view.mode]!='edit']		
	<div class="tabsAction">
		<a href="?id=[ressourceType.id]&idTypeEvent=[newEvent.id]&action=edit" class="butAction">Modifier</a>
		[onshow;block=begin;when [newEvent.supprimable]=='vrai']		
			<a class="butActionDelete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[ressourceType.id]&idTypeEvent=[newEvent.id]&action=delete'};">Supprimer</a>
			
		[onshow;block=end]	
	</div>
[onshow;block=end]	
[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction" style="text-align:center;">
	<input type="submit" value="Enregistrer" name="save" class="button"> 
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
</div>
[onshow;block=end]
