ALTER TABLE llx_agefodd_session ADD COLUMN nb_subscribe_min integer NULL AFTER force_nb_stagiaire;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN nb_subscribe_min integer NULL AFTER fk_product;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN fk_c_category integer NULL AFTER nb_subscribe_min;


