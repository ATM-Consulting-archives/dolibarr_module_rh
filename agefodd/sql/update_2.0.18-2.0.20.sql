ALTER TABLE llx_agefodd_convention DROP INDEX idx_fk_societe, ADD INDEX idx_fk_societe_conv (fk_societe);
ALTER TABLE llx_agefodd_convention DROP INDEX idx_fk_agefodd_session, ADD INDEX idx_fk_agefodd_session_conv (fk_agefodd_session);
ALTER TABLE llx_agefodd_facture DROP INDEX idx_fk_facture, ADD INDEX idx_fk_facture_fac (fk_facture);
ALTER TABLE llx_agefodd_facture DROP INDEX idx_fk_societe, ADD INDEX idx_fk_societe_fac (fk_societe);
ALTER TABLE llx_agefodd_facture DROP INDEX idx_fk_session, ADD INDEX idx_fk_session_fac (fk_session);
ALTER TABLE llx_agefodd_facture DROP INDEX idx_fk_commande, ADD INDEX idx_fk_commande_fac (fk_commande);
ALTER TABLE llx_agefodd_session_calendrier DROP INDEX idx_fk_agefodd_session, ADD INDEX idx_fk_agefodd_session_cal (fk_agefodd_session);
ALTER TABLE llx_agefodd_session_calendrier DROP INDEX idx_fk_agefodd_session_act, ADD INDEX idx_fk_agefodd_session_act_cal (fk_actioncomm);
ALTER TABLE llx_agefodd_session_adminsitu DROP INDEX fk_agefodd_session, ADD INDEX fk_agefodd_session_adminsitu (fk_agefodd_session);
ALTER TABLE llx_agefodd_place DROP INDEX archive, ADD INDEX archive_place (archive);
ALTER TABLE llx_agefodd_formation_catalogue DROP INDEX ref,ADD UNIQUE ref_form_cat (ref);
ALTER TABLE llx_agefodd_formation_objectifs_peda DROP INDEX fk_formation_catalogue, ADD INDEX fk_formation_catalogue_obj_peda (fk_formation_catalogue);
ALTER TABLE llx_agefodd_session_commercial DROP INDEX fk_session, ADD INDEX fk_session_sess_comm (fk_session_agefodd);
ALTER TABLE llx_agefodd_session_contact DROP INDEX fk_session, ADD INDEX fk_session_sess_contact (fk_session_agefodd);
ALTER TABLE llx_agefodd_session_formateur DROP INDEX fk_session, ADD INDEX fk_session_sess_form (fk_session);
ALTER TABLE llx_agefodd_session_formateur DROP INDEX idx_fk_agefodd_formateur, ADD INDEX idx_fk_agefodd_sess_formateur (fk_agefodd_formateur);
ALTER TABLE llx_agefodd_session_stagiaire DROP INDEX fk_session, ADD INDEX fk_session_sess_sta (fk_session_agefodd);
ALTER TABLE llx_agefodd_session_stagiaire DROP INDEX fk_agefodd_stagiaire_type, ADD INDEX fk_agefodd_stagiaire_type_sess_sta (fk_agefodd_stagiaire_type);
ALTER TABLE llx_agefodd_stagiaire DROP INDEX nom, ADD INDEX nom_sta (nom);
ALTER TABLE llx_agefodd_stagiaire DROP INDEX fk_soc, ADD INDEX fk_soc_sta (fk_soc);
ALTER TABLE llx_agefodd_session DROP INDEX fk_soc, ADD INDEX fk_soc_session (fk_soc);







