[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end]  
		
			
			<table width="100%" class="border">
			<tr><td width="20%">Code</td><td>[ressourceType.code; strconv=no]</td></tr>
			<tr><td width="20%">Libellé</td><td>[ressourceType.libelle; strconv=no]</td></tr>
			</table>
		

	
[onshow;block=begin;when [view.mode]!='edit']
	
		
		</div>
		
	<div class="tabsAction">
		<a href="?id=[ressourceType.id]&action=edit" class="butAction">Modifier</a>
		<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=delete&id=[ressourceType.id]'">Supprimer</span>
	</div>
[onshow;block=end]	
[onshow;block=begin;when [view.mode]=='edit']


<div class="tabsAction" style="text-align:center;">
	<input type="submit" value="Enregistrer" name="save" class="button"> 
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
</div>
[onshow;block=end]
