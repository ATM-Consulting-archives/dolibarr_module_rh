<?php

//TRH_LIGNE_CV
//définition de la classe décrivant les lignes de CV d'un utilisateur
class TRH_ligne_cv extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ligne_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('libelleExperience','type=chaine'); 	//formation suivie
		parent::add_champs('descriptionExperience','type=chaine'); 	//formation suivie
		parent::add_champs('lieuExperience','type=chaine'); 	//formation suivie
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

//TRH_FORMATION
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_formation_cv extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('libelleFormation','type=chaine;');		
		parent::add_champs('competenceFormation','type=chaine;');	
		parent::add_champs('commentaireFormation','type=chaine;');		//commentaire associé	
		parent::add_champs('lieuFormation','type=chaine;');		
		parent::add_champs('coutFormation','type=chaine;');		
		parent::add_champs('date_formationEcheance','type=date;');	
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		parent::_init_vars();
		parent::start();
	}
}

//TRH_COMPETENCES
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_competence_cv extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_competence_cv');
		
		parent::add_champs('libelleCompetence','type=chaine;');	
		parent::add_champs('niveauCompetence','type=chaine;');			
		parent::add_champs('fk_user_formation','type=entier;');	
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		parent::_init_vars();
		parent::start();
		
		$this->TNiveauCompetence = array('faible'=>'Faible','moyen'=>'Moyen','bon'=>'Bon','excellent'=>'Excellent');
	}
	
	function deleteEspace($competence){
		$competence=strtolower($competence);
		$compSansEspace=str_replace(' ','%',$competence);
		return "%".$compSansEspace."%";
	}
	
	//mise en forme de la recherche : suppression des espaces, rajout des %
	function miseEnForme($competence){
		$Tcompetence=array();
		foreach ($competence as $comp){
			$Tcompetence[]="%".$comp."%";
		}
		return $Tcompetence;
	}
	
	function separerOu($competenceOu){
		$competenceOu=explode("%ou%",$competenceOu); 
		return $competenceOu=$this->miseEnForme($competenceOu);
	}
	
	function separerEt($competenceEt){
		$competenceEt=explode("%et%",$competenceEt); 
		$competenceEt=$this->miseEnForme($competenceEt);
		//print_r($competenceEt);
		$k=0;
		foreach($competenceEt as $Comp){
			if($k==0){
				$sql.= "c.libelleCompetence LIKE '".$this->separerNiveau($Comp);
				
			}else{
				$sql.= " OR c.libelleCompetence LIKE '".$this->separerNiveau($Comp);
			}
						$k++;
		}
	
		return $sql;
	}
	
	function separerNiveau($competence){
		
		foreach($this->TNiveauCompetence as $niveau){
			
			$niveau=strstr($competence,strtolower($niveau));
			if($niveau!=""){
				$competence=str_replace($niveau,'%',$competence);
				return $competence."' AND c.niveauCompetence LIKE '".$niveau."' ";
			}
		}
		return $competence."'";
		
	}
	
	
	//renvoie la requête finale de la recherche
	function requeteRecherche(&$ATMdb,  $recherche){
		global $conf;
		
		$k=0;
		//print_r($recherche);
		$sql="";
		foreach($recherche as $tagRecherche){
			if($k==0){
				$sql.="SELECT c.fk_user_formation as 'ID' , c.rowid , c.date_cre as 'DateCre', 
			 	CONCAT(u.firstname,' ',u.name) as 'name' ,c.libelleCompetence, c.fk_user, COUNT(*) as 'Niveau'
				FROM   ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."user as u 
				WHERE  c.entity=".$conf->entity. " AND c.fk_user=u.rowid AND( ";
		 		$sql.=$this->separerEt($tagRecherche). ") GROUP BY c.fk_user ";
		 		
		 	}else{
		 		$sql.=" UNION SELECT c.fk_user_formation as 'ID' , c.rowid , c.date_cre as 'DateCre', 
			 	CONCAT(u.firstname,' ',u.name) as 'name' ,c.libelleCompetence, c.fk_user, COUNT(*) as 'Niveau'
				FROM   ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."user as u 
				WHERE  c.entity=".$conf->entity. " AND c.fk_user=u.rowid AND( ";
		 		$sql.=$this->separerEt($tagRecherche). ") GROUP BY c.fk_user ";
		 	}
			$k++;
		}
		//$sql.=")";
		//print $sql;
		//AND c.libelleCompetence LIKE '".$recherche."'";
		$k=0;
		
		
		return $sql;
	}
	
}


//TRH_REMUNERATION
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_remuneration extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_remuneration');
		parent::add_champs('date_entreeEntreprise','type=date;');
		parent::add_champs('anneeRemuneration','type=entier;');	
			
		parent::add_champs('bruteAnnuelle','type=float;');		
		parent::add_champs('salaireMensuel','type=float;');		
		parent::add_champs('primeAnciennete','type=float;');	
		parent::add_champs('primeSemestrielle','type=float;');			
		parent::add_champs('primeExceptionnelle','type=float;');
		
		parent::add_champs('prevoyancePartSalariale','type=chaine;');	
		parent::add_champs('prevoyancePartPatronale','type=chaine;');	
		parent::add_champs('urssafPartSalariale','type=chaine;');	
		parent::add_champs('urssafPartPatronale','type=chaine;');
		parent::add_champs('retraitePartSalariale','type=chaine;');	
		parent::add_champs('retraitePartPatronale','type=chaine;');
		
		parent::add_champs('commentaire','type=chaine;');		
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}
