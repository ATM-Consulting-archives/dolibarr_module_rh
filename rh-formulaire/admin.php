<?php


require('config.php');

llxHeader();
$url = './limesurvey/index.php/admin/';
?>
<h1>Administration des formulaires</h1>


<iframe frameborder="0" id="limeSurveyFrame" name="limeSurveyFrame" src="<?=$url ?>" width="100%" height="800" onload="this.height=this.contentWindow.document.body.scrollHeight+50;" >

</iframe>


<?php

?>