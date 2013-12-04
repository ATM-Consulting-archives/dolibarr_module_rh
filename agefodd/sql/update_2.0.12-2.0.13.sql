ALTER TABLE llx_agefodd_stagiaire_type ADD COLUMN active integer NULL AFTER sort;

ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN but text NULL AFTER prerequis;

ALTER TABLE llx_actioncomm MODIFY elementtype VARCHAR(32);
