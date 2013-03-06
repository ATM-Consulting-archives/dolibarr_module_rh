        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">


<h1>Agenda des ressources</h1>
		
			<div id="agenda"></div>
			
			<script type="text/javascript">
			
			$.get('../../wdCalendar/sample.php', function(data) {
			  $("#agenda").html(data);
			});
			</script>
			
			







