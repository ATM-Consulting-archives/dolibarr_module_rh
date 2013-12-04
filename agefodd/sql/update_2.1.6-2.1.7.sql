ALTER TABLE llx_agefodd_session ADD COLUMN ref_ext varchar(50) DEFAULT NULL AFTER import_key;
ALTER TABLE llx_agefodd_session_stagiaire ADD INDEX idx_session_stagiaire_status (status_in_session);
ALTER TABLE llx_agefodd_session_stagiaire DROP FOREIGN KEY llx_agefodd_session_stagiaire_ibfk_3;
ALTER TABLE llx_agefodd_session MODIFY intitule_custo varchar(100) DEFAULT NULL;
ALTER TABLE llx_agefodd_formation_catalogue MODIFY ref_interne varchar(100) NULL;
ALTER TABLE llx_agefodd_formation_catalogue MODIFY intitule varchar(100) NOT NULL;
