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
	
	function replaceEspaceEnPourcentage($competence){ // AA nom de fonction très mal choisie, elle ne fait pas du tout ce qu'elle inspire
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
	
	
	//renvoie la requête finale de la recherche (onglet recherche profil)
	function requeteRecherche(&$ATMdb,  $recherche){
		global $conf;
		
		$k=0;
		//print_r($recherche);
		$sql="";
		foreach($recherche as $tagRecherche){
			if($k==0){
				$sql.="SELECT c.fk_user_formation as 'ID' , u.rowid as 'fkuser', c.rowid , c.date_cre as 'DateCre', 
			 	CONCAT(u.firstname,' ',u.name) as 'name' ,c.libelleCompetence, c.fk_user, COUNT(*) as 'Niveau'
				FROM   ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."user as u 
				WHERE  c.entity=".$conf->entity. " AND c.fk_user=u.rowid AND( ";
		 		$sql.=$this->separerEt($tagRecherche). ") GROUP BY c.fk_user ";
		 		
		 	}else{
		 		$sql.=" UNION SELECT c.fk_user_formation as 'ID' , u.rowid as 'fkuser', c.rowid , c.date_cre as 'DateCre', 
			 	CONCAT(u.firstname,' ',u.name) as 'name' ,c.libelleCompetence, c.fk_user, COUNT(*) as 'Niveau'
				FROM   ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."user as u 
				WHERE  c.entity=".$conf->entity. " AND c.fk_user=u.rowid AND( ";
		 		$sql.=$this->separerEt($tagRecherche). ") GROUP BY c.fk_user ";
		 	}
			$k++;
		}

		$k=0;
		
		
		return $sql;
	}

	//va permettre la création de la requête pour les recherches stats et effectuer la recherche et obtenir les pourcentages désirés
	function requeteStatistique(&$ATMdb, $idGroupeRecherche, $idTagRecherche, $idUserRecherche){	
			if($idTagRecherche!=0&&$idGroupeRecherche==0&&$idUserRecherche==0){ //on recherche uniquement une compétence
				$sql=$this->rechercheCompetenceStat($ATMdb,$idTagRecherche);
			}
			else if($idTagRecherche!=0&&$idGroupeRecherche!=0&&$idUserRecherche==0){ //on recherche une compétence et un groupe
				$sql=$this->rechercheCompetenceGroupeStat($ATMdb,$idTagRecherche, $idGroupeRecherche);
			}
			else if($idUserRecherche!=0){ //on recherche une compétence et un utilisateur
				$sql=$this->rechercheCompetenceUserStat($ATMdb,$idTagRecherche, $idUserRecherche);
			}
			else if($idTagRecherche==0&&$idGroupeRecherche==0&&$idUserRecherche==0){ //on recherche toutes les stats dans l'entreprise
				$sql=$this->rechercheStatComplete($ATMdb);
			}
			return $sql;
	}
	
	//requete avec un tag précis recherché
	function rechercheCompetenceStat(&$ATMdb, $idTagRecherche){ 
			global $conf;
			
			$TabStat=array();
			//on recherche le nom de la compétence désirée
			$sql="SELECT libelleCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE rowid =".$idTagRecherche." AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$nomTagRecherche=$ATMdb->Get_field('libelleCompetence');
			}
			
			$nomTagRecherche="%".strtolower($nomTagRecherche)."%";
			
			//on calcule le nombre d'utilisateurs total en vue des stats
			$sql="SELECT COUNT(rowid) as 'NombreUser' FROM ".MAIN_DB_PREFIX."user
			WHERE entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUser']=$ATMdb->Get_field('NombreUser');
			}
			
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//faible
			$sql="SELECT COUNT(rowid) as 'NombreUserFaible' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE libelleCompetence LIKE '".$nomTagRecherche."' AND  niveauCompetence LIKE 'faible' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserFaible']=$ATMdb->Get_field('NombreUserFaible');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//moyen
			$sql="SELECT COUNT(rowid) as 'NombreUserMoyen' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE libelleCompetence LIKE '".$nomTagRecherche."' AND  niveauCompetence LIKE 'moyen' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserMoyen']=$ATMdb->Get_field('NombreUserMoyen');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//bon
			$sql="SELECT COUNT(rowid) as 'NombreUserBon' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE libelleCompetence LIKE '".$nomTagRecherche."' AND  niveauCompetence LIKE 'bon' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserBon']=$ATMdb->Get_field('NombreUserBon');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//excellent
			$sql="SELECT COUNT(rowid) as 'NombreUserExcellent' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE libelleCompetence LIKE '".$nomTagRecherche."' AND  niveauCompetence LIKE 'excellent' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserExcellent']=$ATMdb->Get_field('NombreUserExcellent');
			}

			return $TabStat;
	}


	//requete avec un tag précis recherché et groupe précis
	function rechercheCompetenceGroupeStat(&$ATMdb, $idTagRecherche, $idGroupeRecherche){ 
			global $conf;
			
			$TabStat=array();
			//on recherche le nom de la compétence désirée
			$sql="SELECT libelleCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE rowid =".$idTagRecherche." AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$nomTagRecherche=$ATMdb->Get_field('libelleCompetence');
			}
			
			$nomTagRecherche="%".strtolower($nomTagRecherche)."%";
			
			//on calcule le nombre d'utilisateurs total en vue des stats
			$sql="SELECT COUNT(rowid) as 'NombreUser' FROM ".MAIN_DB_PREFIX."user
			WHERE entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUser']=$ATMdb->Get_field('NombreUser');
			}
			
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//faible
			$sql="SELECT COUNT(c.rowid) as 'NombreUserFaible' FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."usergroup_user as g
			WHERE g.fk_usergroup=".$idGroupeRecherche." 
			AND g.fk_user=c.fk_user
			AND c.libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  c.niveauCompetence LIKE 'faible' AND c.entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserFaible']=$ATMdb->Get_field('NombreUserFaible');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//moyen
			$sql="SELECT COUNT(c.rowid) as 'NombreUserMoyen' FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."usergroup_user as g
			WHERE g.fk_usergroup=".$idGroupeRecherche." 
			AND g.fk_user=c.fk_user
			AND c.libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  c.niveauCompetence LIKE 'moyen' AND c.entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserMoyen']=$ATMdb->Get_field('NombreUserMoyen');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//bon
			$sql="SELECT COUNT(c.rowid) as 'NombreUserBon' FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."usergroup_user as g
			WHERE g.fk_usergroup=".$idGroupeRecherche." 
			AND g.fk_user=c.fk_user
			AND c.libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  c.niveauCompetence LIKE 'bon' AND c.entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserBon']=$ATMdb->Get_field('NombreUserBon');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//excellent
			$sql="SELECT COUNT(c.rowid) as 'NombreUserExcellent' FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."usergroup_user as g
			WHERE g.fk_usergroup=".$idGroupeRecherche." 
			AND g.fk_user=c.fk_user
			AND c.libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  c.niveauCompetence LIKE 'excellent' AND c.entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserExcellent']=$ATMdb->Get_field('NombreUserExcellent');
			}

			return $TabStat;
	}

	//requete avec un tag précis recherché et un user précis
	function rechercheCompetenceUserStat(&$ATMdb, $idTagRecherche, $idUserRecherche){ 
			global $conf;
			
			$TabStat=array();
			//on recherche le nom de la compétence désirée
			$sql="SELECT libelleCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE rowid =".$idTagRecherche." AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$nomTagRecherche=$ATMdb->Get_field('libelleCompetence');
			}
			
			$nomTagRecherche="%".strtolower($nomTagRecherche)."%";
			
			//on calcule le nombre d'utilisateurs total en vue des stats
			$sql="SELECT COUNT(rowid) as 'NombreUser' FROM ".MAIN_DB_PREFIX."user
			WHERE entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUser']=$ATMdb->Get_field('NombreUser');
			}
			
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//faible
			$sql="SELECT COUNT(rowid) as 'NombreUserFaible' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE fk_user=".$idUserRecherche." AND libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  niveauCompetence LIKE 'faible' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserFaible']=$ATMdb->Get_field('NombreUserFaible');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//moyen
			$sql="SELECT COUNT(rowid) as 'NombreUserMoyen' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE fk_user=".$idUserRecherche." AND libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  niveauCompetence LIKE 'moyen' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserMoyen']=$ATMdb->Get_field('NombreUserMoyen');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//bon
			$sql="SELECT COUNT(rowid) as 'NombreUserBon' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE fk_user=".$idUserRecherche." AND libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  niveauCompetence LIKE 'bon' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserBon']=$ATMdb->Get_field('NombreUserBon');
			}
			
			//on teste pour chaque difficulté, la proportion des collaborateurs concernés
			//excellent
			$sql="SELECT COUNT(rowid) as 'NombreUserExcellent' FROM ".MAIN_DB_PREFIX."rh_competence_cv
			WHERE fk_user=".$idUserRecherche." AND libelleCompetence LIKE '".$nomTagRecherche."' 
			AND  niveauCompetence LIKE 'excellent' AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TabStat['nbUserExcellent']=$ATMdb->Get_field('NombreUserExcellent');
			}

			return $TabStat;
	}

	//requete totale de statistiques 
	function rechercheStatComplete(&$ATMdb){ 
			global $conf;
			
			$TabStat=array();
			
			//on récupère toutes les compétences existantes, et on en sort des stats. 
			
			$sql="SELECT c.rowid, c.libelleCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv as c
			WHERE c.entity=".$conf->entity;
			$ATMdb->Execute($sql);
			$TTagCompetence=array();
			$TTagCompetence[0]='Tous';
			while($ATMdb->Get_line()) {
				$TTagCompetence[$ATMdb->Get_field('rowid')]="%".strtolower($ATMdb->Get_field('libelleCompetence'))."%";
			}
			
			$k=0;
			foreach($TTagCompetence as $tag){ 	//pour chaque coméptences, on recherche les stats de niveau pour tous les utilisateurs
				
				//on teste pour chaque difficulté, la proportion des collaborateurs concernés
				//faible
				$sql="SELECT COUNT(rowid) as 'NombreUserFaible' FROM ".MAIN_DB_PREFIX."rh_competence_cv
				WHERE libelleCompetence LIKE '".$tag."' AND  niveauCompetence LIKE 'faible' AND entity=".$conf->entity;
				$ATMdb->Execute($sql);
				while($ATMdb->Get_line()) {
					$TabStat[$k]['nbUserFaible']=$ATMdb->Get_field('NombreUserFaible');
				}
				
				//on teste pour chaque difficulté, la proportion des collaborateurs concernés
				//moyen
				$sql="SELECT COUNT(rowid) as 'NombreUserMoyen' FROM ".MAIN_DB_PREFIX."rh_competence_cv
				WHERE libelleCompetence LIKE '".$tag."' AND  niveauCompetence LIKE 'moyen' AND entity=".$conf->entity;
				$ATMdb->Execute($sql);
				while($ATMdb->Get_line()) {
					$TabStat[$k]['nbUserMoyen']=$ATMdb->Get_field('NombreUserMoyen');
				}
				
				//on teste pour chaque difficulté, la proportion des collaborateurs concernés
				//bon
				$sql="SELECT COUNT(rowid) as 'NombreUserBon' FROM ".MAIN_DB_PREFIX."rh_competence_cv
				WHERE libelleCompetence LIKE '".$tag."' AND  niveauCompetence LIKE 'bon' AND entity=".$conf->entity;
				$ATMdb->Execute($sql);
				while($ATMdb->Get_line()) {
					$TabStat[$k]['nbUserBon']=$ATMdb->Get_field('NombreUserBon');
				}
				
				//on teste pour chaque difficulté, la proportion des collaborateurs concernés
				//excellent
				$sql="SELECT COUNT(rowid) as 'NombreUserExcellent' FROM ".MAIN_DB_PREFIX."rh_competence_cv
				WHERE libelleCompetence LIKE '".$tag."' AND  niveauCompetence LIKE 'excellent' AND entity=".$conf->entity;
				$ATMdb->Execute($sql);
				while($ATMdb->Get_line()) {
					$TabStat[$k]['nbUserExcellent']=$ATMdb->Get_field('NombreUserExcellent');
				}
				$k++;
			}
				
			//on calcule le nombre d'utilisateurs total en vue des stats
			$sql="SELECT COUNT(rowid) as 'NombreUser' FROM ".MAIN_DB_PREFIX."user
			WHERE entity=".$conf->entity;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()){
				$TabStat[$k]['nbUser']=$ATMdb->Get_field('NombreUser');
			}
				
			return $TabStat;
	}

	
	
	
}


//TRH_REMUNERATION
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_remuneration extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_remuneration');
		parent::add_champs('date_entreeEntreprise','type=date;');
		
		parent::add_champs('date_debutRemuneration','type=date;');
		parent::add_champs('date_finRemuneration','type=date;');
		
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
