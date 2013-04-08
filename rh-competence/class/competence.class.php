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
				$sql.= " AND (c.libelleCompetence LIKE '".$this->separerNiveau($Comp).")";
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
		$sql="SELECT c.fk_user_formation as 'ID' , c.rowid , c.date_cre as 'DateCre', 
			  CONCAT(u.firstname,' ',u.name) as 'name' ,c.libelleCompetence
			 , c.fk_user
		FROM   ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."user as u 
		WHERE  c.entity=".$conf->entity. " AND c.fk_user=u.rowid AND(( ";
		//AND c.libelleCompetence LIKE '".$recherche."'";
		$k=0;
		foreach($recherche as $tagRecherche){
			if($k==0){
		 		$sql.=$this->separerEt($tagRecherche).")";
		 	}else{
		 		$sql.=" OR (".$this->separerEt($tagRecherche).")";
		 	}
			$k++;
		}
		$sql.=")";
		return $sql;
	}
	
}

