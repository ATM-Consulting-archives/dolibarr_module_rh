ALTER TABLE llx_agefodd_session ADD COLUMN nb_subscribe_min integer NULL AFTER force_nb_stagiaire;
UPDATE llx_agefodd_session SET nb_subscribe_min=nb_min_target;
ALTER TABLE llx_agefodd_session DROP COLUMN nb_min_target;
ALTER TABLE llx_agefodd_session ADD COLUMN import_key varchar(36) DEFAULT NULL;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN fk_c_category integer NULL AFTER nb_subscribe_min;

