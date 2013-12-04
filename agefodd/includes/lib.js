function fnForceUpdate(obj) {
	if (!obj.checked) {
		document.getElementById('nb_stagiaire').disabled=true;
		document.getElementById('nb_stagiaire').value='';
	}
	else {
		document.getElementById('nb_stagiaire').disabled=false;
	}
}